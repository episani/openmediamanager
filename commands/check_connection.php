<?php
 //create or open database
$dbconn = new PDO("sqlite:/var/www/openmediamanager/sqlite/openmediamanager.sqlite");
function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 
$dbconn->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);

$applic_config=array();
$sql="SELECT * FROM config where config_name='SELECTED_VPN_UID'";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$applic_config[$row['config_name']]=stripslashes($row['config_value']);
}

if($applic_config['SELECTED_VPN_UID'] !=0 && $applic_config['SELECTED_VPN_UID'] !=43){
    exec("/var/www/openmediamanager/commands/check_connection_vpn.sh");
} 


		
?>