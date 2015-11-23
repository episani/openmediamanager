#!/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

/usr/bin/php /var/www/commands/create_wifi_settings.php
/var/www/commands/network_start.sh
