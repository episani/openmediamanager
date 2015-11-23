<?php
 //create or open database
$dbconn = new PDO("sqlite:/var/www/openmediamanager/sqlite/openmediamanager.sqlite");
function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 
$dbconn->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);


$sql="SELECT * FROM config";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$applic_config[$row['config_name']]=stripslashes($row['config_value']);
}

$applic_config_default=array();
foreach ($applic_config as $config_name => $config_value){
	$config_name_exploded=explode("_",$config_name);
	if($config_name_exploded[0] != "DEFAULT"){
		if(isset($applic_config['DEFAULT_'.$config_name]) && $applic_config[$config_name]==""){
			$applic_config_default[$config_name]=$applic_config['DEFAULT_'.$config_name];
		} else {
			$applic_config_default[$config_name]=$applic_config[$config_name];
		}
	}
}



//now select templates

$sql="SELECT * FROM files_template WHERE section='network'";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$templates[$row['file_code']]=$row;
}


$applic_config_marker=array();
$template_interfaces="DHCP";

//first eth0
if($applic_config_default['ETH0_NETWORK_TYPE']=="STATIC"){
	//need all the eth0 parameters, otherwise, I'll change it to DHCP which is the default
	if($applic_config['ETH0_STATIC_IP'] !="" &&
		$applic_config['ETH0_NETMASK'] !="" &&
		$applic_config['ETH0_NETWORK'] !="" &&
		$applic_config['ETH0_GATEWAY'] !=""
		
	){
		$template_interfaces="STATIC";
		$applic_config_marker['###ETH0_STATIC_IP###']=$applic_config['ETH0_STATIC_IP'];
		$applic_config_marker['###ETH0_NETMASK###']=$applic_config['ETH0_NETMASK'];
		$applic_config_marker['###ETH0_NETWORK###']=$applic_config['ETH0_NETWORK'];
		$applic_config_marker['###ETH0_GATEWAY###']=$applic_config['ETH0_GATEWAY'];
	}
}


//now wlan0 && DHCP
	//need all the wlan0 parameters, otherwise, I'll change to the defaults
if($applic_config['WLAN0_VPN_ADDR'] !="" &&
	$applic_config['WLAN0_VPN_NETMASK'] !="" &&
	$applic_config['WLAN0_VPN_DHCP_START'] !="" &&
	$applic_config['WLAN0_VPN_DHCP_END'] !="" &&
	$applic_config['WLAN0_VPN_DHCP_DNS'] !="" &&
	$applic_config['WLAN0_VPN_NETMASK'] !="" &&
	$applic_config['WLAN0_VPN_DHCP_GATEWAY'] !=""
){
	$applic_config_marker['###WLAN0_VPN_ADDR###']=$applic_config['WLAN0_VPN_ADDR'];
	$applic_config_marker['###WLAN0_VPN_NETMASK###']=$applic_config['WLAN0_VPN_NETMASK'];
	$applic_config_marker['###WLAN0_VPN_DHCP_START###']=$applic_config['WLAN0_VPN_DHCP_START'];
	$applic_config_marker['###WLAN0_VPN_DHCP_END###']=$applic_config['WLAN0_VPN_DHCP_END'];
	$applic_config_marker['###WLAN0_VPN_DHCP_DNS###']=$applic_config['WLAN0_VPN_DHCP_DNS'];
	$applic_config_marker['###WLAN0_VPN_NETMASK###']=$applic_config['WLAN0_VPN_NETMASK'];
	$applic_config_marker['###WLAN0_VPN_DHCP_GATEWAY###']=$applic_config['WLAN0_VPN_DHCP_GATEWAY'];
}


foreach ($templates as $template_code=>$template_array){
	foreach($applic_config_marker as $marker => $marker_value){
			$template_array['file_content']=str_replace($marker,$marker_value,$template_array['file_content']);	
	}
	$templates[$template_code]['file_content']=$template_array['file_content'];
}

		
?>