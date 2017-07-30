from django.contrib import auth
from django.utils.crypto import constant_time_compare

from .backends import FlourishSessionBackend

class FlourishAuthenticationMiddleware(object):
    def __init__(self, get_response):
        self.get_response = get_response

    def check_phpsessid(self, request):
        if request.user.is_authenticated:
            backend = auth.load_backend(request.session[auth.BACKEND_SESSION_KEY])
            if not isinstance(backend, FlourishSessionBackend):
                return

            if not constant_time_compare(request.session['PHPSESSID'],
                                         request.COOKIES.get('PHPSESSID')):

                # The user has changed session or logged out without us knowing.
                # This should not happen. Clean up the both sessions just in case.
                auth.logout(request)

            return

        try:
            rawphpsessid = request.COOKIES['PHPSESSID']
        except KeyError:
            return

        # Try to authenticate this user. It's pretty likely that
        # FlourishSessionBackend will succeed, but not guaranteed.
        #
        # Change the order of AUTHENTICATION_BACKENDS if this causes
        # you problems.
        user = auth.authenticate(request, rawphpsessid=rawphpsessid)
        if user:
            request.user = user
            auth.login(request, user)

    def __call__(self, request):
        self.check_phpsessid(request)
        return self.get_response(request)

