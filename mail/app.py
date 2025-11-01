from flask import Flask, request, render_template, jsonify
from asgiref.wsgi import WsgiToAsgi
from models import db, Sent_Mails
from dotenv import load_dotenv
from email.utils import formataddr, make_msgid, formatdate
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import smtplib
import os

app = Flask(__name__)

# Load .env
load_dotenv()

# Get SMTP configuration
SMTP_SERVER = os.getenv('MAIL_SERVER')
SMTP_PORT = int(os.getenv('MAIL_PORT', 587))
SMTP_USERNAME = os.getenv('MAIL_USERNAME')
SMTP_PASSWORD = os.getenv('MAIL_PASSWORD')
SENDER_NAME = os.getenv('MAIL_DEFAULT_SENDER', 'CUSB BANK')
SENDER_DOMAIN = SMTP_USERNAME.split('@')[1] if '@' in SMTP_USERNAME else 'cusbank.com'

print("=== SMTP Configuration ===")
print(f"SMTP_SERVER: {SMTP_SERVER}")
print(f"SMTP_PORT: {SMTP_PORT}")
print(f"SMTP_USERNAME: {SMTP_USERNAME}")
print(f"SENDER_NAME: {SENDER_NAME}")
print(f"SENDER_DOMAIN: {SENDER_DOMAIN}")
print("=" * 50)

# Database Configuration
if os.getenv('DEVELOPMENT_MODE') == 'live':
    app.config['SQLALCHEMY_DATABASE_URI'] = os.getenv('LIVE_DATABASE_URL')
else:
    app.config['SQLALCHEMY_DATABASE_URI'] = os.getenv('LOCAL_DATABASE_URL')

app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db.init_app(app)

def send_email_smtp(recipient_email, recipient_name, subject, heading, message):
    """
    Send email using direct SMTP connection.
    Compatible with both SSL (port 465) and STARTTLS (port 587).
    """
    try:
        # Render HTML template
        html_content = render_template(
            'mail_template.html',
            name=recipient_name,
            subject=subject,
            heading=heading,
            message=message
        )

        # Plain text fallback
        plain_content = f"Hello {recipient_name},\n\n{message}\n\nPlease view this message in an HTML-capable email client for the best experience.\n\n- {SENDER_NAME}"

        # Build message
        msg = MIMEMultipart("alternative")
        msg["Subject"] = subject
        msg["From"] = formataddr((SENDER_NAME, SMTP_USERNAME))
        msg["To"] = recipient_email
        msg["Date"] = formatdate(localtime=True)
        msg["Message-ID"] = make_msgid(domain=SENDER_DOMAIN)

        # Attach both versions
        msg.attach(MIMEText(plain_content, "plain"))
        msg.attach(MIMEText(html_content, "html"))

        # Decide between SSL or TLS based on port
        if SMTP_PORT == 465:
            # Implicit SSL (port 465)
            with smtplib.SMTP_SSL(host=SMTP_SERVER, port=SMTP_PORT, timeout=15) as server:
                server.ehlo()
                server.login(SMTP_USERNAME, SMTP_PASSWORD)
                server.send_message(msg, from_addr=SMTP_USERNAME, to_addrs=[recipient_email])
        else:
            # STARTTLS (port 587 or others)
            with smtplib.SMTP(host=SMTP_SERVER, port=SMTP_PORT, timeout=15) as server:
                server.ehlo()
                server.starttls()
                server.ehlo()
                server.login(SMTP_USERNAME, SMTP_PASSWORD)
                server.send_message(msg, from_addr=SMTP_USERNAME, to_addrs=[recipient_email])

        print(f"✅ Email sent successfully to {recipient_email}")
        return True, "Email sent successfully"

    except Exception as e:
        print(f"❌ Failed to send email: {e}")
        import traceback
        traceback.print_exc()
        return False, str(e)


# Index page
@app.route('/')
def index():
    return render_template('index.html')


