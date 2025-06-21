from flask import Flask, request, render_template, jsonify
from flask_mail import Mail, Message
from asgiref.wsgi import WsgiToAsgi
from models import db, Sent_Mails
from dotenv import load_dotenv
import os

# import mail
app = Flask(__name__)

# Load .env
load_dotenv()

# configure flask 
app.config['MAIL_SERVER'] = os.getenv('MAIL_SERVER')
app.config['MAIL_PORT'] = 587

app.config['MAIL_USE_TLS'] = True
app.config['MAIL_USERNAME'] = os.getenv('MAIL_USERNAME')
app.config['MAIL_PASSWORD'] = os.getenv('MAIL_PASSWORD')
app.config['MAIL_DEFAULT_SENDER'] = os.getenv('MAIL_DEFAULT_SENDER')

# Database Configuration
# app.config['SQLALCHEMY_DATABASE_URI'] = "mysql+pymysql://admin:admin@localhost/tradexpro"
if os.getenv('DEVELOPMENT_MODE') == 'live':
    app.config['SQLALCHEMY_DATABASE_URI'] = os.getenv('LIVE_DATABASE_URL')
else:
    app.config['SQLALCHEMY_DATABASE_URI'] = os.getenv('LOCAL_DATABASE_URL')

app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db.init_app(app)

mail = Mail(app)

# Index page
@app.route('/')
def index():
    return render_template('index.html')

# Send mail route
@app.route('/send', methods=['GET', 'POST'])
def send():
    if request.method == 'POST':
        name = request.form.get('recipient_name')  # Match the HTML field name
        email = request.form.get('recipient_email')  # Match the HTML field name
        subject = request.form.get('subject')  # Match the HTML field name
        message = request.form.get('body')  # Match the HTML field name
        print(f"Name: {name}, Email: {email}, Subject: {subject}, Message: {message}")
        return "Form data received and printed"
    return render_template('send.html')


@app.route('/send-mail', methods=['POST'])
def send_mail():
    try:
        # Parse JSON data from the request
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

        # Compose the email
        html_body = render_template('migrate.html', name=name, subject=subject, heading=heading, message=message)
        msg = Message(subject=subject, recipients=[email], html=html_body)

        # Send the email
        mail.send(msg)

        # Save to database
        history = Sent_Mails(name=name, email=email, heading=heading, subject=subject, message=message, status='Sent')
        db.session.add(history)
        db.session.commit()

        return jsonify({"message": "Email sent successfully"}), 200

    except Exception as e:
        print(f"Error: {e}")
        return jsonify({"error": f"Failed to send email: {str(e)}"}), 500

@app.route('/api/history', methods=['GET'])
def history():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)

    # Paginate the query
    paginated_history = Sent_Mails.query.paginate(page=page, per_page=per_page, error_out=False)

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
        msg = Message(
            subject="Test Email",
            recipients=["kingsleyesisi1@gmail.com"],  # Replace with your email
            body="This is a test email from KINGS WORLD."
        )
        mail.send(msg)
        return "Test email sent successfully!"
    except Exception as e:
        return f"Failed to send test email: {str(e)}"

@app.route('/history', methods=['GET'])
def history_page():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)

    # Paginate the query
    paginated_history = Sent_Mails.query.paginate(page=page, per_page=per_page, error_out=False)

    return render_template('history.html', history=paginated_history)


@app.route('/view/<int:id>', methods=['GET'])
def view_mail(id):
    mail = Sent_Mails.query.get_or_404(id)
    context = {
        "name": mail.name,
        "email": mail.email,
        "subject": mail.subject,
        "heading": mail.heading,
        "message": mail.message,
        "status": mail.status
    }
    return render_template('mail_template.html', **context)

application = WsgiToAsgi(app)

if __name__ == '__main__':
    app.run(debug=True)

