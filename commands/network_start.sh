#!/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

/etc/init.d/openvpn restart && /etc/init.d/hostapd restart && /sbin/ifconfig wlan0 192.168.10.1 && /var/www/openmediamanager/commands/vpnfw.sh && /etc/init.d/udhcpd restart && /usr/local/bin/mdns-repeater wlan0 eth0 && /etc/init.d/bind9 restart

