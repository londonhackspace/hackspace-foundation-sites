from functools import wraps

from django.shortcuts import render
from django.shortcuts import redirect
from django.http import HttpResponse
from django.conf import settings
from django.contrib.auth.decorators import login_required
from django.urls import reverse as url_reverse

import gocardless_pro as gocardless

from .models import Customer

gc_client = gocardless.Client(
    access_token=settings.GOCARDLESS_CREDENTIALS['access_token'],
    environment=settings.GOCARDLESS_ENV
)

# Helpers
def require_gocardless_user(f):
    @wraps(f)
    def decorator(request):
        if not request.user.gocardless_user:
            # User isn't a gocardless user, send them to the main mambers page
            return redirect("/members/")
        return f(request)
    return login_required(decorator)

@require_gocardless_user
def index(request):
    customer_record = Customer.objects.filter(user=request.user).first()
    context = {
        'customer_record': customer_record,

    }
    return render(request, 'gocardless/index.html', context=context)

# Setup gocardless link for user
@require_gocardless_user
def setup_user(request):
    # before we do anything, make sure the user isn't already set up
    customer_record = Customer.objects.filter(user=request.user).first()
    if customer_record is not None:
        # user already has a link
        return redirect('gocardless:index')

    params = {
        "description": "London Hackspace",
        "session_token": request.session['PHPSESSID'],
        "success_redirect_url": request.build_absolute_uri(url_reverse('gocardless:setup_redirect')),
        "prefilled_customer": {
            "email": request.user.email
        }
    }

    flow = gc_client.redirect_flows.create(params=params)
    return redirect(flow.redirect_url)

# complete the flow created in setup_user
@require_gocardless_user
def setup_complete(request):
    # make sure we have the get parameter
    flow_id = request.GET.get('redirect_flow_id')
    # without the flow ID, send them to the beginning
    if flow_id is None:
        return redirect('gocardless:index')

    params = {
        "session_token": request.session['PHPSESSID'],
    }

    flow = gc_client.redirect_flows.complete(flow_id, params=params)

    # stash the details away
    c = Customer()
    c.user = request.user
    c.customer = flow.links.customer
    c.mandate = flow.links.mandate
    c.save()

    # now we have the customer object, we can add a custom field to link it to the LHS user
    params = {
        "metadata": {
                "HackspaceId": request.user.id
            }
    }
    gc_client.customers.update(c.customer, params=params)

    # now we redirect to the index page, which should now show the user as having a link
    #return redirect('gocardless:index')
    return redirect(flow.confirmation_url)