# Send mail route
@app.route('/send', methods=['GET', 'POST'])
def send():
    if request.method == 'POST':
        name = request.form.get('recipient_name')
        email = request.form.get('recipient_email')
        subject = request.form.get('subject')
        heading = request.form.get('heading', subject)
        message = request.form.get('body')
        
        # Send email using direct SMTP
        success, result = send_email_smtp(email, name, subject, heading, message)
        
        if success:
            # Save to database
            try:
                history = Sent_Mails(
                    name=name, 
                    email=email, 
                    heading=heading, 
                    subject=subject, 
                    message=message, 
                    status='Sent'
                )
                db.session.add(history)
                db.session.commit()
            except Exception as db_error:
                print(f"Database error: {db_error}")
            
            return jsonify({"message": result}), 200
        else:
            # Save failed attempt
            try:
                history = Sent_Mails(
                    name=name, 
                    email=email, 
                    heading=heading, 
                    subject=subject, 
                    message=message, 
                    status='Failed'
                )
                db.session.add(history)
                db.session.commit()
            except Exception as db_error:
                print(f"Database error: {db_error}")
            
            return jsonify({"error": f"Failed to send email: {result}"}), 500
    
    return render_template('send.html')


@app.route('/send-mail', methods=['POST'])
def send_mail():
    try:
        data = request.get_json()

        # Validate required fields
        required_fields = ['recipient_name', 'recipient_email', 'subject', 'heading', 'body']
        for field in required_fields:
            if not data.get(field):
                return jsonify({"error": f"'{field}' is required"}), 400

        # Extract fields
        name = data['recipient_name']
        email = data['recipient_email']
        subject = data['subject']
        heading = data['heading']
        message = data['body']

        print(f"Sending email to {email} with subject: {subject}")
        
        # Send email using direct SMTP (same as your working script)
        success, result = send_email_smtp(email, name, subject, heading, message)
        
        # Try to save to database, but don't fail the request if DB is down
        try:
            history = Sent_Mails(
                name=name, 
                email=email, 
                heading=heading, 
                subject=subject, 
                message=message, 
                status='Sent' if success else 'Failed'
            )
            db.session.add(history)
            db.session.commit()
        except Exception as db_error:
            print(f"Database error: {db_error}")
            # Optionally log db_error somewhere

        if success:
            return jsonify({"message": result}), 200
        else:
            return jsonify({"error": f"Failed to send email: {result}"}), 500

    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({"error": f"Failed to send email: {str(e)}"}), 500


@app.route('/api/history', methods=['GET'])
def history():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)

    # Paginate the query with proper ordering
    paginated_history = Sent_Mails.query.order_by(
        Sent_Mails.id.desc()
    ).paginate(page=page, per_page=per_page, error_out=False)

    data = [{
        "id": obj.id,
        "name": obj.name,
        "email": obj.email,
        "subject": obj.subject,
        "heading": obj.heading,
        "message": obj.message,
        "status": obj.status
    } for obj in paginated_history.items]

    return jsonify({
        "data": data,
        "total": paginated_history.total,
        "pages": paginated_history.pages,
        "current_page": paginated_history.page
    })


@app.route('/test-mail', methods=['GET'])
def test_mail():
    try:
        test_recipient = "kingsleyesisi1@gmail.com"
        
        print(f"Sending test email to {test_recipient}...")
        
        # Use the same send_email_smtp function
        success, result = send_email_smtp(
            recipient_email=test_recipient,
            recipient_name="Test User",
            subject="Test Email from CUSB BANK",
            heading="System Test Email",
            message="This is a test email from CUSB Bank Mail System. If you can read this, the email system is working correctly!"
        )
        
        if success:
            return f"✅ Test email sent successfully to {test_recipient}!"
        else:
            return f"❌ Failed to send test email: {result}"
            
    except Exception as e:
        print(f"Test mail error: {e}")
        import traceback
        traceback.print_exc()
        return f"❌ Failed to send test email: {str(e)}"


@app.route('/history', methods=['GET'])
def history_page():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)

    # Paginate the query with proper ordering
    paginated_history = Sent_Mails.query.order_by(
        Sent_Mails.id.desc()
    ).paginate(page=page, per_page=per_page, error_out=False)

    return render_template('history.html', history=paginated_history)


@app.route('/view/<int:id>', methods=['GET'])
def view_mail(id):
    mail_record = Sent_Mails.query.get_or_404(id)
    context = {
        "name": mail_record.name,
        "email": mail_record.email,
        "subject": mail_record.subject,
        "heading": mail_record.heading,
        "message": mail_record.message,
        "status": mail_record.status
    }
    return render_template('mail_template.html', **context)


application = WsgiToAsgi(app)

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True)