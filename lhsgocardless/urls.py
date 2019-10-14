from django.conf.urls import url

from . import views
from . import api

app_name = 'gocardless'
urlpatterns = [
    url(r'^$', views.index, name='index'),
    url(r'setup$', views.setup_user, name='setup'),
    url(r'setupcomplete$', views.setup_complete, name='setup_redirect'),
    url(r'^webhook$', api.webhook),
]