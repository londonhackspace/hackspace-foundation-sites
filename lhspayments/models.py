from django.db import models

from lhsauth.models import User

class Payment(models.Model):
    TYPE_BANKPAYMENT = 1
    TYPE_GOCARDLESS = 2

    STATE_PENDING = 1
    STATE_SUCCEEDED = 2
    STATE_FAILED = 3
    
    PAYMENT_TYPES = [
        (TYPE_BANKPAYMENT, "Bank Payment"),
        (TYPE_GOCARDLESS, "GoCardless")
    ]

    PAYMENT_STATES = [
        (STATE_PENDING, "Pending"),
        (STATE_SUCCEEDED, "Succeeded"),
        (STATE_FAILED, "Failed")
    ]

    id = models.CharField(primary_key=True, max_length=255)
    timestamp = models.DateTimeField()
    user = models.ForeignKey(User)
    amount = models.DecimalField(max_digits=6, decimal_places=2)
    payment_type = models.IntegerField(choices=PAYMENT_TYPES)
    payment_state = models.IntegerField(choices=PAYMENT_STATES)

