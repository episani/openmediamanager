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
	$applic_config[$row['config_name']]=stripslashes($row['config_value']);
}

$applic_config_default=array();
foreach ($applic_config as $config_name => $config_value){
	$config_name_exploded=explode("_",$config_name);
	if($config_name_exploded[0] != "DEFAULT"){
		if(isset($applic_config['DEFAULT_'.$config_name])){
			$applic_config_default[$config_name]=$applic_config['DEFAULT_'.$config_name];
		}
	}
}


foreach ($applic_config_default as $config_name => $config_value){
	$sql="UPDATE config set config_value='".$config_value."' WHERE config_name='".$config_name."'";
	$res=$dbconn->query($sql);
}



//erase passwords from vpn companies
$sql="SELECT * FROM vpn_company";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	if($row['company_name'] != "VPNBook"){
		$sql="UPDATE vpn_company set username='',password='',tls_auth=''";
	} else {
		$sql="UPDATE vpn_company set username='vpnbook',password='sWedre3u',tls_auth=''";
	}
	//$res2=$dbconn->query($sql);
}

//reload config
$applic_config=array();
$sql="SELECT * FROM config";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$applic_config[$row['config_name']]=stripslashes($row['config_value']);
}




		
?>