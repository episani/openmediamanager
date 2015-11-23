#!/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
/usr/bin/php /var/www/commands/create_network_files.php && /etc/init.d/network restart && /etc/init.d/openvpn restart