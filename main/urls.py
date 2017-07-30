from django.conf.urls import url

from . import views

app_name = 'main'
urlpatterns = [
    url(r'^$', views.index, name='index'),
    url(r'^session$', views.session, name='session'),
    url(r'^logout$', views.logout, name='logout')
]

