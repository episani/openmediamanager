<?php
$config['config']['run_cron_jobs']=0;
$config['config']['page']='index.php';
$config['config']['ext_name']="tx_onqmediamanager_pi1";
$config['config']['prefixId']=$config['config']['ext_name'];
$config['config']['gst']=10;
$config['config']['dbconn']=$dbconn;
if(!isset($_SESSION[$config['config']['prefixId'].'_login'])){
	$config['config']['sessiondata']=array();
} else {
	$config['config']['sessiondata']=$_SESSION[$config['config']['prefixId'].'_login'];
}

$config['config']['edit_button_caption']="edit";
$config['config']['view_button_caption']="view";
$config['config']['edit_button_class']="onqmediamanager_button";
$config['config']['delete_button_caption']="delete";
$config['config']['delete_button_class']="onqmediamanager_button";
$config['config']['find_button_caption']="Find";
$config['config']['find_button_class']="tx_onqmediamanager_button";
$config['config']['add_group']="add group";
$config['config']['add_record_button_caption']="add record";
$config['config']['add_record_button_class']="onqmediamanager_button";
$config['config']['previous_button_caption']="<< previous";
$config['config']['previous_button_class']="onqmediamanager_button";
$config['config']['next_button_caption']="next >>";
$config['config']['next_button_class']="onqmediamanager_button";
$config['config']['view_all_button_caption']="view all";
$config['config']['displaying_record_caption']="displaying records %d to %d of %d";
$config['config']['page_popup']='index.php';
$config['config']['security_mode']="administrator";

$config['config']['sections'][]="Main Modules|home";
$config['config']['sections'][]="Main Modules|openvpn_company";
$config['config']['sections'][]="Main Modules|openvpn_location";
$config['config']['sections'][]="Main Modules|network_settings";
//$config['config']['sections'][]="Main Modules|dyndns_settings";
$config['config']['sections'][]="Main Modules|info";
$config['config']['sections'][]="Main Modules|logout";
$config['config']['sections'][]="Main Modules|reboot";
$config['config']['sections'][]="Main Modules|shutdown";


$config['home']['nav_menu']=1;
$config['home']['nav_search']=0;
$config['home']['nav_add']=0;
$config['home']['security_mode']=$config['config']['security_mode'];
$config['home']['title']="Home";


$config['login']['nav_menu']=1;
$config['login']['nav_search']=0;
$config['login']['nav_add']=0;
$config['login']['security_mode']=$config['config']['security_mode'];
$config['login']['title']="Login";


$config['logout']['nav_menu']=1;
$config['logout']['nav_search']=0;
$config['logout']['nav_add']=0;
$config['logout']['security_mode']=$config['config']['security_mode'];
$config['logout']['title']="Logout";


$config['reboot']['nav_menu']=1;
$config['reboot']['nav_search']=0;
$config['reboot']['nav_add']=0;
$config['reboot']['security_mode']=$config['config']['security_mode'];
$config['reboot']['title']="Restart";


$config['shutdown']['nav_menu']=1;
$config['shutdown']['nav_search']=0;
$config['shutdown']['nav_add']=0;
$config['shutdown']['security_mode']=$config['config']['security_mode'];
$config['shutdown']['title']="Shutdown";




$config['network_settings']['nav_menu']=1;
$config['network_settings']['nav_search']=0;
$config['network_settings']['nav_add']=0;
$config['network_settings']['nav_filter']=0;
$config['network_settings']['security_mode']=$config['config']['security_mode'];
$config['network_settings']['title']="Network Settings";
$config['network_settings']['allow_edit']=1;
$config['network_settings']['allow_delete']=1;
$config['network_settings']['allow_add']=0;
$config['network_settings']['allow_update']=1;
$config['network_settings']['table']="network_settings";
$config['network_settings']['display']=10;
$config['network_settings']['var_edit'][]=",<h2>Ethernet Settings</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['network_settings']['var_edit'][]="network_settings,Mode,ETH0_NETWORK_TYPE,TEXT,selector_generic_noselect,,DHCP=DHCP|STATIC=STATIC,120";
$config['network_settings']['var_edit'][]="network_settings,IP Address (If STATIC),ETH0_STATIC_IP,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,Netmask (If STATIC),ETH0_NETMASK,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,Gateway (If STATIC),ETH0_GATEWAY,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]=",,,BREAKER,breaker,breaker_template,,";
$config['network_settings']['var_edit'][]=",<h2>WiFi Settings</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['network_settings']['var_edit'][]="network_settings,SSID (WiFi Network Name),WLAN0_VPN_SSID,TEXT,text_input_new,single_row,240,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi Password,WLAN0_VPN_PASS,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,Broadcast SSID,WLAN0_VPN_IGNORE_BROADCAST_SSID,TEXT,selector_generic_noselect,,1=No|0=Yes,120";
$config['network_settings']['var_edit'][]="network_settings,WiFi IP Address,WLAN0_VPN_ADDR,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi Netmask,WLAN0_VPN_NETMASK,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi DHCP Start,WLAN0_VPN_DHCP_START,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi DHCP End,WLAN0_VPN_DHCP_END,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi DHCP Gateway,WLAN0_VPN_DHCP_GATEWAY,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi DHCP DNS,WLAN0_VPN_DHCP_DNS,TEXT,text_input_new,,120,100";
$config['network_settings']['var_edit'][]="network_settings,WiFi Channel,WLAN0_CHANNEL,TEXT,selector_generic,,1=1|2=2|3=3|4=4|5=5|6=6|7=7|8=8|9=9|10=10|11=11|12=12|13=13,100";



