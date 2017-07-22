from django.conf.urls import url
from django.contrib.auth import logout

from . import views

urlpatterns = [
    url(r'^$', views.index, name='index'),
    url(r'^logout/$', vies.logout, name='logout')
]

