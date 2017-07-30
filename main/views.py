from django.shortcuts import render
from django.http import HttpResponse, JsonResponse, HttpResponseRedirect
from django.contrib.auth import logout as auth_logout
from django.urls import reverse

def index(request):
    # This should never be reached, but we keep it for redirecting to
    return HttpResponse('Django app index')

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

    auth_logout(request)
    return HttpResponseRedirect(reverse('main:index'))

