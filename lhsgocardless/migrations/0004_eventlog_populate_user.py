from django.db import migrations
import json

from ..management.commands.gocardless_log_set_users import add_users_to_eventlog


def add_users(apps, schema_editor):
    eventLog = apps.get_model("lhsgocardless", "EventLog")
    payment = apps.get_model("lhspayments", "Payment")
    customer = apps.get_model("lhsgocardless", "Customer")
    subscription = apps.get_model("lhsgocardless", "Subscription")
    add_users_to_eventlog(eventLog, payment, customer, subscription)

class Migration(migrations.Migration):
    dependencies = [
        ("lhsgocardless", "0003_eventlog_user"),        
        ("lhspayments", "0004_alter_payment_user"),
    ]

    operations = [
        migrations.RunPython(add_users),
    ]