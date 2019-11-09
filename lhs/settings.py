"""
Django settings for lhs project.

Generated by 'django-admin startproject' using Django 1.11.3.

For more information on this file, see
https://docs.djangoproject.com/en/1.11/topics/settings/

For the full list of settings and their values, see
https://docs.djangoproject.com/en/1.11/ref/settings/
"""

import os

# Build paths inside the project like this: os.path.join(BASE_DIR, ...)
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/1.11/howto/deployment/checklist/

SECRET_KEY = '9vy^#gb*ec6q#!%^xsvth$$_)by-7^*i=qe27s_^vlhvo)rt5&'

DEBUG = True

SITE_ID = 1
DOMAIN_NAME = 'localhost'
ABSOLUTEURI_PROTOCOL = 'https'

ALLOWED_HOSTS = [DOMAIN_NAME]

EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'


# Application definition

INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.sites',
    'django.contrib.staticfiles',
    'absoluteuri',
    'lhsauth',
    'lhsgocardless',
    'lhspayments',
    'main',
]

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'lhsauth.middleware.FlourishAuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.contrib.sites.middleware.CurrentSiteMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

AUTHENTICATION_BACKENDS = [
    'lhsauth.backends.FlourishSessionBackend',
]

AUTH_USER_MODEL = 'lhsauth.User'

ROOT_URLCONF = 'lhs.urls'

TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
            'builtins': [
                'absoluteuri.templatetags.absoluteuri',
            ],
        },
    },
]

WSGI_APPLICATION = 'lhs.wsgi.application'


# Database
# https://docs.djangoproject.com/en/1.11/ref/settings/#databases

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql_psycopg2',
        'NAME': 'hackspace',
        'USER': 'hackspace'
    }
}


# Password validation
# https://docs.djangoproject.com/en/1.11/ref/settings/#auth-password-validators

AUTH_PASSWORD_VALIDATORS = [
    {
        'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator',
    },
]

PASSWORD_HASHERS = [
    'lhsauth.lib.FlourishSHA1PasswordHasher.FlourishSHA1PasswordHasher'
]

SESSION_SERIALIZER = 'lhs.lib.MsgPackSerializer.MsgPackSerializer'

SESSION_COOKIE_AGE = 30*60  # to match Flourish for non-persistent sessions

# Internationalization
# https://docs.djangoproject.com/en/1.11/topics/i18n/

LANGUAGE_CODE = 'en-gb'

TIME_ZONE = 'UTC'

USE_I18N = True

USE_L10N = True

USE_TZ = True


# Static files (CSS, JavaScript, Images)
# https://docs.djangoproject.com/en/1.11/howto/static-files/

STATIC_URL = '/static/'
STATIC_ROOT = os.path.join(BASE_DIR, "static/")

FLOURISH_LOOPBACK_URLS = {
    'authenticate': 'https://%s/session.php' % DOMAIN_NAME,
    'destroy': 'https://%s/session.php?destroy' % DOMAIN_NAME,
}

LOGIN_REDIRECT_URL = '/members'

LOGIN_URL = LOGIN_REDIRECT_URL

PROJECT_MAILING_LIST = 'london-hack-space-test'

CONTACT_EMAIL = 'contact@' + DOMAIN_NAME
NOREPLY_EMAIL = 'no-reply@' + DOMAIN_NAME

GOCARDLESS_ENV = 'sandbox'
# These are our sandbox details, fear not.
GOCARDLESS_CREDENTIALS = {
  'access_token':  '0SDNTJADVK',
  'webhook_secret': 'YourSecretHere',
}

