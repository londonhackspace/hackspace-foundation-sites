import json

from django.http import HttpResponse
from django.conf import settings
from django.views.decorators.csrf import csrf_exempt

from gocardless_pro import webhooks
from gocardless_pro.errors import InvalidSignatureError

from .models import EventLog

@csrf_exempt
def webhook(request):
    secret = settings.GOCARDLESS_CREDENTIALS['webhook_secret']

    signature = request.META["HTTP_WEBHOOK_SIGNATURE"]
    body = request.body.strip()

    try:
        events = webhooks.parse(body, secret, signature)

        for event in events:
            log_event = EventLog()
            log_event.id = event.id
            log_event.created_at = event.created_at
            log_event.resource_type = event.resource_type
            log_event.action = event.action
            log_event.links = json.dumps(event.links.attributes)
            log_event.details = json.dumps(event.details.attributes)
            log_event.save()

    except InvalidSignatureError:
        # as per the GoCardless docs, return 498 here
        return HttpResponse(status=498)

    return HttpResponse(status=204)
