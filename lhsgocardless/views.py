from functools import wraps

from django.shortcuts import render
from django.http import HttpResponse
from django.contrib.auth.decorators import login_required

# Helpers
def require_gocardless_user(f):
	@wraps(f)
	def decorator(request):
		return f(request)
	return login_required(decorator)

@require_gocardless_user
def index(request):
	return HttpResponse("Hi there. I'd like to talk to you about GoCardless.")