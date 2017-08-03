# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "debian/jessie64"
  config.vm.box_version = "8.2.0"
  config.vm.provision :shell, path: 'bootstrap.sh'
  config.vm.hostname = 'lhs-www-test'
  config.vm.synced_folder ".", "/var/www/hackspace-foundation-sites", owner: "vagrant", group: "www-data"
  config.vm.network "forwarded_port", guest: 80, host: 8000
end
