from django.shortcuts import render
from django.http import HttpResponseRedirect
from django.contrib.auth import logout as auth_logout


def index(request):
    return 'Django app index'

def logout(request):
    # All Flourish logout links should be pointed here to ensure users
    # are logged out of Django and Flourish when they request it.
    #
    # The Flourish session can also be destroyed in other pages, such
    # as kiosk/addcard.php, but those flows don't hit Django.

    auth_logout()
    return HttpResponseRedirect(reverse('main:index'))

