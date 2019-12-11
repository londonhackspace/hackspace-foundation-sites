from lhspayments.models import Payment

from django.template.loader import render_to_string
from django.conf import settings

import smtplib
from email.message import EmailMessage

def send_email(user, subject, body):
    msg = EmailMessage()
    msg['Subject'] = subject
    msg['From'] = 'London Hackspace <trustees@london.hackspace.org.uk>'
    msg['To'] = user.email
    msg.set_content(body)

    smtp = None
    if settings.SMTP_TLS:
        smtp = smtplib.SMTP_SSL(settings.SMTP_SERVER)
    else:
        smtp = smtplib.SMTP(settings.SMTP_SERVER)
        if settings.SMTP_STARTTLS:
            smtp.starttls()
    if settings.SMTP_USER is not None:
        smtp.login(settings.SMTP_USER, settings.SMTP_PASSWORD)
    smtp.send_message(msg)
    smtp.quit()

def lapse_email(user):
    ctx = {
        'name': user.full_name,
        'date': Payment.objects.filter(user=user).order_by('-timestamp').exclude(payment_state=Payment.STATE_FAILED).first().timestamp.date()
    }

    send_email(user, 'Your London Hackspace membership has lapsed', render_to_string("email/lapse.template", ctx))

def new_user_email(user):
    ctx = {
        'name': user.full_name,
        'date': Payment.objects.filter(user=user).order_by('-timestamp').exclude(payment_state=Payment.STATE_FAILED).first().timestamp.date()
    }

    send_email(user, 'Your London Hackspace membership is now active', render_to_string("email/new_user.template", ctx))