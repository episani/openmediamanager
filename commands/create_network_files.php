<?php
 //create or open database
$dbconn = new PDO("sqlite:/var/www/openmediamanager/sqlite/openmediamanager.sqlite");
function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 
$dbconn->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);


$applic_config=array();
$sql="SELECT * FROM config";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$applic_config['###'.strtoupper($row['config_name']).'###']=stripslashes($row['config_value']);
}


$applic_config_default=array();
foreach ($applic_config as $config_name => $config_value){
	$config_name=str_replace("#","",$config_name);
        $config_name_exploded=explode("_",$config_name);
        if($config_name_exploded[0] != "DEFAULT"){
                if(isset($applic_config['###DEFAULT_'.$config_name.'###']) && $applic_config['###'.$config_name.'###']==""){
                        $applic_config_default['###'.$config_name.'###']=$applic_config['###DEFAULT_'.$config_name.'###'];
                } else {
                        $applic_config_default['###'.$config_name.'###']=$applic_config['###'.$config_name.'###'];
                }
        }
}




$sql="SELECT * FROM files_template WHERE file_code='hostapd.conf'";
$res=$dbconn->query($sql);
$row=$res->fetch( PDO::FETCH_ASSOC );
$row['file_content']=stripslashes($row['file_content']);
$row['file_content']=str_replace("\r","",stripslashes($row['file_content']));

foreach($applic_config as $marker => $value){
	$row['file_content']=str_replace($marker,$value,$row['file_content']);
}



if(isset($row['file_content']) && $row['file_content'] != "" && isset($row['file_name']) && $row['file_name'] !=""){
	if(file_exists($row['file_name']) ){
		unlink($row['file_name']);
	}

	$fp=fopen($row['file_name'],"a");
	fwrite($fp,$row['file_content']);
	fclose($fp);

}


// now need to create the network intefaces files
// there are 2 templates: 1 for dhcp and one for static
// the default template is the dhcp one

$applic_config_marker=array();
$file_code="interfaces_dhcp";

//first eth0
if($applic_config['###ETH0_NETWORK_TYPE###']=="STATIC"){
        //need all the eth0 parameters, otherwise, I'll change it to DHCP which is the default
        if($applic_config['###ETH0_STATIC_IP###'] !="" &&
                $applic_config['###ETH0_NETMASK###'] !="" &&
                $applic_config['###ETH0_NETWORK###'] !="" &&
                $applic_config['###ETH0_GATEWAY###'] !=""

        ){
		$file_code="interfaces_static";
                $applic_config_marker['###ETH0_NETWORK_TYPE###']=$applic_config['###ETH0_NETWORK_TYPE###'];
                $applic_config_marker['###ETH0_STATIC_IP###']=$applic_config['###ETH0_STATIC_IP###'];
                $applic_config_marker['###ETH0_NETMASK###']=$applic_config['###ETH0_NETMASK###'];
                $applic_config_marker['###ETH0_NETWORK###']=$applic_config['###ETH0_NETWORK###'];
                $applic_config_marker['###ETH0_GATEWAY###']=$applic_config['###ETH0_GATEWAY###'];
        } else {
            $applic_config_marker['###ETH0_NETWORK_TYPE###']=$applic_config_default['###ETH0_NETWORK_TYPE###'];
            $applic_config_marker['###ETH0_STATIC_IP###']=$applic_config_default['###ETH0_STATIC_IP###'];
            $applic_config_marker['###ETH0_NETMASK###']=$applic_config_default['###ETH0_NETMASK###'];
            $applic_config_marker['###ETH0_NETWORK###']=$applic_config_default['###ETH0_NETWORK###'];
            $applic_config_marker['###ETH0_GATEWAY###']=$applic_config_default['###ETH0_GATEWAY###'];
        }
} else {
    $applic_config_marker['###ETH0_NETWORK_TYPE###']=$applic_config_default['###ETH0_NETWORK_TYPE###'];
    $applic_config_marker['###ETH0_STATIC_IP###']=$applic_config_default['###ETH0_STATIC_IP###'];
    $applic_config_marker['###ETH0_NETMASK###']=$applic_config_default['###ETH0_NETMASK###'];
    $applic_config_marker['###ETH0_NETWORK###']=$applic_config_default['###ETH0_NETWORK###'];
    $applic_config_marker['###ETH0_GATEWAY###']=$applic_config_default['###ETH0_GATEWAY###'];
}



