<?php

require_once "class.onqbasic_editor.php";

class openmediamanager extends onqbasic_editor {

	var $config_all=array();
	var $config=array();
	var $applic_config=array();
	var $settings=array();
	var $input=array();
	var $in=array();
	var $piVars=array();
	var $body;


	function openmediamanager($config,$input,$in) {
	
			

			$this->config_all=$config;
			if(isset($config[$input['section_name']])){
	  			$this->config=$config[$input['section_name']];
	  		} else {
	  			$this->config=array();
	  		}
			$this->settings=$config['config'];
			$this->input=$input;
			$this->in=$in;
			
		
			if($this->settings['run_cron_jobs'] ==1) {		
				$this->cronjobs();
			}
		
		
			if(substr($this->input['action'],0,11) =="set_filter_"){
				$this->set_filter(substr($this->input['action'],11));
			}		


		switch ($this->input['section_name']){

			case "home":
				$this->body.=$this->home();
				break;
			
			case "navigation_holder":
				$this->navigation_holder();
				break;

			case "wifi_settings":
				$this->main();
				break;
				
			case "network_settings":
				$this->main();
				break;				

			case "dyndns_settings":
				$this->main();
				break;

			case "openvpn_company":
				$this->main();
				break;

			case "openvpn_location":
				$this->main();
				break;

			case "info":
				$this->body.=$this->info();
				break;
				
			case "logout":
				$this->body.=$this->logout_box();
				break;
				
			case "logout_action":
				$this->logout_action();
				break;				
				
			case "login":
				$this->login_box();
				break;

			case "reboot":
				$this->body.=$this->reboot();
				break;
				
			case "shutdown":
				$this->body.=$this->shutdown();
				break;												

		}




	}



	function show_errors($error){
		$body='';
		if(is_array($error)){
			$body.='<ul>';
			foreach($error as $value){
				$body.='<li>'.$value.'</li>';
			}
			$body.='</ul>';
		}
		return $body;
	}



	function reboot() {
		$body="";
		
		if(!isset($this->input['submit'])){
			$body.='
			<div style="clear:both;padding-top:20px">			
			<p>Are you sure you want to Restart?';
			$body.='</p>
			<form action="'.$this->settings['page'].'" target="_top" method="post">
			<table border="0">
			<tr><td>
			<input type="submit" name="'.$this->settings['prefixId'].'[submit]" value="Restart" />
			<input type="hidden" name="'.$this->settings['prefixId'].'[section_name]" value="'.$this->input['section_name'].'" />
			</td></tr>
			</table>
			</form>
			</div>';
		} else {
			$body.='<div style="clear:both;padding-top:20px"><p>The system is restarting...</p></div>';
			exec("/var/www/bin/openmediamanager/reboot");
			
			
		}
		return $body;		
	}






	function shutdown() {
		$body="";
		
		if(!isset($this->input['submit'])){
			$body.='
			<div style="clear:both;padding-top:20px">			
			<p>Are you sure you want to Shutdown?';
			$body.='</p>
			<form action="'.$this->settings['page'].'" target="_top" method="post">
			<table border="0">
			<tr><td>
			<input type="submit" name="'.$this->settings['prefixId'].'[submit]" value="Shutdown" />
			<input type="hidden" name="'.$this->settings['prefixId'].'[section_name]" value="'.$this->input['section_name'].'" />
			</td></tr>
			</table>
			</form>
			</div>';
		} else {
			$body.='<div style="clear:both;padding-top:20px"><p>The system is shutting down...</p></div>';
			exec("/var/www/bin/openmediamanager/shutdown");

		}
		return $body;		
	}



