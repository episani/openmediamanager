#!/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
/usr/bin/php /var/www/commands/restore_config.php && /etc/init.d/network restart && /etc/init.d/openvpn restart