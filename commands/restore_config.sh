#!/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
/usr/bin/php /var/www/openmediamanager/commands/restore_config.php && /usr/bin/php /var/www/openmediamanager/commands/create_network_files.php && /usr/bin/php /var/www/openmediamanager/commands/create_vpn_files.php && /etc/init.d/openvpn restart && /var/www/openmediamanager/commands/network_start.sh
