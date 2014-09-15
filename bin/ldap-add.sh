#!/bin/sh

/usr/sbin/smbldap-usershow $1 > /dev/null

if [ $? -ne 0 ] ; then
   # user dosn't exist, so create them
   /usr/sbin/smbldap-useradd -a -c $1 -d /home/$1 -s /bin/bash -A 1 $1
   if [ $? -ne 0 ] ; then
   	echo "Error adding user"
   	exit 1
   fi
fi

/var/www/hackspace-foundation-sites/bin/smbldap-passwd-hashes $1 $2 $3

if [ $? -ne 0 ] ; then
	echo "error adding password hashes"
	exit 1
else
	echo "User added ok"
fi
