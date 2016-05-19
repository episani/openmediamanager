<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2016 Edgar Pisani (episani@onqweb.com.au)
*  All rights reserved
*
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Plugin 'onqmediamanager' 
 *
 * @author	Edgar Pisani <edgar@onqweb.com.au>
 */

session_start();
require_once "include/class.onqbasic_editor.php";
require_once "include/class.openmediamanager.php";

 
 //create or open database
$dbconn = new PDO("sqlite:sqlite/openmediamanager.sqlite");
function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 
$dbconn->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);

include_once "include/mainconfig.php"; 


$all_vars=array();
$all_vars[]="user";
$all_vars[]="pass";
$all_vars[]="logintype";
$all_vars[]="filter";
$all_vars[]="uid";
$all_vars[]="attachment_uid";
$all_vars[]="section_name";
$all_vars[]="vpn_name";
$all_vars[]="sub_section_name";
$all_vars[]="saved_var";
$all_vars[]="code";
$all_vars[]="cat";
$all_vars[]="find";
$all_vars[]="action";
$all_vars[]="file_type";		
$all_vars[]="submit_attach";
$all_vars[]="date_report_from";
$all_vars[]="date_report_to";
$all_vars[]="delete_from_report";
$all_vars[]="report_uid";
$all_vars[]="person_to";
$all_vars[]="email_to";
$all_vars[]="submit";
$all_vars[]="type_of_report";
$all_vars[]="report_submitted";
$all_vars[]="reset_filters";
$all_vars[]="wizard_step";
$all_vars[]="step";
$all_vars[]="serial";


$input=array();


if(isset($_REQUEST[$config['config']['prefixId']]['all_saved'])){
	$input=unserialize(base64_decode($_REQUEST[$config['config']['prefixId']]['all_saved']));
}
		

for($i=0;$i<count($all_vars);$i++){
	if(isset($_REQUEST[$config['config']['prefixId']][$all_vars[$i]])){
		$input[$all_vars[$i]]=$_REQUEST[$config['config']['prefixId']][$all_vars[$i]];
	}
}


if(!isset($input['section_name']) || $input['section_name']==""){
	$input['section_name']="home";
}

if(!isset($input['action'])){
	$input['action']="";
}



$in=array();
		
if(isset($config[$input['section_name']])) {
	if(isset($config[$input['section_name']]['var_edit'])) {	
		for($i=0;$i<count($config[$input['section_name']]['var_edit']);$i++){
			$parts=explode(",",$config[$input['section_name']]['var_edit'][$i]);
			if($parts[3]!="DATEPICKER" &&  $parts[3]!="DATEPICKERHOUR" &&  $parts[3]!="BREAKER" && $parts[3]!="LINEBREAKER" && $parts[3]!="FREEHTML" && $parts[3]!="INFO" &&  $parts[3]!="BLANK" &&  $parts[3]!="TEXTVIEW" &&  $parts[3]!="DATEVIEW"){
				if(isset($_REQUEST[$config['config']['prefixId']][$parts[2]])){

					$in[$parts[0]][$parts[2]]=$_REQUEST[$config['config']['prefixId']][$parts[2]];
				}
				
				if(isset($_REQUEST[$config['config']['prefixId']]['new_'.$parts[2]])){
					$in[$parts[0]]['new_'.$parts[2]]=$_REQUEST[$config['config']['prefixId']]['new_'.$parts[2]];
				}		    
			}
			
			
			
			if($parts[3]=="DATEPICKER") {
				if(isset($_REQUEST[$config['config']['prefixId']][$parts[2]]) && strlen($_REQUEST[$config['config']['prefixId']][$parts[2]]) == 10) {
					$in[$parts[0]][$parts[2]]=mktime(0,0,0,substr($_REQUEST[$config['config']['prefixId']][$parts[2]],3,2),substr($_REQUEST[$config['config']['prefixId']][$parts[2]],0,2),substr($_REQUEST[$config['config']['prefixId']][$parts[2]],6,4));
				} else {
					if(isset($_REQUEST[$config['config']['prefixId']][$parts[2]])){
						$in[$parts[0]][$parts[2]]=0;
					}
				}
			}

			if($parts[3]=="DATEPICKERHOUR") {
				if(isset($_REQUEST[$config['config']['prefixId']][$parts[2]]) && strlen($_REQUEST[$config['config']['prefixId']][$parts[2]]) == 18) {
					$hour=substr($_REQUEST[$config['config']['prefixId']][$parts[2]],11,2);
					$ampm=strtolower(substr($_REQUEST[$config['config']['prefixId']][$parts[2]],16,2));
					if($ampm=="pm") {
						$hour+=12;
						if($hour==24) {
							$hour=0;	
						}
					}
					$in[$parts[0]][$parts[2]]=mktime($hour,substr($_REQUEST[$config['config']['prefixId']][$parts[2]],14,2),0,substr($_REQUEST[$config['config']['prefixId']][$parts[2]],3,2),substr($_REQUEST[$config['config']['prefixId']][$parts[2]],0,2),substr($_REQUEST[$config['config']['prefixId']][$parts[2]],6,4));
				} else {
					if(isset($_REQUEST[$config['config']['prefixId']][$parts[2]])){
						$in[$parts[0]][$parts[2]]=0;
					}
				}
			}
		}
	}
}

	
$content="";
$applic=new openmediamanager($config,$input,$in);
$body=$applic->body;

if(!isset($_SESSION[$config['config']['prefixId'].'_login'])){
	$sessionData=array();
} else {
	$sessionData=$_SESSION[$config['config']['prefixId'].'_login'];
}


$applic->settings['sessiondata']=$sessionData;


if((!isset($sessionData['loggedin']) || $sessionData['loggedin']==0)){
	$applic->input["logintype"]="login";
	$content.=$applic->login_box();
} else {
	if(!stristr($input['section_name'],"ajax")){
		if(file_exists('js/custom.js')){
			$content='<script src="js/custom.js?ver=8"></script>';
		}
		
		
		$sql="SELECT * FROM config WHERE config_name='SELECTED_VPN_UID' ";
		$res=$dbconn->query($sql);
		$row=$res->fetch( PDO::FETCH_ASSOC );

		if($row['config_value']!=0){
			$sql="SELECT * FROM vpn_company WHERE uid=(SELECT vpn_company_uid FROM vpn_location WHERE uid=".$row['config_value'].")";
			$res=$dbconn->query($sql);
			$row=$res->fetch( PDO::FETCH_ASSOC );
			
			//if($row['username']!="" && $row['password']!=""){
				$content.=$applic->navigation_holder();
			//}

		}		
		
		if($input['section_name']=="login"){
			$content.=$applic->home();		
		}
		
		
		
		$content.=$body;
	} else {	
		
	}
}
	
$template=$applic->fileResource("templates/website_template.html");


$markerArray=array();
$markerArray["###WEBSITE_CONTENT###"]=$content;
$template=$applic->substituteMarkerArray($template, $markerArray);

echo $template;
		
//$dbconn->close();

	


?>