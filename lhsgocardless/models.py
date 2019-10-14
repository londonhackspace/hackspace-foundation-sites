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

# gocardless has the concept of customers, mandates, and bank accounts.
# the only thing we really need here are mandates. We store customer ID as well in case it is useful.
# Using the redirect flow, we can only create all of these together, so storing them
# like this is not a problem.
class Customer(models.Model):
    user = models.ForeignKey(User)
    customer = models.CharField(max_length=255)
    mandate = models.CharField(max_length=255)
    created = models.DateTimeField(auto_now_add=True)

class Subscription(models.Model):
    customer = models.ForeignKey(Customer)
    subscription = models.CharField(max_length=255)