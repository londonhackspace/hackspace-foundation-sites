#!/bin/sh

# username uid nthash sshahash shell

/usr/sbin/smbldap-usershow $1 > /dev/null

if [ $? -ne 0 ] ; then
   # user dosn't exist, so create them
   /usr/sbin/smbldap-groupadd -ag $2 $1
   /usr/sbin/smbldap-useradd -a -c $1 -u $2 -g $2 -d /home/$1 -s $5 -A 1 $1
   if [ $? -ne 0 ] ; then
   	echo "Error adding user"
   	exit 1
   fi
else
   # user exists, so modify them.
   /usr/sbin/smbldap-usermod -c $1 -s $5 $1
fi


/var/www/hackspace-foundation-sites/bin/smbldap-passwd-hashes $1 $3 $4

if [ $? -ne 0 ] ; then
	echo "error adding password hashes"
	exit 1
else
	echo "User added ok"
fi
