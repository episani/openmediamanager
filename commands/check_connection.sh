#!/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
ping -I tun1 -c 3 8.8.8.8 > /dev/null
if [ $? -eq 0 ]; then 
echo "Ping Ok"
else
/sbin/ifdown eth0 && /sbin/ifup eth0 && /etc/init.d/openvpn restart && /var/www/openmediamanager/commands/network_start.sh
fi

