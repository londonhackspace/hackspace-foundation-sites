from django.core.management.base import BaseCommand
from datetime import datetime, timedelta, timezone
import subprocess

import smtplib
from email.message import EmailMessage

from lhsauth.models import User
from lhspayments.models import Payment

from django.template.loader import render_to_string
from django.conf import settings

def lapse_email(user):
    ctx = {
        'name': user.full_name,
        'date': Payment.objects.filter(user=user).order_by('-timestamp').exclude(payment_state=Payment.STATE_FAILED).first().timestamp.date()
    }

    msg = EmailMessage()
    msg['Subject'] = 'Your London Hackspace membership has lapsed'
    msg['From'] = 'London Hackspace <trustees@london.hackspace.org.uk>'
    msg['To'] = user.email
    msg.set_content(render_to_string("email/lapse.template", ctx))

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


def unsubscribe_member(user):
    user.subscribed = False
    #user.save()

    if user.ldapuser is not None:
        print("Deleting LDAP account: %s" % (user.ldapuser,))
        subprocess.run("/var/www/hackspace-foundation-sites/bin/ldap-delete.sh", user.ldapuser)
    
    lapse_email(user)


class Command(BaseCommand):
    help = "Checks for inactive members"

    def handle(self, *args, **options):
        # Find ex-members

        # calculate the latest payment that's no longer valid
        d = datetime.now(timezone.utc) - timedelta(weeks=6)

        for u in User.objects.filter(subscribed=True):
            # essentially, has the user made a non-failed payment in the last six weeks?
            payment = Payment.objects.filter(user=u).filter(timestamp__gte=d).exclude(payment_state=Payment.STATE_FAILED).first()
            if payment is None:
                print("%s is no longer a member. Unsubscribing." % (u.full_name))
                unsubscribe_member(u)

