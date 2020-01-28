from .email import new_user_email, lapse_email

import subprocess

def on_new_member(user):
    user.subscribed = True
    user.save()
    new_user_email(user)

    if user.ldapuser and user.ldapnthash and user.ldapsshahash and user.ldapshell and user.ldapemail:
        subprocess.run("/var/www/hackspace-foundation-sites/bin/ldap-add.sh",
                user.ldapuser,
                str(user.id+100000),
                user.ldapnthash,
                user.ldapsshahash,
                user.ldapshell,
                user.ldapemail)

def unsubscribe_member(user):
    user.subscribed = False
    user.save()

    if user.ldapuser is not None:
        print("Deleting LDAP account: %s" % (user.ldapuser,))
        subprocess.run("/var/www/hackspace-foundation-sites/bin/ldap-delete.sh", user.ldapuser)
    
    lapse_email(user)