from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from datetime import datetime
app = Flask(__name__)

db = SQLAlchemy()

class Sent_Mails(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(80), unique=False, nullable=False)
    email = db.Column(db.String(80), unique=False, nullable=False)
    subject = db.Column(db.String(80), unique=False, nullable=False)
    heading = db.Column(db.String(80))
    message = db.Column(db.String(2000))
    status = db.Column(db.String(20), default="Not Sent")
    sent_on =  db.Column(db.DateTime, default=datetime.now())

    def __repr__(self):
        return f'user {self.name}'

if __name__ == "__main__":
    app.run(debug=True)