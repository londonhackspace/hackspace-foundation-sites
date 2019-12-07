from functools import wraps
from datetime import date

from django.shortcuts import render
from django.shortcuts import redirect
from django.http import HttpResponse
from django.conf import settings
from django.contrib.auth.decorators import login_required
from django.urls import reverse as url_reverse

import gocardless_pro as gocardless
from gocardless_pro.errors import InvalidApiUsageError

from .models import Customer, Subscription
from lhspayments.models import Payment

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
    if customer_record is not None:
        try:
            mandate = gc_client.mandates.get(customer_record.mandate)
            context['mandate_status'] = mandate.status
            context['subscription'] = Subscription.objects.filter(customer=customer_record).first()

            if context['subscription'] is not None:
                context['gc_subscription'] = gc_client.subscriptions.get(context['subscription'].subscription)
                context['amount'] = int(context['gc_subscription'].amount/100)
        except InvalidApiUsageError:
            context['mandate_status'] = 'unknown'
    return render(request, 'gocardless/index.html', context=context)

# Handle subscription activities
@require_gocardless_user
def subscription(request):
    customer_record = Customer.objects.filter(user=request.user).first()

    if customer_record is None:
        return redirect('gocardless:index')

    if not 'subscription-amount' in request.POST:
        return redirect('gocardless:index')

    # GoCardless expects this in the smallest denomination
    # of the currency
    amount = int(request.POST['subscription-amount'])*100

    subscription_record = Subscription.objects.filter(customer=customer_record).first()
    
    # is this an update?
    if subscription_record is not None:
        params = {
            "amount": str(amount),
        }
        gc_client.subscriptions.update(subscription_record.subscription, params=params)
        return redirect('gocardless:index')        

    # TODO: If they're an existing subscriber, we need to find when their last payment was
    #       and use that for the start date of this

    params = {
        "amount": str(amount),
        "currency": "GBP",
        "name": "London Hackspace",
        "interval_unit": "monthly",
        "day_of_month": str(date.today().day),
        "links": {
        "mandate": customer_record.mandate
        }
    }

    sub = gc_client.subscriptions.create(params=params)

    sub_rec = Subscription.create_subscription_from_gocardless_subscription(sub)
    sub_rec.save()

    if not request.user.subscribed:
        # now create an immediate payment for the first month
        params = {
            "amount": str(amount),
            "currency": "GBP",
            "description": "First month payment",
            "links": {
                "mandate": customer_record.mandate
            }
        }

        payment = gc_client.payments.create(params=params)
        payment_rec = Payment.create_from_gocardless_payment(payment, request.user)
        payment_rec.save()
        request.user.subscribed = True
        request.user.save()

    return redirect('gocardless:index')

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
            "HackspaceId": str(request.user.id)
        }
    }
    gc_client.customers.update(c.customer, params=params)

    # now we redirect to the index page, which should now show the user as having a link
    return redirect('gocardless:index')
