from django.conf.urls import url

from . import views

app_name = 'lhsauth'
urlpatterns = [
    url(r'^$', views.index, name='index'),
    url(r'^session$', views.session, name='session'),
    url(r'^login$', views.RedirectLoginView.as_view(), name='login'),
    url(r'^admin/login', views.RedirectLoginView.as_view()),
    url(r'^logout$', views.logout, name='logout'),
    url(r'^admin/logout', views.logout),
]

