<?php
 //create or open database
$dbconn = new PDO("sqlite:/var/www/openmediamanager/sqlite/openmediamanager.sqlite");
function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 
$dbconn->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);


$sql="SELECT * FROM config";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$applic_config['###'.strtoupper($row['config_name']).'###']=stripslashes($row['config_value']);
}


$sql="SELECT * FROM files_template WHERE file_code='hostapd.conf'";
$res=$dbconn->query($sql);
$row=$res->fetch( PDO::FETCH_ASSOC );
$row['file_content']=stripslashes($row['file_content']);
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


$sql="SELECT * FROM files_template WHERE file_code='network_start.sh'";
$res=$dbconn->query($sql);
$row=$res->fetch( PDO::FETCH_ASSOC );
$row['file_content']=stripslashes($row['file_content']);
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