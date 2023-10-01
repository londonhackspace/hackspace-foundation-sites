import datetime
import os

from .settings import *

# SECURITY WARNING: create your own key, and keep it secret!
SECRET_KEY = 'notasecretkey'

# SECURITY WARNING: don't run with debug turned on in production!
DEBUG = True

SITE_ID = 1
DOMAIN_NAME = 'localhost'
ABSOLUTEURI_PROTOCOL = 'http'

ALLOWED_HOSTS = [DOMAIN_NAME]

EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'


SMTP_SERVER = 'mail'
SMTP_TLS = False
SMTP_STARTTLS = False
SMTP_USER = None
SMTP_PASSWORD = None

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql_psycopg2',
        'HOST': 'db',
        'NAME': 'hackspace',
        'USER': 'hackspace',
        'PASSWORD': 'hackspace',
    }
}

PROJECT_MAILING_LIST = 'london-hack-space'

FLOURISH_LOOPBACK_URLS = {
    'authenticate': 'http://web:8000/session.php',
    'destroy': 'http://web:8000/session.php?destroy',
}

CONTACT_EMAIL = 'contact@' + DOMAIN_NAME
NOREPLY_EMAIL = 'no-reply@' + DOMAIN_NAME

# set this to live in production
GOCARDLESS_ENV = 'sandbox'
GOCARDLESS_CREDENTIALS = {
  'access_token':  'A0d7SbxK-Ylz',
  'webhook_secret': 'YourSecretHere',
}

NORDIGEN_TOKEN = os.getenv('NORDIGEN_TOKEN', 'your-token')
NORDIGEN_ACCOUNT = os.getenv('NORDIGEN_ACCOUNT', 'your-account-id')

SUBS_MIN_PAYMENT = 5.00
SUBS_GRACE_PERIOD = datetime.timedelta(weeks=6)