	function home(){
		

		$body="";

		if(!isset($this->input['step'])){
			$step=1;		
		} else {
			$step=$this->input['step'];
		}

		$sql="SELECT * FROM config WHERE config_name='SERIAL' ";
		$res=$this->settings['dbconn']->query($sql);
		$row=$res->fetch( PDO::FETCH_ASSOC );

		$body.='<div style="clear:both"></div>';
		
		if($row['config_value']==""){
			$error="";
			if($step==2){
				if($this->input['serial']==""){
					$error="Please enter a serial number";
				} else {
					$postfields = 'mac='.rawurlencode($this->getCurrentMacAddress()).
                '&serial='.rawurlencode($this->input['serial']);
				
					$result=$this->getOMCInfo('check_serial',$postfields);
					
					

					if($result=="error"){
						$error="The serial number is invalid";			
					} else {
						if(is_numeric($result)) {
							$result2=$this->getOMCInfo('set_mac',$postfields);
							$sql="UPDATE config SET config_value='".$result."' WHERE config_name='SERIAL' ";
							$res=$this->settings['dbconn']->query($sql);
							if(file_exists('sqlite/code.txt')){
								unlink('sqlite/code.txt');							
							}
							$fp=fopen('sqlite/code.txt',"a");
							fwrite($fp,$result);
							fclose($fp);
						}
					}
				}
				
				if($error!=""){
					$step=1;				
				}
			
			}	
			
			
			if($step==1){
				$body.='<h2>Please enter the serial of your device</h2>';
				$body.='<form action="'.$this->settings['page'].'" target="_top" method="post">';
				$body.='<input type="hidden" name="tx_onqmediamanager_pi1[section_name]" value="home" />';
				$body.='<input type="hidden" name="tx_onqmediamanager_pi1[step]" value="2" />';
				if($error!=""){
					$body.='<p>'.$error.'</p>';				
				}
				$body.='<input type="text" name="tx_onqmediamanager_pi1[serial]" value="'.$this->input['serial'].'" />';
				$body.='<input type="submit" name="tx_onqmediamanager_pi1[submit]" value="submit" />';
				$body.='</form>';
			}
			
			
			
			
			if($step==2){
				$body.='<h2>Please select your VPN company</h2>';
				$body.='<form action="'.$this->settings['page'].'" target="_top" method="post">';
				$body.='<input type="hidden" name="tx_onqmediamanager_pi1[section_name]" value="home" />';
				$body.='<input type="hidden" name="tx_onqmediamanager_pi1[step]" value="2" />';
				if($error!=""){
					$body.='<p>'.$error.'</p>';				
				}
				$body.='<input type="text" name="tx_onqmediamanager_pi1[serial]" value="'.$this->input['serial'].'" />';
				$body.='<input type="submit" name="tx_onqmediamanager_pi1[submit]" value="submit" />';
				$body.='</form>';
			}			
			
			
			
			
			
		} else {
			$body.='<h2>Change VPN Location</h2>';
			$body.=$this->change_vpn_location();
		}
		return $body;	
	
	}



	function getOMCInfo($script,$postfields) {
		$ch = curl_init('http://www.openmediacentre.com.au/fileadmin/phpfunc/'.$script.'.php');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec ($ch);
		curl_close ($ch);
		return $output;
	}






	function change_vpn_location(){
		$body="";
		if(isset($this->input['vpn_name']) && $this->input['vpn_name'] != "" && is_numeric($this->input['vpn_name']) && $this->input['vpn_name'] > 0){
			$sql="UPDATE config set config_value='".$this->input['vpn_name']."' WHERE config_name='SELECTED_VPN_UID'";
			$res=$this->settings['dbconn']->query($sql);
			exec("/var/www/bin/openmediamanager/create_vpn_files");
		}
		
		
		
		
		$this->read_applic_config();		
		$body.='
		<script language="JavaScript">
		<!--
			function change_section_vpn(form) {
			var myindex=form.vpn_name.selectedIndex
			location.href=(form.vpn_name.options[myindex].value);
			}

		//-->
		</script>
		';

		$body.='<form name="select_vpn" style="margin:0;padding:0">';
		$body.='<select name="vpn_name" onchange="change_section_vpn(this.form)" >';
		
		$body.='<option value="">Please select VPN location</option>';
		
		/*
		//disabled this at it was double nating, some things would work, others wouldn't
		
		$body.='<option value="'.$this->settings['page'];
		if(!strstr($this->settings['page'],"?")) $body.="?";
		$aux['vpn_name']=999999;
		$aux['section_name']=$this->input['section_name'];
		$body.='&'.$this->settings['prefixId'].'[all_saved]='.base64_encode(serialize($aux));
		$body.='" ';
	    if((isset($this->input['vpn_name']) && $this->input['vpn_name'] == 999999) || ($this->applic_config['SELECTED_VPN_UID'] == 999999)){
			$body.='selected="selected" '; 
		}
		$body.= '>No VPN</option>';
		*/

		$sql="SELECT vpn_location.uid,vpn_company.company_name,vpn_location.location 
		FROM (vpn_location) 
		LEFT JOIN vpn_company ON vpn_company.uid=vpn_location.vpn_company_uid 
		ORDER by vpn_company.company_name,vpn_location.location";
		
		$res=$this->settings['dbconn']->query($sql);
		
		while ($row=$res->fetch( PDO::FETCH_ASSOC )){ 
			$body.='<option value="'.$this->settings['page'];
			if(!strstr($this->settings['page'],"?")) $body.="?";
			$aux['vpn_name']=$row['uid'];
			$aux['section_name']=$this->input['section_name'];
			$body.='&'.$this->settings['prefixId'].'[all_saved]='.base64_encode(serialize($aux));
			$body.='" ';
	      if((isset($this->input['vpn_name']) && $this->input['vpn_name'] == $row['uid']) || ($this->applic_config['SELECTED_VPN_UID'] == $row['uid'])){
				$body.='selected="selected" '; 
			}
			$body.= '>'.stripslashes($row['company_name'])." ".stripslashes($row['location']).'</option>';			
		}		
		$body.='</select>';
		$body.='</form>'; 
		return $body;
	}


