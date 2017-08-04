from django.shortcuts import render
from django.http import HttpResponse, JsonResponse, HttpResponseRedirect
from django.contrib import auth
from django.contrib.auth.views import LoginView
from django.urls import reverse
from django.utils.http import urlencode

from urllib.parse import urljoin


def index(request):
    return HttpResponse('Django app index')

