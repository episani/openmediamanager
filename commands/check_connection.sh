#!/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
success=0
while [ $success -ne 1 ]; do
    ping -I tun1 -c 3 8.8.8.8
    if [ $? -eq  0 ]; then
	success=1;
    else
	/etc/init.d/openvpn restart
	success=1	
    fi
done

