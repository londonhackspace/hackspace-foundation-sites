from django.contrib import admin
from django.contrib.auth.admin import UserAdmin, Group
from django.contrib.auth.forms import UserChangeForm, UserCreationForm, AdminPasswordChangeForm
from django.utils.translation import ugettext_lazy as _

from .models import User


class LHSUserAdmin(UserAdmin):
    fieldsets = (
        (None, {'fields': ('email', 'password')}),
        (_('Personal info'), {'fields': ('full_name', 'address')}),
        (_('Attributes'), {'fields': ('hackney', 'terminated', 'admin')}),
        (_('Contact'), {'fields': ('nickname', 'irc_nick', 'ldapuser',
                        'emergency_name', 'emergency_phone')}),
    )
    add_fieldsets = (
        (None, {
            'classes': ('wide',),
            'fields': ('email', 'password1', 'password2'),
        }),
    )
    list_display = ('email', 'full_name', 'admin')
    list_filter = ('admin', 'terminated')
    search_fields = ('email', 'full_name')
    ordering = ('full_name',)
    filter_horizontal = ()

    def __init__(self, *args, **kwargs):
        super(UserAdmin, self).__init__(*args, **kwargs)


admin.site.register(User, LHSUserAdmin)
admin.site.unregister(Group)