	function read_applic_config(){

		$sql="SELECT * FROM config";
		$res=$this->settings['dbconn']->query($sql);
		
		while ($row=$res->fetch( PDO::FETCH_ASSOC )){
			$this->applic_config[$row['config_name']]=stripslashes($row['config_value']);
		}
	
	}
	
	
	function create_vpn_files(){
		$sql="SELECT vpn_company.*,vpn_location.ip_address,vpn_location.port 
		FROM vpn_location 
		LEFT JOIN vpn_company ON vpn_company.uid=vpn_location.vpn_company_uid 
		WHERE vpn_location.uid=".$this->input['vpn_name'];
		$res=$this->settings['dbconn']->query($sql);
		$row=$res->fetch( PDO::FETCH_ASSOC );
		
		$directory="commands/openvpn_files";
		
		if (!file_exists($directory)) {
    		mkdir($directory, 055, true);
		}
		//delete all files in directory
		$files = glob($directory.'/*'); // get all file names
		foreach($files as $file){ // iterate files
  			if(is_file($file))
    		unlink($file); // delete file
		}
		
		$extra_params="";
		
		$extra_params.="remote ".$row['ip_address']." ".$row['port']."\n";


		//create password file
		$file_name=$directory."/auth_".$row['uid'].".txt";
		$fp=fopen($file_name,"a");
		fwrite($fp,stripslashes($row['username'])."\n".stripslashes($row['password']));
		fclose($fp);
		$extra_params.="auth-user-pass auth_".$row['uid'].".txt\n";		
		
		
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
		
		//finally the openvpn config file

		$file_name=$directory."/openvpn.conf";
		$fp=fopen($file_name,"a");
		fwrite($fp,stripslashes($row['openvpn_parameters'])."\n".$extra_params);
		fclose($fp);
	
	}

	function post_update_wifi_settings($id){
		foreach($this->in[$this->config['table']] as $name => $value){
			if($name!="uid" && $value != ""){
				$sql="UPDATE config SET config_value='".$value."' WHERE config_name='".$name."'";
				$res  = $this->settings['dbconn']->query($sql);			
			}
		}
		exec("/var/www/bin/openmediamanager/create_wifi_settings");
	}
	

	function post_update_dyndns_settings($id){
		foreach($this->in[$this->config['table']] as $name => $value){
			if($name!="uid"){
				$sql="UPDATE config SET config_value='".$value."' WHERE config_name='".$name."'";
				$res  = $this->settings['dbconn']->query($sql);			
			}
		}
		$this->set_dyndns();
	}


	
	
