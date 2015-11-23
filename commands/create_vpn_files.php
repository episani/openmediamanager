<?php
 //create or open database
$dbconn = new PDO("sqlite:/var/www/sqlite/openmediamanager.sqlite");
function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 
$dbconn->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);


$sql="SELECT * FROM config";
$res=$dbconn->query($sql);
		
while ($row=$res->fetch( PDO::FETCH_ASSOC )){
	$applic_config[$row['config_name']]=stripslashes($row['config_value']);
}
		
if(isset($applic_config['SELECTED_VPN_UID']) && is_numeric($applic_config['SELECTED_VPN_UID']) && $applic_config['SELECTED_VPN_UID'] > 0 && $applic_config['SELECTED_VPN_UID'] != 999999){
		$sql="SELECT vpn_company.*,vpn_location.address_pool 
		FROM vpn_location 
		LEFT JOIN vpn_company ON vpn_company.uid=vpn_location.vpn_company_uid 
		WHERE vpn_location.uid=".$applic_config['SELECTED_VPN_UID'];
		$res=$dbconn->query($sql);
		$row=$res->fetch( PDO::FETCH_ASSOC );
		
		$directory="/etc/openvpn";
		
		if (!file_exists($directory)) {
    		mkdir($directory, 055, true);
		}
		//delete all files in directory
		$files = glob($directory.'/*'); // get all file names
		foreach($files as $file){ // iterate files
  			if(is_file($file)){
  				if($file != '/etc/openvpn/update-resolv-conf'){
    				unlink($file); // delete file
    			}
    		}
		}
		
		$extra_params="";

		$extra_params.="up /etc/openvpn/update-resolv-conf\ndown /etc/openvpn/update-resolv-conf\n";



		$address_pool_array=explode("\n",$row['address_pool']);
		
		foreach($address_pool_array as $address_pool){
			$extra_params.="remote ".$address_pool."\n";
		}

		

		
		//create password file
		if($row['username'] != "" && $row['password'] != ""){
			$file_name=$directory."/auth_".$row['uid'].".txt";
			$fp=fopen($file_name,"a");
			fwrite($fp,stripslashes($row['username'])."\n".stripslashes($row['password']));
			fclose($fp);
			$extra_params.="auth-user-pass auth_".$row['uid'].".txt\nauth-nocache\n";
		}	
		
		
		if($row['ca_crt'] != ""){
			$file_name=$directory."/ca.crt";
			$fp=fopen($file_name,"a");
			fwrite($fp,stripslashes($row['ca_crt']));
			fclose($fp);
			$extra_params.="ca ca.crt\n";
		}


		if($row['crl_pem'] != ""){
			$file_name=$directory."/crl.pem";
			$fp=fopen($file_name,"a");
			fwrite($fp,stripslashes($row['crl_pem']));
			fclose($fp);
			$extra_params.="crl-verify crl.pem\n";
		}
		
		$extra_params="up /etc/openvpn/update-resolv-conf\ndown /etc/openvpn/update-resolv-conf\n";
		
		//finally the openvpn config file

		$file_name=$directory."/openvpn.conf";
		$fp=fopen($file_name,"a");
		fwrite($fp,stripslashes($row['openvpn_parameters'])."\n".$extra_params);
		fclose($fp);
		echo '0';
		return;
}

if($applic_config['SELECTED_VPN_UID'] == 999999){
	echo '1';
	return;
}		
?>
