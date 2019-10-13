from django.conf.urls import url

from . import views
from . import api

app_name = 'lhsgocardless'
urlpatterns = [
    url(r'^$', views.index, name='index'),
    url(r'^webhook$', api.webhook),
]