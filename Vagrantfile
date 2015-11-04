# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "debian/jessie64"
  config.vm.network "public_network"
  config.vm.provision :shell, path: 'bootstrap.sh'
  config.vm.hostname = 'lhs-www-test'
  config.vm.synced_folder ".", "/var/www/hackspace-foundation-sites", owner: "www-data", group: "www-data"
end
