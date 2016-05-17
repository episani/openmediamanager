#!/bin/sh
iptables -F
iptables -X
iptables -t nat -F
iptables -t nat -X
iptables -t mangle -F
iptables -t mangle -X

iptables -A INPUT -i tun0 -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -A INPUT -i tun0 -j DROP
iptables -A FORWARD -i tun0 -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i wlan0 -o tun0 -j ACCEPT
iptables -A FORWARD -i eth0 -o wlan0 -j ACCEPT
iptables -A FORWARD -i wlan0 -o eth0 -j ACCEPT
iptables -t nat -A POSTROUTING  -o tun0 -j MASQUERADE
iptables -t nat -A POSTROUTING  -o eth0 -j MASQUERADE

