#!/bin/sh

PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

cd /var/www/openmediamanager/
chown -R openmediavpn:openmediavpn * 
chown -R root:root bin 
chmod +s bin/* 
chown -R root:root bin_source
chmod -R 700 bin_source/* 
chmod 700 commands/*.sh 
chown root:root commands/*.sh
chmod 644 commands/*.php commands/index.html 
chown -R www-data:www-data sqlite
chown openmediavpn:openmediavpn sqlite/*.php


cd /var/www/openmediamanager/bin_source
gcc reboot.c -o reboot
gcc shutdown.c -o shutdown
gcc create_vpn_files.c -o create_vpn_files
gcc create_network_files.c -o create_network_files
gcc create_wifi_settings.c -o create_wifi_settings

cp reboot ../bin/
cp shutdown ../bin/
cp create_vpn_files ../bin/
cp create_network_files ../bin/
cp create_wifi_settings ../bin/
cd /var/www/openmediamanager/bin
chown root:root *
chmod 755 *
chmod +s *
chmod 444 index.html
chmod u-s index.html