$config['network_settings']['var_display']=array();
$config['network_settings']['var_req'][]="SSID is required|network_settings|WLAN0_VPN_SSID";
$config['network_settings']['var_req'][]="WiFi Password is required|network_settings|WLAN0_VPN_PASS";




$config['dyndns_settings']['nav_menu']=1;
$config['dyndns_settings']['nav_search']=0;
$config['dyndns_settings']['nav_add']=0;
$config['dyndns_settings']['nav_filter']=0;
$config['dyndns_settings']['security_mode']=$config['config']['security_mode'];
$config['dyndns_settings']['title']="Dynamic DNS Settings";
$config['dyndns_settings']['allow_edit']=1;
$config['dyndns_settings']['allow_delete']=1;
$config['dyndns_settings']['allow_add']=0;
$config['dyndns_settings']['allow_update']=1;
$config['dyndns_settings']['table']="dyndns_settings";
$config['dyndns_settings']['display']=10;
$config['dyndns_settings']['var_edit'][]=",<h2>Dynamic DNS Settings</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['dyndns_settings']['var_edit'][]="dyndns_settings,Dynamic DNS Enabled,DYNDNS_ENABLED,TEXT,selector_generic_noselect,,0=No|1=Yes,120";
$config['dyndns_settings']['var_edit'][]="dyndns_settings,Dynamic DNS Company,DYNDNS_COMPANY,TEXT,selector_generic,,dyndns=Dynamic DNS|noip=No IP,120";
$config['dyndns_settings']['var_edit'][]="dyndns_settings,Dynamic DNS Username,DYNDNS_USERNAME,TEXT,text_input_new,,120,100";
$config['dyndns_settings']['var_edit'][]="dyndns_settings,Dynamic DNS Password,DYNDNS_PASSWORD,TEXT,text_input_new,,120,100";
$config['dyndns_settings']['var_edit'][]="dyndns_settings,Dynamic DNS Hostname,DYNDNS_HOSTNAME,TEXT,text_input_new,,120,100";
$config['dyndns_settings']['var_display']=array();
$config['dyndns_settings']['var_req']=array();


