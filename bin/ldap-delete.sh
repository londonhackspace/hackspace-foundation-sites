#!/bin/sh
#
# just takes a username as an argument
# make sure you quote it!
#
#

/usr/sbin/smbldap-usershow $1 > /dev/null

if [ $? -ne 0 ] ; then
	echo "User not found"
	exit 1
fi

/usr/sbin/smbldap-userdel $1

if [ $? -eq 0 ] ; then
	echo "User deleted ok"
else
	echo "Error Deleting user"
	exit 1
fi

/usr/sbin/smbldap-groupdel $1

if [ $? -eq 0 ] ; then
	echo "Group deleted ok"
else
	echo "Error Deleting group"
	exit 1
fi
