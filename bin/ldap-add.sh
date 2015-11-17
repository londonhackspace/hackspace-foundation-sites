#!/bin/sh

# username uid nthash sshahash shell email

uid=$2

#
# LHS uid's start at 100000
#
if [ $uid -lt 100000 ]  ; then
	logger -p auth.crit attempt to add low uid: "$uid" : "$1" "$6"
	exit 1
fi

/usr/sbin/smbldap-usershow "$1" > /dev/null

if [ $? -ne 0 ] ; then
   # user dosn't exist, so create them
   /usr/sbin/smbldap-groupadd -ag "$2" "$1"
   if [ -z "$6" ] ; then
	   /usr/sbin/smbldap-useradd -a -c "$1" -u "$2" -g "$2" -G Members  -d "/home/$1" -s "$5" -A 1 "$1"
   else
	   /usr/sbin/smbldap-useradd -a -c "$1" -u "$2" -g "$2" -G Members -d "/home/$1" -s "$5" -A 1 -M "$6" "$1"
   fi
   if [ $? -ne 0 ] ; then
   	echo "Error adding user"
	echo "$1" "$2" "$3" "$4" "$5" "$6"
   	exit 1
   fi
else
   # user exists, so modify them.
   if [ -z "$6" ] ; then
	err=`/usr/sbin/smbldap-usermod -c "$1" -s "$5" -G +Members "$1"`
   else
	err=`/usr/sbin/smbldap-usermod -c "$1" -s "$5" -G +Members -M "$6" "$1"`
   fi

   if [ ! -z "$err" ] ; then
	echo $err | grep ' already member of the group "Members"' > /dev/null
	if [ $? -ne 0 ] ; then
		echo "something went wrong with modifing the member"
		echo $err
	fi
   fi
fi


/var/www/hackspace-foundation-sites/bin/smbldap-passwd-hashes "$1" "$3" "$4"

if [ $? -ne 0 ] ; then
	echo "error adding password hashes"
	exit 1
else
	echo "User added ok"
fi
