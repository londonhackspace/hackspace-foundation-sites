#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive

# Install required packages
apt-get update
apt-get install -y vim python3-virtualenv virtualenv python3-dev libpq-dev
apt-get install -y php5 php5-curl php-apc git postgresql-9.4 php5-pgsql
apt-get install -y ruby-erubis ruby-pg ruby-hpricot ruby-mail

su postgres -c 'createuser -s vagrant'
su postgres -c 'createuser hackspace'
su postgres -c 'createdb -O hackspace hackspace'

cat > /etc/postgresql/9.4/main/pg_hba.conf <<EOF
local   hackspace       hackspace                               trust
host    hackspace       hackspace       127.0.0.1/32            trust
host    hackspace       hackspace       ::1/128                 trust
local   all             vagrant                                 trust

local   all             postgres                                peer
local   all             all                                     peer
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
EOF

service postgresql reload

psql -U hackspace hackspace < /var/www/hackspace-foundation-sites/etc/schema.sql

# Configure php
sed -i~ "s/short_open_tag = Off/short_open_tag = On/g" /etc/php5/apache2/php.ini
sed -i~ "s/display_errors = Off/display_errors = On/g" /etc/php5/apache2/php.ini
sed -i~ "s/display_startup_errors = Off/display_startup_errors = On/g" /etc/php5/apache2/php.ini

# Configure apache
a2enmod rewrite
a2enmod expires
cp /var/www/hackspace-foundation-sites/apache-config-drop-in /etc/apache2/sites-available/000-default.conf
service apache2 restart

cat > /home/vagrant/.bash_profile <<EOF
cd /var/www/hackspace-foundation-sites/
. ./env/bin/activate
EOF
