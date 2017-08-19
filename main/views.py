from django.shortcuts import render
from django.http import HttpResponse, JsonResponse, HttpResponseNotFound, HttpResponseRedirect
from django.contrib import auth
from django.contrib.auth.views import LoginView
from django.urls import reverse
from django.utils.html import escape
from django.utils.http import urlencode

from urllib.parse import urljoin


def index(request):
    return HttpResponse('Django app index. This should be processed by PHP.')

def project(request, id):
    return HttpResponse('Project details page. This should be processed by PHP.')

def new_project(request):
    return HttpResponse('New project page. This should be processed by PHP.')

def profile(request, id):
    return HttpResponse(escape('Profile page for %r. This should be processed by PHP.' % id))

def fallback(request, url):
    return HttpResponseNotFound(escape('Fallback page for %r. This should be processed by PHP.' % url))


def report(request):
    return render(request, 'main/report.html')

