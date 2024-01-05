from django.urls import path

from . import views
from . import api

app_name = 'gocardless'
urlpatterns = [
    path('', views.index, name='index'),
    path('setup', views.setup_user, name='setup'),
    path('setupcomplete', views.setup_complete, name='setup_redirect'),
    path('subscription', views.subscription, name='subscription'),
    path('reset', views.reset, name='reset'),
    path('removesub', views.remove_sub, name='remove_sub'),
    path('webhook', api.webhook),
]