from django.db import models

# Keep fairly raw GoCardless events, for logging and dupe detection
class EventLog(models.Model):
	id = models.CharField(primary_key=True, max_length=255)
	processed = models.BooleanField(default=False)
	created_at = models.DateTimeField()
	resource_type = models.CharField(max_length=255)
	action = models.CharField(max_length=255)
	links = models.TextField()
	details = models.TextField()