//now wlan0 && DHCP
//need all the wlan0 parameters, otherwise, I'll change to the defaults
if($applic_config['###WLAN0_VPN_ADDR###'] !="" &&
        $applic_config['###WLAN0_VPN_NETMASK###'] !="" &&
        $applic_config['###WLAN0_VPN_DHCP_START###'] !="" &&
        $applic_config['###WLAN0_VPN_DHCP_END###'] !="" &&
        $applic_config['###WLAN0_VPN_DHCP_DNS###'] !="" &&
        $applic_config['###WLAN0_VPN_NETMASK###'] !="" &&
        $applic_config['###WLAN0_VPN_DHCP_GATEWAY###'] !=""
){
        $applic_config_marker['###WLAN0_VPN_ADDR###']=$applic_config['###WLAN0_VPN_ADDR###'];
        $applic_config_marker['###WLAN0_VPN_NETMASK###']=$applic_config['###WLAN0_VPN_NETMASK###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_START###']=$applic_config['###WLAN0_VPN_DHCP_START###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_END###']=$applic_config['###WLAN0_VPN_DHCP_END###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_DNS###']=$applic_config['###WLAN0_VPN_DHCP_DNS###'];
        $applic_config_marker['###WLAN0_VPN_NETMASK###']=$applic_config['###WLAN0_VPN_NETMASK###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_GATEWAY###']=$applic_config['###WLAN0_VPN_DHCP_GATEWAY###'];
} else {
        $applic_config_marker['###WLAN0_VPN_ADDR###']=$applic_config_default['###WLAN0_VPN_ADDR###'];
        $applic_config_marker['###WLAN0_VPN_NETMASK###']=$applic_config_default['###WLAN0_VPN_NETMASK###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_START###']=$applic_config_default['###WLAN0_VPN_DHCP_START###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_END###']=$applic_config_default['###WLAN0_VPN_DHCP_END###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_DNS###']=$applic_config_default['###WLAN0_VPN_DHCP_DNS###'];
        $applic_config_marker['###WLAN0_VPN_NETMASK###']=$applic_config_default['###WLAN0_VPN_NETMASK###'];
        $applic_config_marker['###WLAN0_VPN_DHCP_GATEWAY###']=$applic_config_default['###WLAN0_VPN_DHCP_GATEWAY###'];
}


$sql="SELECT * FROM files_template WHERE file_code='".$file_code."'";
$res=$dbconn->query($sql);
$row=$res->fetch( PDO::FETCH_ASSOC );
$row['file_content']=stripslashes($row['file_content']);
$row['file_content']=str_replace("\r","",stripslashes($row['file_content']));
foreach($applic_config_marker as $marker => $value){
	$row['file_content']=str_replace($marker,$value,$row['file_content']);
}

if(isset($row['file_content']) && $row['file_content'] != "" && isset($row['file_name']) && $row['file_name'] !=""){
	if(file_exists($row['file_name']) ){
		unlink($row['file_name']);
	}

	$fp=fopen($row['file_name'],"a");
	fwrite($fp,$row['file_content']);
	fclose($fp);

}



$sql="SELECT * FROM files_template WHERE file_code='dhcpd.conf'";
$res=$dbconn->query($sql);
$row=$res->fetch( PDO::FETCH_ASSOC );
$row['file_content']=stripslashes($row['file_content']);
$row['file_content']=str_replace("\r","",stripslashes($row['file_content']));
foreach($applic_config_marker as $marker => $value){
	$row['file_content']=str_replace($marker,$value,$row['file_content']);
}

if(isset($row['file_content']) && $row['file_content'] != "" && isset($row['file_name']) && $row['file_name'] !=""){
	if(file_exists($row['file_name']) ){
		unlink($row['file_name']);
	}

	$fp=fopen($row['file_name'],"a");
	fwrite($fp,$row['file_content']);
	fclose($fp);

}




$sql="SELECT * FROM files_template WHERE file_code='network_start.sh'";
$res=$dbconn->query($sql);
$row=$res->fetch( PDO::FETCH_ASSOC );
$row['file_content']=stripslashes($row['file_content']);
$row['file_content']=str_replace("\r","",stripslashes($row['file_content']));
foreach($applic_config as $marker => $value){
	$row['file_content']=str_replace($marker,$value,$row['file_content']);
}



if(isset($row['file_content']) && $row['file_content'] != "" && isset($row['file_name']) && $row['file_name'] !=""){
	if(file_exists($row['file_name']) ){
		unlink($row['file_name']);
	}

	$fp=fopen($row['file_name'],"a");
	fwrite($fp,$row['file_content']);
	fclose($fp);
	chmod($row['file_name'],0700);
	chown($row['file_name'],'root');
	chgrp($row['file_name'],'root');

}		



	
?>
