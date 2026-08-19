from collections import OrderedDict

from django.db import models

from lhsauth.models import User

# Keep fairly raw GoCardless events, for logging and dupe detection
class EventLog(models.Model):
    id = models.CharField(primary_key=True, max_length=255)
    processed = models.BooleanField(default=False)
    created_at = models.DateTimeField()
    resource_type = models.CharField(max_length=255)
    action = models.CharField(max_length=255)
    links = models.TextField()
    details = models.TextField()
    user = models.ForeignKey(User, on_delete=models.PROTECT, null=True)

# gocardless has the concept of customers, mandates, and bank accounts.
# the only thing we really need here are mandates. We store customer ID as well in case it is useful.
# Using the redirect flow, we can only create all of these together, so storing them
# like this is not a problem.
class Customer(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    customer = models.CharField(max_length=255)
    mandate = models.CharField(max_length=255)
    created = models.DateTimeField(auto_now_add=True)

class Subscription(models.Model):
    customer = models.ForeignKey(Customer, on_delete=models.CASCADE)
    subscription = models.CharField(max_length=255)

    @staticmethod
    def create_subscription_from_gocardless_subscription(subscription):
        s = Subscription()
        s.subscription = subscription.id
        gc_mandate = subscription.links.mandate
        # if customer ends up being None, this is kinda bad.
        # TODO: handle this case
        s.customer = Customer.objects.filter(mandate=gc_mandate).first()
        return s
    
class oldLhsGocardless():
    MAX_LENGTH = 20

    def __init__(self):
        self.data = OrderedDict()

    def add(self, key, user_id):
        if (len(self.data) >= self.MAX_LENGTH):
            self.data.popitem(last=True)
        self.data[key] = user_id

    def get(self, key):
        return self.data.get(key, None) if key in self.data else None
    
old_subscriptions = oldLhsGocardless()
old_mandates = oldLhsGocardless()
