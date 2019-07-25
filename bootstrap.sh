#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive

curl -s https://nginx.org/keys/nginx_signing.key | apt-key add -

[[ -f /etc/apt/sources.list.d/nginx.list ]] || cat >/etc/apt/sources.list.d/nginx.list <<EOF
deb http://nginx.org/packages/debian/ jessie nginx
deb-src http://nginx.org/packages/debian/ jessie nginx
EOF

# Install required packages
apt-get update
apt-get install -y vim python3-virtualenv virtualenv python3-dev libpq-dev \
                   php5 php5-curl php-apc git postgresql-9.4 php5-pgsql \
                   ruby-erubis ruby-pg ruby-hpricot ruby-mail \
                   php5-fpm nginx libyaml-dev \
                   build-essential

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

# Configure php
sed -i~ -e "s/short_open_tag = Off/short_open_tag = On/" \
        -e "s/display_errors = Off/display_errors = On/" \
        -e "s/display_startup_errors = Off/display_startup_errors = On/" \
        /etc/php5/fpm/php.ini

sed -i~ -e "s#listen = /var/run/php5-fpm.sock#listen = 127.0.0.1:9000#" \
        -e "s/user = www-data/user = vagrant/" \
        /etc/php5/fpm/pool.d/www.conf

service php5-fpm restart

# Configure nginx
rm /etc/nginx/conf.d/default.conf
rm /etc/nginx/sites-enabled/default
cp /var/www/hackspace-foundation-sites/nginx-config-drop-in /etc/nginx/conf.d/lhs-www-test.conf

service nginx restart

cd /var/www/hackspace-foundation-sites
# The host OS is likely to be different to the guest
# --always-copy is to prevent symlinks on Windows
# This is awkward: we should consider alternatives
su vagrant -c 'virtualenv -p python3 --always-copy vagrant-env'
su vagrant -c 'vagrant-env/bin/pip install -r requirements.txt'

su vagrant -c 'vagrant-env/bin/python manage.py migrate sites'
su vagrant -c 'vagrant-env/bin/python manage.py migrate'
su vagrant -c 'vagrant-env/bin/python manage.py loaddata main/fixtures/*'

psql -U hackspace hackspace < /var/www/hackspace-foundation-sites/etc/create-flourish-tables.sql
psql -U hackspace hackspace < /var/www/hackspace-foundation-sites/etc/restore-column-defaults.sql
psql -U hackspace hackspace < /var/www/hackspace-foundation-sites/etc/restore-multicolumn-pks.sql

# Ensure there is a folder for the sessions
mkdir -p var/session

cat > /home/vagrant/.bash_profile <<EOF
cd /var/www/hackspace-foundation-sites/
. ./vagrant-env/bin/activate
echo
echo 'Now run ./manage.py runserver 9001'
EOF
