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
