from django.shortcuts import render
from django.http import HttpResponse, JsonResponse, HttpResponseRedirect
from django.contrib import auth
from django.contrib.auth.views import LoginView
from django.urls import reverse
from django.utils.http import urlencode

from urllib.parse import urljoin


def index(request):
    # This should never be reached - we use it to calculate the base PHP URL
    return HttpResponse('Django app index')

def flourish_url(path):
    base_url = reverse('lhsauth:index')
    return urljoin(base_url, path)


def session(request):
    data = {}

    if request.user.is_authenticated:
        if request.session.get_expire_at_browser_close():
            session_type = 'normal'
        else:
            session_type = 'persistent'

        session_expires = request.session.get_expiry_date()

        data = {
            'user_id': request.user.id,
            'type': session_type,
            'expires': session_expires.timestamp(),
        }

    return JsonResponse(data)

def logout(request):
    # All Flourish logout links should be pointed here to ensure users
    # are logged out of Django and Flourish when they request it.
    #
    # The Flourish session can also be destroyed in other pages, such
    # as kiosk/addcard.php, but those flows don't hit Django.

    auth.logout(request)
    return HttpResponseRedirect(flourish_url('/'))

class RedirectLoginView(LoginView):
    def dispatch(self, request, *args, **kwargs):
        redirect_to = self.get_success_url()
        if redirect_to in [self.request.path, flourish_url('/login.php')]:
            redirect_to = ''

        url = flourish_url('/login.php')
        if redirect_to:
            url += '?%s' % urlencode({'forward': redirect_to})

        return HttpResponseRedirect(url)