	function set_dyndns(){
		//https://username:password@www.dnsdynamic.org/api/?hostname=techno.ns360.info&myip=127.0.0.1
		//https://username:password@dynupdate.no-ip.com/nic/update?hostname=mytest.testdomain.com&myip=1.2.3.4		
	
		
		$command="/sbin/ifconfig tun1 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'";
		$localIP = exec ($command);
		if($localIP==""){
			return;		
		}
		$sql="SELECT * FROM dyndns_settings ";
		$res  = $this->settings['dbconn']->query($sql);
		$row=$res->fetch( PDO::FETCH_ASSOC );
		
		if($row['DYNDNS_ENABLED']==0){
			return;		
		}
		
		$params=array();
		$params['hostname']=stripslashes($row['DYNDNS_HOSTNAME']);
		$params['myip']=$localIP;

		$username=stripslashes($row['DYNDNS_USERNAME']);
		$password=stripslashes($row['DYNDNS_PASSWORD']);

		if($username=="" || $password==""){
			return;		
		}

		$url="";

		if($row['DYNDNS_COMPANY']=="dyndns"){
			$url="https://members.dyndns.org/nic/update";
		}
		
		if($row['DYNDNS_COMPANY']=="noip"){
			$url="https://dynupdate.no-ip.com/nic/update";
		}
		
		if($url==""){
			return;		
		}
		
		$url = $url.'?'.http_build_query($params, '', '&');
    
		print_r($url);
		
		$user_agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';
		$user_agent = "User-Agent: openmediavpn/0.0.1 edgar.pisani@gmail.com";
		
		include_once "include/mycurl.php";
		
		
		
		$curl = new mycurl($url);
		$curl->useAuth(true);
		//$curl->setCert(config::getMainIni('cert'));
		$curl->setName($username);
		$curl->setPass($password);
		$curl->setUserAgent($user_agent);
		$curl->createCurl();
		$result = $curl->getWebPage();
		$status = $curl->getHttpStatus();
		
	
		$codes=explode(" ",$result);
		
		
		if($codes[0]=="good" || $codes[0]=="nochg"){
			$row['DYNDNS_RETRIES']=0;
		} else {
			//after 3 retries the vpn will be disabled
			$row['DYNDNS_RETRIES']++;
			if($row['DYNDNS_RETRIES']>=3){
				$row['DYNDNS_ENABLED']=0;
			}
		}
		
		$sql="UPDATE dyndns_settings SET DYNDNS_RETRIES=".$row['DYNDNS_RETRIES'].",DYNDNS_ENABLED=".$row['DYNDNS_ENABLED'].",DYNDNS_LASTRESPONSE='".$codes[0]."'";
		print_r($sql);
		$res  = $this->settings['dbconn']->query($sql);
	}
	
	
	function set_forward_rules(){
		//iptables -t nat -A PREROUTING -p tcp -i ppp0 --dport 8001 -j DNAT --to-destination 192.168.1.200:8080
		//iptables -A FORWARD -p tcp -d 192.168.1.200 --dport 8080 -m state --state NEW,ESTABLISHED,RELATED -j ACCEPT	
	}
	
	
	
	function info(){
		$body="";
		$body.='<div style="clear:both"></div>';
		$body.='<h2>System Information</h2><p>';
		$body.="<b>Mac Address:</b> ".$this->getCurrentMacAddress()."<br />";
		$body.='</p>';
		return $body;	
	
	}	



    function getCurrentMacAddress(){
    
        $mac_address="";
        $ifconfig = shell_exec("/sbin/ifconfig eth0");
        preg_match("/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i", $ifconfig, $ifconfig);
        if (isset($ifconfig[0])) {
            $mac_address=trim(strtoupper($ifconfig[0]));
        }
        return $mac_address;
    }
    
    
	function check_openvpn_company($ed,$error){

		if((($this->in['vpn_company']['username'] != '' || $this->in['vpn_company']['password'] != '') && $this->in['vpn_company']['tls_auth'] != '') 
		|| ($this->in['vpn_company']['username'] == '' && $this->in['vpn_company']['password'] == '' && $this->in['vpn_company']['tls_auth'] == ''))
		{
				$error['username']="Please enter User Name and Password or TLS Auth";
		} 


		if($this->in['vpn_company']['tls_auth'] == ''){
			if($this->in['vpn_company']['username'] == '' || $this->in['vpn_company']['password'] == ''){
				$error['username']="User Name and Password are required";
			}    	
		}
		
		if((stristr($this->in['vpn_company']['openvpn_parameters'],'<ca>') || stristr($this->in['vpn_company']['openvpn_parameters'],'</ca>')) && $this->in['vpn_company']['ca_cert']){
			$error['ca_cert']="You can't have a <ca> or </ca> in your OpenVPN Parameters if you enter something the the CA Certificate field";
			$error['openvpn_parameters']=$error['ca_cert'];
		}


		if((stristr($this->in['vpn_company']['openvpn_parameters'],'<key>') || stristr($this->in['vpn_company']['openvpn_parameters'],'</key>')) && $this->in['vpn_company']['cert_key']){
			$error['cert_key']="You can't have a <key> or </key> in your OpenVPN Parameters if you enter something the the Key field";
			$error['openvpn_parameters']=$error['cert_key'];
		}



		if((stristr($this->in['vpn_company']['openvpn_parameters'],'<cert>') || stristr($this->in['vpn_company']['openvpn_parameters'],'</cert>')) && $this->in['vpn_company']['cert']){
			$error['cert']="You can't have a <cert> or </cert> in your OpenVPN Parameters if you enter something the the Certificate field";
			$error['openvpn_parameters']=$error['cert'];
		}


		if((stristr($this->in['vpn_company']['openvpn_parameters'],'<tls-auth>') || stristr($this->in['vpn_company']['openvpn_parameters'],'</tls-auth>'))){
			$error['openvpn_parameters']="You can't have a <tls-auth> or </tls-auth> in your OpenVPN Parameters. Please enter the TLS Auth in the corresponding field.";
		}


		
		$this->error=$error;
		
	}
}
?>