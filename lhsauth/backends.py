from django.conf import settings
from django.contrib.auth import get_user_model, load_backend, BACKEND_SESSION_KEY
from django.core.exceptions import PermissionDenied
from django.contrib.auth.signals import user_logged_out
from django.dispatch import receiver
from django.utils import timezone

import re
import requests
from datetime import datetime

UserModel = get_user_model()

"""
A backend that authenticates against an existing instance of Flourish
by fetching a page with the same cookie provided in the user's request.

Use with the FlourishAuthenticationMiddleware to link sessions between
Flourish and Django automatically.

Requires a URL on the PHP side that validates a session with Flourish,
and returns JSON with the `user_id` and `fSession::expires` timestamp.
Also requires an unadvertised URL which destroys a Flourish session if
it exists. It's possible to use one script with arguments for both.

This sets a datetime expiry on sessions, so you must also change your
default session serialisation from JSONSerializer to PickleSerializer
or MsgPackSerializer.

Provide these URLs in your settings.py, e.g.:

FLOURISH_LOOPBACK_URLS = {
    'authenticate': 'http://localhost:9000/session.php',
    'destroy': 'http://localhost:9000/session.php?destroy',
}

To ensure both sessions are logged out together, users should be sent
to a Django view that calls `django.contrib.auth.logout`.
"""

class FlourishSessionBackend(object):
    def authenticate(self, request, rawphpsessid):
        # We currently require a request as we want to tie session lifetimes
        if request is None:
            return None

        if not re.match(r'[0-9A-Za-z,-]{26,32}', rawphpsessid):
            raise ValueError('Invalid phpsessid')

        cookies = {'PHPSESSID': rawphpsessid}
        session = requests.get(settings.FLOURISH_LOOPBACK_URLS['authenticate'], cookies=cookies)
        session.raise_for_status()

        session = session.json()
        try:
            user_id = session['user_id']
        except KeyError:
            # Session expired or invalid - allow other backends
            return None

        assert isinstance(user_id, int)

        user = UserModel.objects.get(pk=user_id)
        if not user.is_active:
            # Stop all authentication attempts - no more backends
            raise PermissionDenied

        # Even non-persistent sessions have an expiry, so check it
        if session['expires'] < 1500000000:
            raise ValueError('Flourish session expiry timestamp is invalid')

        expires = datetime.fromtimestamp(session['expires'], tz=timezone.utc)
        if expires < timezone.now():
            return None

        if session['type'] != 'persistent':
            # End the session when the browser closes, or 30 mins, whichever is first
            # We allow for a window of risk here to avoid always checking the session
            expires = 0

        # Flourish bumps its session expiry on every request, but
        # we'll just reauthenticate if the Django session expires.
        request.session.set_expiry(expires)

        request.session['PHPSESSID'] = rawphpsessid

        return user


    def get_user(self, user_id):
        return UserModel.objects.get(pk=user_id)


@receiver(user_logged_out)
def destroy_session(sender, **kwargs):
    """
    Intercept the logout process so we can clear the Flourish session
    before Django's. Otherwise, the phpsessid could be used for Django
    URLs afterwards.

    Relies on `django.contrib.auth.logout` not using `send_robust`.
    """
    try:
        request = kwargs['request']
    except KeyError:
        return

    user = kwargs.get('user')
    if not user:
        return

    backend = load_backend(request.session[BACKEND_SESSION_KEY])
    if not isinstance(backend, FlourishSessionBackend):
        return

    cookies = {'PHPSESSID': request.session['PHPSESSID']}
    session = requests.get(settings.FLOURISH_LOOPBACK_URLS['destroy'], cookies=cookies)

    # As long as no error was returned, the session's destroyed
    # (or never existed) so we can safely continue logging out.
    session.raise_for_status()

