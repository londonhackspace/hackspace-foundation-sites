from django.urls import path, re_path

from . import views

app_name = 'lhsauth'
urlpatterns = [
    path('', views.index, name='index'),
    path('session', views.session, name='session'),
    path('login', views.RedirectLoginView.as_view(), name='login'),
    re_path(r'^admin/login', views.RedirectLoginView.as_view()),
    path('logout', views.logout, name='logout'),
    re_path(r'^admin/logout', views.logout),
]

