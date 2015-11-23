#!/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

a=$(/usr/bin/php /var/www/commands/create_vpn_files.php)

if [ $a -eq 0 ]
then
  /etc/init.d/openvpn restart
  /var/www/commands/vpnfw.sh
else
  /etc/init.d/openvpn stop
  /var/www/commands/localfw.sh
fi


