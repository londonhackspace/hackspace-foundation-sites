from django.urls import path, re_path

from . import views

app_name = 'main'
urlpatterns = [
    path('', views.index, name='index'),
    re_path(r'^storage/([0-9]+)$', views.project, name='project'),
    re_path(r'^storage/edit.php$', views.new_project, name='new_project'),
    re_path(r'^members/profile/([0-9]+)$', views.profile, name='profile'),
    path('reports/report', views.report, name='report'),
    re_path(r'^(.*)$', views.fallback, name='fallback'),
]

