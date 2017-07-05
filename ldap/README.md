Test these first in a temporary directory.

For Debian Jessie
-----------------
```
Mark@turing:~$ cd /usr/sbin
Mark@turing:/usr/sbin$ for i in smbldap-{passwd,userdel,usermod}.patch; do sudo patch < /var/www/hackspace-foundation-sites/ldap/$i; done
patching file smbldap-passwd
patching file smbldap-userdel
patching file smbldap-usermod
Mark@turing:/usr/sbin$ cd /usr/share/perl5/
Mark@turing:/usr/share/perl5$ sudo patch < /var/www/hackspace-foundation-sites/ldap/smbldap_tools.pm.jessie.patch
patching file smbldap_tools.pm
```