$config['openvpn_company']['editable_field']=1;
$config['openvpn_company']['view_only_if_not_editable'][]='company_name';
$config['openvpn_company']['nav_menu']=1;
$config['openvpn_company']['nav_search']=0;
$config['openvpn_company']['nav_add']=1;
$config['openvpn_company']['nav_filter']=1;
$config['openvpn_company']['security_mode']=$config['config']['security_mode'];
$config['openvpn_company']['title']="OpenVPN Companies";
$config['openvpn_company']['allow_edit']=1;
$config['openvpn_company']['allow_delete']=1;
$config['openvpn_company']['allow_add']=1;
$config['openvpn_company']['allow_update']=1;
$config['openvpn_company']['table']="vpn_company";
$config['openvpn_company']['display']=10;
$config['openvpn_company']['var_edit'][]=",<h2>Company Name</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['openvpn_company']['var_edit'][]="vpn_company,OpenVPN Company,company_name,TEXT,text_input_new,,120,100";
$config['openvpn_company']['var_edit'][]=",<h2>Login Details</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['openvpn_company']['var_edit'][]="vpn_company,User Name,username,TEXT,text_input_new,,120,100";
$config['openvpn_company']['var_edit'][]="vpn_company,Password,password,TEXT,text_input_new,,120,100";
$config['openvpn_company']['var_edit'][]="vpn_company,TLS Auth,tls_auth,TEXT,textarea_input_new,single_row,245,200";
$config['openvpn_company']['var_edit'][]=",,,BREAKER,breaker,breaker_template,,";
$config['openvpn_company']['var_edit'][]="vpn_company,OpenVPN Parameters,openvpn_parameters,TEXT,textarea_input_new,single_row,245,200";
$config['openvpn_company']['var_edit'][]="vpn_company,CA Certificate (ca),ca_crt,TEXT,textarea_input_new,single_row,245,200";
$config['openvpn_company']['var_edit'][]=",,,BREAKER,breaker,breaker_template,,";
$config['openvpn_company']['var_edit'][]="vpn_company,Certificate (cert),cert,TEXT,textarea_input_new,single_row,245,200";
$config['openvpn_company']['var_edit'][]="vpn_company,Key,cert_key,TEXT,textarea_input_new,single_row,245,200";
$config['openvpn_company']['var_edit'][]="vpn_company,CRL Pem,crl_pem,TEXT,textarea_input_new,single_row,245,200";
//$config['openvpn_company']['var_display'][]="ID,vpn_company,uid,,left,100";
$config['openvpn_company']['var_display'][]="Company,vpn_company,company_name,,left,100";
$config['openvpn_company']['var_display'][]="User Name,vpn_company,username,,left,100";
$config['openvpn_company']['var_display'][]="Password,vpn_company,password,,left,100";		
$config['openvpn_company']['var_search'][]="vpn_company.company_name";
$config['openvpn_company']['var_search'][]="vpn_company.username";
$config['openvpn_company']['order_by'][]="vpn_company.company_name";
$config['openvpn_company']['var_req'][]="Company Name is required|vpn_company|company_name";
$config['openvpn_company']['var_req'][]="OpenVPN parameters are required|vpn_company|openvpn_parameters";


$config['openvpn_location']['editable_field']=1;
$config['openvpn_location']['view_only_if_not_editable'][]='location';
$config['openvpn_location']['nav_menu']=1;
$config['openvpn_location']['nav_search']=0;
$config['openvpn_location']['nav_add']=1;
$config['openvpn_location']['nav_filter']=1;
$config['openvpn_location']['security_mode']=$config['config']['security_mode'];
$config['openvpn_location']['title']="OpenVPN Locations";
$config['openvpn_location']['allow_edit']=1;
$config['openvpn_location']['allow_delete']=1;
$config['openvpn_location']['allow_add']=1;
$config['openvpn_location']['allow_update']=1;
$config['openvpn_location']['table']="vpn_location";
$config['openvpn_location']['display']=10;
$config['openvpn_location']['table_join'][]="vpn_company";
$config['openvpn_location']['table_join_fields'][]="vpn_company.company_name";
$config['openvpn_location']['table_join_relation'][]="vpn_company.uid=vpn_location.vpn_company_uid";



$config['openvpn_location']['var_edit'][]=",<h2>Open VPN Location</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['openvpn_location']['var_edit'][]="vpn_location,Location,location,TEXT,text_input_new,,120,100";
$config['openvpn_location']['var_edit'][]="vpn_location,Address Pool,address_pool,TEXT,textarea_input_new,single_row,245,200";

$config['openvpn_location']['var_edit'][]=",<h2>Open VPN Company</h2>,,SUBTITLE,subtitle,subtitle_template,,";
$config['openvpn_location']['var_edit'][]="vpn_location,VPN Company Name,vpn_company_uid,TEXT,selector_new,,vpn_company=uid=company_name,100,100";
//$config['openvpn_location']['var_display'][]="ID,vpn_location,uid,,left,100";
$config['openvpn_location']['var_display'][]="Company,vpn_company,company_name,,left,100";
$config['openvpn_location']['var_display'][]="Location,vpn_location,location,,left,100";
$config['openvpn_location']['var_search'][]="vpn_company.company_name";
$config['openvpn_location']['var_search'][]="vpn_location.location";
$config['openvpn_location']['order_by'][]="vpn_company.company_name";
$config['openvpn_location']['order_by'][]="vpn_location.location";
$config['openvpn_location']['var_req'][]="VPN Company Name is required|vpn_location|vpn_company_uid";
$config['openvpn_location']['var_req'][]="Location is required|vpn_location|location";
$config['openvpn_location']['var_req'][]="Address Pool is required|vpn_location|address_pool";




$config['info']['nav_menu']=1;
$config['info']['nav_search']=0;
$config['info']['nav_add']=0;
$config['info']['security_mode']=$config['config']['security_mode'];
$config['info']['title']="System Information";


?>

