<?php
class onqbasic_editor {

	var $config_all=array();
	var $config=array();
	var $settings=array();
	var $input=array();
	var $in=array();
	var $piVars=array();
	var $current_record=array();
	var $body="";
	var $download_link="";
	var $error=array();

	function onqbasic_editor($config,$input,$in,$piVars,$cObj) {
	
			$this->config_all=$config;
	  		$this->config=$config[$input['section_name']];
			$this->settings=$config['config'];
			$this->input=$input;
			$this->in=$in;
			$this->piVars=$piVars;
			$this->cObj=$cObj;
			return;
			
	}


	function main(){
	
		switch ($this->input['action']){
			case "del":
				$this->delete_record();
				break;
			case "del2":
				$this->delete_record2();
				break;
			case "new":
				$this->edit_record('2');				
				break;
			case "edit":
				$this->edit_record('1');				
				break;
			case "copy":
				$this->copy_record();				
				break;
			case "create":
				$this->create_record();				
				break;									
			case "create_new":
				$this->save_new();
				break;
			case "update":
				$this->update();
				break;
			case "print_all":
				$this->print_all();
				break;
			case "attachments":
			case "save_attachment":
			case "delete_attachment":
				$this->attachments();
				break;
			default:
				if(!isset($this->config['var_display'])){
					$this->edit_record('2');
				} else {			
					$this->gen_display();
				}
		}
		return $this->body;
	}


	function gen_display(){

		$body="";
		$csv=array();
		if(isset($this->input['page'])){
			$page=$this->input['page'];		
		} else {
			$page=0;
		}
		
		$ini=$page*$this->config['display'];
		$end=$ini+$this->config['display'];
		$order_by="";
		
		if(!isset($this->input['order_field'])) {
			if(isset($this->config['order_by'])) {
				for($i=0;$i<count($this->config['order_by']);$i++){
					$order_by.=$this->config['order_by'][$i];
					if($i<(count($this->config['order_by'])-1)) $order_by.=",";
				}
			}	
		} else {
			$order_by.=$this->input['order_field']." ".$this->input['display_order'];
		}
		$where="";
		
		
		$value='';		
		$gen_query=" FROM (".$this->config['table'].")  ";


		$num=0;	
		if(isset($this->config['table_join']) && is_array($this->config['table_join'])) {
			foreach($this->config['table_join'] as $value) {
				if(strlen($value) > 0 && strlen ($this->config['table_join_relation'][$num]) > 0) {
					$gen_query.="LEFT JOIN ".$value." ON ".$this->config['table_join_relation'][$num]." ";	  
				}
				$num++;
			}
		}
	
	
		if(isset($this->input['find']) && strlen($this->input['find']) > 0){
	  		if(strlen($where) <=0) {
				$where.=" WHERE ";
			} else {
	  			$where.=" AND ";
			}
	  

			$where.=" (";
			
							
			for($j=0;$j<count($this->config['var_search']);$j++){
				//$this->config['var_search'][$j]=str_replace(" DESC","",$this->config['var_search'][$j]);
				$where.=$this->config['var_search'][$j]." LIKE '%".addslashes($this->input['find'])."%' ";
				if($j<(count($this->config['var_search'])-1)) $where.="OR ";
			}
			$where.=") ";
		}
	
		

	
		if(isset($this->config['table_relation_where']) && is_array($this->config['table_relation_where'])) {
			foreach($this->config['table_relation_where'] as $value) {
				if(is_array($value)) {
					foreach($value as $k =>$v) {
						if(strlen($where) <=0) {
	 		 				$where.=" WHERE ";
						} else {
			  				$where.=" AND ";
						}
						$where.=$k."='".$v."' ";
					}
				}
			}	  
		}
	
	
		if(isset($this->config['table_relation_where_free_expression']) && is_array($this->config['table_relation_where_free_expression'])) {
			foreach($this->config['table_relation_where_free_expression'] as $value) {
				if(strlen($where) <=0) {
 					$where.=" WHERE ";
				} else {
  					$where.=" AND ";
				}
				$where.=$value;					
			}
		}	
	

		if(isset($this->config['filters']) && is_array($this->config['filters'])){
			foreach($this->config['filters'] as $filter){
				$filter_parts=explode("|",$filter);
				$method=$filter_parts[0].'_filter_gen_display';
				if(method_exists($this,$method)) {
					$where.=$this->$method($filter_parts[0],$where,$this->config['table']);
				}
			}
		}



		$query="SELECT COUNT(*) as total ";
		$query .= $gen_query;
		$query .= $where;
		
		$res=$this->settings['dbconn']->query($query);
		

		$row=$res->fetch( PDO::FETCH_ASSOC );
		$total=$row["total"];


		$query="SELECT ";

		$select_fields=array();
		$select_fields[]=$this->config['table'].".uid";
		foreach($this->config['var_display'] as $vd) {
			$vd_array=explode(",",$vd);
			if($vd_array[1] !="") {
				$select_fields[]=$vd_array[1].".".$vd_array[2];
			}
		}		
		$query.=implode(",",$select_fields);		


		$value='';
		if(isset($this->config['table_join_fields']) && is_array($this->config['table_join_fields'])) {
			foreach($this->config['table_join_fields'] as $value) {
				if(strlen($value) > 0) {
					$query.=",".$value." ";
				}
			}
		}
						
		$query.=$gen_query." ";
		

						
		$query.=$where;

		if(strlen($order_by) > 0) {
			$query.=" ORDER by ".$order_by;
		}

		//need the csv query without paging
		$csv['query']=base64_encode(serialize($query));


		$query.=" LIMIT ".$ini.",".$this->config['display'];
		

		$res=$this->settings['dbconn']->query($query);
		//print_r($query);


		if($res) {
			$numofprods=$total;
		} else {
			$numofprods=0;
		}
		
		
		if($numofprods > 0) {
			//$csv['query']=base64_encode(serialize($query));
			$csv['config']=base64_encode(serialize($this->config));
			$csv['section_name']=$this->input['section_name'];
		}		
		
		if($numofprods ==1 ) {
			$res=$this->settings['dbconn']->query($query);
			$row=$res->fetch( PDO::FETCH_ASSOC );
			$this->input['action']="edit";
			$this->input['uid']=$row['uid'];
			$this->edit_record('1');
			return;
		}
		

		if($numofprods > 0){
			$res=$this->settings['dbconn']->query($query);
			$body.='<div class="onqmediamanager_list_div"><table border="0" cellpadding="0" cellspacing="0" class="onqmediamanager_list_table" >';
		

			//beginning of the table header
			$body.='<tr>';
			for($i=0;$i<count($this->config['var_display']);$i++){
				$pieces=explode(",",$this->config['var_display'][$i]);
				$body.='<td class="onqmediamanager_list_table_header" ';
				if(strlen($pieces[4]) > 0) $body.='style="text-align:'.$pieces[4].'"';
				$body.=' >';
			
			
			
			//if you click on one of the fields the order of how information is displayed will be changed

				$body.='<a href="'.$this->settings['page'].'?';
				if($pieces[2] != "allocated_staff_sql") {
					$field=$pieces[1].".".$pieces[2];
				} else {
					$field="allocated_staff_sql";				
				}
				
	

				if(isset($this->input['order_field']) && $this->input['order_field'] != $field){
						$display_order="ASC";
				} else {
					if(!isset($this->input['display_order']) || strlen($this->input['display_order']) <=0){
						$display_order="ASC";
					} else {
						if($this->input['display_order']=="ASC"){
							$display_order="DESC";
						}else{
							$display_order="ASC";
						}
					}
				}


				$input=array();
				$input=$this->input;
				$input['display_order']=$display_order;
				$input['order_field']=$field;
				unset($input['page']);
				$body.=$this->settings['ext_name'].'[all_saved]='.base64_encode(serialize($input));
				$body.='">';

				$body.=$pieces[0];
				$body.='</a>';
				$body.='</td>';
			}

				
			if($this->config['allow_delete']== 1) {
				$body.='<td class="onqmediamanager_list_table_header">&nbsp;</td>';
			}				


			if(isset($this->config['allow_copy']) && $this->config['allow_copy']== 1 && isset($this->config['var_copy']) && count($this->config['var_copy']) > 0) {
				$body.='<td class="onqmediamanager_list_table_header">&nbsp;</td>';
			}
			
			
			if(isset($this->config['allow_create']) && $this->config['allow_create']== 1 && isset($this->config['var_create']) && count($this->config['var_create']) > 0) {
				$body.='<td class="onqmediamanager_list_table_header">&nbsp;</td>';
			}			

			if($this->config['allow_edit']== 1) {
				$body.='<td class="onqmediamanager_list_table_header">';
				$body.='
				<form name="get_csv" action="download_csv.php" method="post" target="_blank">
				<input type="hidden" name="csv" value="'.base64_encode(serialize($csv)).'" />
				<input type="submit" value="get csv" style="padding:2px"/>
				</form>';
				$body.='</td>';
			}



			$body.='</tr>';
			// end of the header

			// begin of the database result

			$j=0;
			while ($row=$res->fetch( PDO::FETCH_ASSOC )){

				$par=1;
				if((doubleval($j/2)-intval($j/2))==0) {
					$par=0;
				}

				$body.='<tr class="';
				if($par){
					$body.="onqmediamanager_list_table_dr";
				} else {
					$body.="onqmediamanager_list_table_lr";
				}
				$body.='">';
				for($i=0;$i<count($this->config['var_display']);$i++){
					$pieces=explode(",",$this->config['var_display'][$i]);
					$body.='<td class="onqmediamanager_list_table_td" ';
					if(strlen($pieces[4]) > 0) {
						$body.='style="text-align:'.$pieces[4].'';
					}
					$body.='" >';

					if(strlen($pieces[5]) > 0){
						if(strlen($row[$pieces[2]]) > $pieces[5]) $row[$pieces[2]]=substr($row[$pieces[2]],0,$pieces[5])."...";
					}
					
					if($pieces[3]=='datepicker'){
						if(is_numeric($row[$pieces[2]]) && $row[$pieces[2]] > 0) {
							//$datetime=date("d&#8209;m&#8209;Y",$row[$pieces[2]]);
							$datetime=date("d/m/Y",$row[$pieces[2]]);
						} else {
							$datetime="";
						}
						$row[$pieces[2]]=$datetime;					
					}


					if($pieces[3]=='datepickerhour'){
						if(is_numeric($row[$pieces[2]]) && $row[$pieces[2]] > 0) {
							//$datetime=date("d&#8209;m&#8209;Y&#160;h:ia",$row[$pieces[2]]);
							$datetime=date("d/m/Y&#160;h:iA",$row[$pieces[2]]);
						} else {
							$datetime="";
						}
						$row[$pieces[2]]=$datetime;
					}
					
					if($pieces[3]=='currency'){
						$row[$pieces[2]]='$'.$this->currency($row[$pieces[2]]);
					}

					if($pieces[4]=="email") {
						$row[$pieces[2]]='<a href="mailto:'.stripslashes($row[$pieces[2]]).'">email</a>';
					}


					$body.=stripslashes($row[$pieces[2]]);

					$body.='</td>';
			}

			if($this->config['allow_delete']== 1) {
				$body.='<td style="text-align:center;width:40px">';
				$body.='<form name="delete" action="'.$this->settings['page'].'" method="post">';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="del" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[uid]" value="'.$row['uid'].'" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($this->input)).'">'."\n";				
				$body.='<input type="submit" value="'.$this->settings['delete_button_caption'].'" class="'.$this->settings['delete_button_class'].'" />';
				$body.='</form>';
				$body.='</td>';
			}


			if(isset($this->config['allow_copy']) && $this->config['allow_copy']== 1 && isset($this->config['var_copy']) && count($this->config['var_copy']) > 0) {
				$body.='<td style="text-align:center;width:40px">';
				$add_message='';
				$method='copy_condition_'.$this->input['section_name'];
				if(method_exists($this,$method)) {
					$add_message=$this->$method('copy');
				} 
				
				$body.='<form name="copy" action="'.$this->settings['page'].'" method="post" ';
				if($add_message!='') {
					$body.=' onsubmit="alert(\''.addslashes($add_message).'\'); return false;" ';
				}
				$body.='>';				
				
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="copy" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[uid]" value="'.$row['uid'].'" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($this->input)).'">'."\n";
				$method='copy_condition_'.$this->input['section_name'];
				$body.='<input type="submit" value="Copy" class="'.$this->settings['edit_button_class'].'" />';
				$body.='</form>';
				$body.='</td>';
			}


			if(isset($this->config['allow_create']) && $this->config['allow_create']== 1 && isset($this->config['var_create']) && count($this->config['var_create']) > 0) {
				$body.='<td style="text-align:center;width:40px">';
				$add_message='';
				$method='create_condition_'.$this->input['section_name'];
				if(method_exists($this,$method)) {
					$add_message=$this->$method('creat');
				} 
				
				$body.='<form name="create" action="'.$this->settings['page'].'" method="post" ';
				if($add_message!='') {
					$body.=' onsubmit="alert(\''.addslashes($add_message).'\'); return false;" ';
				}
				$body.='>';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="create" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[uid]" value="'.$row['uid'].'" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($this->input)).'">'."\n";				
				$body.='<input type="submit" value="'.$this->config['var_create_name'].'" class="'.$this->settings['edit_button_class'].'" />';
				$body.='</form>';
				$body.='</td>';
			}


			if($this->config['allow_edit']== 1) {
				$body.='<td style="text-align:center;width:40px">';
				$body.='<form name="edit_record" action="'.$this->settings['page'].'" method="post">';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="edit" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[uid]" value="'.$row['uid'].'" />';
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($this->input)).'">'."\n";
				if($this->config['allow_update']== 1) {				
					$body.='<input type="submit" value="'.$this->settings['edit_button_caption'].'" class="'.$this->settings['edit_button_class'].'" />';
				} else {
					$body.='<input type="submit" value="'.$this->settings['view_button_caption'].'" class="'.$this->settings['edit_button_class'].'" />';
				}
				$body.='</form>';
				$body.='</td>';
			}			

			
			$body.='</tr>';
				$j++;
			}
			$body.='</table></div>';

			// now, the next and previous buttons

			$body.='<br />';

			$body.='<div  align="center"><table class="onqmediamanager_list_table">';
			$body.='<tr>';
      	$body.='<td class="onqmediamanager_list_table_td"  style="width:100px;text-align:left">';
			if($page > 0){
				$body.='<form name="previous" action="'.$this->settings['page'].'" method="post">'."\n";
				$input=array();
				$input=$this->input;
				$input['page']=$this->input['page']-1;

				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'">'."\n";
				$body.='<input type="submit" value="'.$this->settings['previous_button_caption'].'" class="'.$this->settings['previous_button_class'].'">'."\n";
				$body.='</form>';
			} else {
				$body.='&nbsp;';
			}

	
			$body.='</td>';

			$body.='<td class="onqmediamanager_list_table_td"  style="width:100%;text-align:center">';
			$body.=sprintf($this->settings['displaying_record_caption'],($ini+1),($end > $total)?$total:$end,$total);
			$body.='</td>';
		
			$body.='<td class="onqmediamanager_list_table_td"  style="text-align:right;width:100px">';
			
			if($total > ($page+1)*$this->config['display']){
				$body.='<form name="next" action="'.$this->settings['page'].'" method="post">';
				$input=array();
				$input=$this->input;
				$input['page']=$page+1;
		
				$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'">';
				$body.='<input type="submit" value="'.$this->settings['next_button_caption'].'" class="'.$this->settings['next_button_class'].'">';
				$body.='</form>';
			} else{
				$body.='&nbsp;';
			}
			$body.='</td>';

			$body.='</tr>';
			$body.='</table></div>';

		
		} else { // numofprods
			$body.='<div  class="onqmediamanager_list_div"><strong>No records found</strong></div>';
		} // endif numofprods

		$this->body.=$body;
	}
	

	function copy_record(){
			$query="SELECT ";

			$select_fields=array();
			foreach($this->config['var_copy'] as $ve) {
				$ve_array=explode(",",$ve);
				if($ve_array[0] != ''){
					$select_fields[]=$ve_array[0].".".$ve_array[1];
				}
			}		
			$query.=implode(",",$select_fields);		
			
			$query .= " FROM ".$this->config['table']." ";
			$query.=" WHERE ".$this->config['table'].".uid='".$this->input['uid']."' ";	
	

			$res  = $this->settings['dbconn']->query($query);
			//print_r($query);
			$row=$res->fetch( PDO::FETCH_ASSOC );


			foreach($this->config['var_copy'] as $ve) {
				$ve_array=explode(",",$ve);
				if($ve_array[0] != ''){
					$this->in[$this->config['table']][$ve_array[1]]=$row[$ve_array[1]];
				}
			}		


			$method="before_copy_".$this->input['section_name'];
			if(method_exists($this,$method)) {
				$this->$method($this->input['uid']);
			}



			$this->input['action']="new";			
			$this->edit_record('2');

	}



	function create_record(){

			$method="before_create_".$this->input['section_name'];
			if(method_exists($this,$method)) {
				$this->$method($this->input['uid']);
			}

			$method="create_record_".$this->input['section_name'];
			if(method_exists($this,$method)) {
				$this->$method($this->input['uid']);
			}

	}




	function edit_record($ed){

		$body="";
		
		//$body.='<script src="typo3conf/ext/onqmediamanager/js/datetimepicker_css.js?ver=8"></script>';
		
		if((!isset($this->input['section1_id_frame']) || $this->input['section1_id_frame']=='') && (!isset($this->input['section2_id_frame']) || $this->input['section2_id_frame']=='') && isset($this->config['var_display'])){
			$body.=$this->navigation_menu();
		}	
		$body.="\n".'<div id="main_editor" class="onqmediamanager_edit_div">';

		
		if($ed==1){	
			$query="SELECT ";

			$select_fields=array();
			$select_fields[]=$this->config['table'].".uid";
			foreach($this->config['var_edit'] as $ve) {
				$ve_array=explode(",",$ve);
				if($ve_array[3] != 'BREAKER' && $ve_array[3] != 'LINEBREAKER' && $ve_array[3] != 'FREEHTML' && $ve_array[3] != 'BLANK' && $ve_array[3] != 'INFO' && $ve_array[3] != 'SUBTITLE') {
					if($ve_array[0] != ''){
						$select_fields[]=$ve_array[0].".".$ve_array[2];
					}
				}	
			}		
			$query.=implode(",",$select_fields);		

			$value='';
			if(isset($this->config['table_join_fields']) && is_array($this->config['table_join_fields'])) {
				foreach($this->config['table_join_fields'] as $value) {
					if(strlen($value) > 0) {
						$query.=",".$value." ";
					}
				}
			}
			
			$query .= " FROM ".$this->config['table']." ";

			$num=0;	
			if(isset($this->config['table_join']) && is_array($this->config['table_join'])) {
				foreach($this->config['table_join'] as $value) {
					if(strlen($value) > 0 && strlen ($this->config['table_join_relation'][$num]) > 0) {
						$query.="LEFT JOIN ".$value." ON ".$this->config['table_join_relation'][$num]." ";	  
					}
					$num++;
				}
			}


			
			$query.=" WHERE ".$this->config['table'].".uid='".$this->input['uid']."' ";
			
			if(isset($this->config['table_relation_where']) && is_array($this->config['table_relation_where'])) {
				foreach($this->config['table_relation_where'] as $value) {
					if(is_array($value)) {
						foreach($value as $k =>$v) {
							if(strlen($where) <=0) {
	 		 					$where.=" WHERE ";
							} else {
			  					$where.=" AND ";
							}
							$where.=$k."='".$v."' ";
						}
					}
				}	  
			}			


			if(isset($this->config['table_relation_where_free_expression']) && is_array($this->config['table_relation_where_free_expression'])) {
				foreach($this->config['table_relation_where_free_expression'] as $value) {
					if(strlen($where) <=0) {
	 					$where.=" WHERE ";
					} else {
	  					$where.=" AND ";
					}
					$where.=$value;					
				}
			}


			$res  = $this->settings['dbconn']->query($query);

			$row=$res->fetch( PDO::FETCH_ASSOC );
			$this->current_record=$row;
		} else {
			foreach($this->config['var_edit'] as $ve) {
				$ve_array=explode(",",$ve);
				if(isset($this->in[$ve_array[0]][$ve_array[2]])){
					$row[$ve_array[2]]=$this->in[$ve_array[0]][$ve_array[2]];
				} else {
					$row[$ve_array[2]]="";
				}
			}		
			$this->current_record=array();
		}



		$j=0;

		$body.=$this->begin_edit($ed);
		
		
		$method='edit_record_'.$this->input['section_name'];
		if(method_exists($this,$method)) {
			$body.=$this->$method($ed,$row);
		} else {

			for($i=0;$i<count($this->config['var_edit']);$i++){
				$pieces=explode(",",$this->config['var_edit'][$i]);
				$editor=array();
				$editor['table']=$pieces[0];
				$editor['label']=$pieces[1];
				$editor['var_name']=$pieces[2];
				$editor['var_type']=$pieces[3];
				$editor['function']=$pieces[4];
				$editor['template']=$pieces[5];
				$editor['var_length']=$pieces[6];
				$editor['max_length']=$pieces[7];

				$body.=$this->tr_display($row,$j,$ed,$editor);
				$j++;
			}
		}

	$body.=$this->end_edit($ed,$row);
	$body.='</div>';

	$body.='<div id="onqmediamanager_adjust"></div>';	
	$this->body.=$body;
}



	function begin_edit($ed){

		$body="";
		
		$body.='<div style="clear:both;height:1px;">&nbsp;</div>';
		



		if(count($this->error) > 0){
			$body.='<div class="onqmediamanager_edit_main_error">';
			$body.='<p>There were errors processing the form, please check</p>';
			$body.='</div>';
		}

		if(!isset($this->input['mobile_id_frame']) && !isset($this->input['plan_id_frame']) && ($this->config['security_mode']=="administrator" || $this->config['security_mode']=="parent_distributors" || $this->config['security_mode']=="distributors") ){
			$body .= '<div id="onqmediamanager_back_button">';
			$body .= '<form name="back_to_list" action="'.$this->settings['page'].'"  method="post" >';
			$input=array();
			$input=$this->input;
			unset($input['action']);
			$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'">';
			$body.='<div style="float:left"><input type="submit" value="<< back" class="button" /></div>';	
			$body.='</form>';
			$body.='</div>';
		}


		
		if($ed!=2) {
			$body.=$this->editor_tabs_menu();
		}

		$body .= '<form name="edit_record" action="'.$this->settings['page'].'"  method="post" >';

		$input=array();
		$input=$this->input;
		

		$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'">';
		
		if($ed==1){
			$body.="\n".'<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="update">';
		} else {
			$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="create_new">';		
		}



	
		$body.="\n".'<div class="onqmediamanager_editorbox_container">
		<div id="'.$this->input['section_name'].'_section_div">
		';
		
		if(isset($this->config['legend'])){
			$body.=$this->config['legend'];	
		}
		
		
		if(!isset($this->config['custom_editor'])) {		
			$body.='<div class="onqmediamanager_editorbox">'."\n";
		}
		return $body;
	}

	function end_edit($ed,$row){
		$body="";

		if(!isset($this->config['custom_editor'])) {			
			$body.="</div>";
		}	
	
		$body.="\n".'<div style="clear:both;height:1px;">&nbsp;</div>';

		//buttons will be inside or outside depends on whether they are main editor (out)
		//or tab editor (in)			
		
		if(isset($this->input['mobile_id_frame']) || isset($this->input['plan_id_frame'])){ 		
			$body.='<div class="onqmediamanager_edit_buttons_in">';
			$body.='<div style="float:left"><input type="button" value="<< back" class="button" onclick="history.back(1);" /></div>';	
			if($this->config['allow_add']==1){
				$body.='<div style="float:right">';
				if($ed==1){
					$body.='<input type="submit" value="save changes >>" class="'.$this->settings['add_record_button_class'].'" />';
				} else {
					$body.='<input type="submit" value="add record >>" class="'.$this->settings['add_record_button_class'].'" />';
				}
				$body.='</div>'."\n";
			}
			$body.='</div>';
			$body.='</form>';
		} else {
			if($this->config['allow_update']==1){
				$body.='<div style="float:right">';
				if($ed==1){
					$update_js=array();
					$method='update_function_'.$this->input['section_name'];
					if(method_exists($this,$method)) {
						$update_js=$this->$method();
					} 

					if(count($update_js)==2) {
						$body.='<script>'.$update_js['code'].'</script>';
					
					}
				
					$body.='<input type="submit" value="save changes  >>" class="'.$this->settings['add_record_button_class'].'"';
					if(count($update_js)==2) {
						$body.=' onclick="return '.$update_js['function'].'" ';
					}					
					$body.=' />';
				} else {
					$body.='<input type="submit" value="add record >>" class="'.$this->settings['add_record_button_class'].'" />';
				}
				$body.='</div></form>'."\n";
			}
		
		
		}
		
		$body.='</div>'; //end div of main section
		//here I put all the other editors
		if($ed!=2) {
			$body.=$this->editor_tabs_content($ed,$row);		
		}
		$body.='</div>'; //end div of container
		
		//buttons for main editor
		
		
		return $body;	
	}



	function tr_display($row,$j,$ed,$editor){
		$display=array();
		$display['ed']=$ed;
		$display['label']=$editor['label'];
		
		if($editor['var_name'] != "") {
			$display['name']="tx_onqmediamanager_pi1[".$editor['var_name']."]";
			$display['id']="tx_onqmediamanager_pi1_".$editor['var_name']."_id";
			if(isset($this->in[$editor['table']][$editor['var_name']]) && $editor['function'] !="selector_distributors" && $editor['function'] !="selector_sections" && $editor['function'] !="selector_distributors_ls") {
				$display['value']=$this->in[$editor['table']][$editor['var_name']];
			} else {
				$display['value']=stripslashes($row[$editor['var_name']]);
			}
		

			if(isset($this->error[$editor['var_name']])) {
				$display['error']=$this->error[$editor['var_name']];
			}
		} else {
			$display['name']="";
			$display['id']="";
			$display['value']="";
			$display['error']="";
		}
		$display['var_length']=$editor['var_length'];
		$display['max_length']=$editor['max_length']; 
		
		if($editor['template']==''){
			$template = $this->getSubpart($this->fileResource("templates/onqmediamanager_template.html"),"###GENERIC_TEMPLATE_ROW###");
		} else {
			$template = $this->getSubpart($this->fileResource("templates/onqmediamanager_template.html"),"###".strtoupper($editor['template'])."###");
		}
		
		$function=$editor['function'];
		
		if($function==''){
			$function='text_input_new';		
		}
		
		
		

		
		$markerArray=$this->$function($display);
		
		$markerArray['###DIV_LABEL###']="tx_onqmediamanager_pi1_".$editor['var_name']."_div_label_id";
		$markerArray['###DIV_INPUT###']="tx_onqmediamanager_pi1_".$editor['var_name']."_div_input_id";
		
		if(isset($markerArray['###ERROR###']) && $markerArray['###ERROR###']!=''){
			$markerArray['###ERROR###']='<div class="onqmediamanager_error_message">'.$markerArray['###ERROR###'].'</div>';
		}
		
		$template=$this->substituteMarkerArray($template, $markerArray);
		return $template;
		
	
	}
	
	
	function breaker($display){
		$body='';
		$markerArray=array();
		$body.='</div>';
		$body.="\n".'<div class="onqmediamanager_editorbox" >'."\n";
		$markerArray["###BREAKER###"]=$body;
		return $markerArray;
	}


	function linebreaker($display){
		$body='';
		$markerArray=array();
		$body.='</div>';
		$body.='<div style="clear:both;padding-bottom:20px;"></div>';
		$body.=$display['label'];
		$body.="\n".'<div class="onqmediamanager_editorbox" >'."\n";
		$markerArray["###LINEBREAKER###"]=$body;
		return $markerArray;
	}





	function blank_Section($display){
		$markerArray=array();
		$markerArray['###LABEL###']="&nbsp;";
		$markerArray['###INPUT###']="&nbsp;";
		$markerArray['###ERROR###']="";
		return $markerArray;
	}
	
	
	function subtitle($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		return $markerArray;
	}
	
	
	function freehtml($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		return $markerArray;
	}		
		
	
	function text_input_new($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<input type="text" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$display['value'].'" ';
		if($display['var_length'] > 0) {
			$markerArray['###INPUT###'].='style="width:'.$display['var_length'].'px" ';
		}		
		$markerArray['###INPUT###'].='/>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}
	


	function text_hidden($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<input type="hidden" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$display['value'].'" ';
		$markerArray['###INPUT###'].='/>'.$display['value'];
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}
	
	
	function hidden_hidden($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<input type="hidden" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$display['value'].'" ';
		$markerArray['###INPUT###'].='/>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}	



	function text_input_currency($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='$ <input type="text" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$display['value'].'" ';
		if($display['var_length'] > 0) {
			$markerArray['###INPUT###'].='style="width:'.$display['var_length'].'px" ';
		}		
		$markerArray['###INPUT###'].='/>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}


	function text_input_percentage($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<input type="text" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$display['value'].'" ';
		if($display['var_length'] > 0) {
			$markerArray['###INPUT###'].='style="width:'.$display['var_length'].'px" ';
		}		
		$markerArray['###INPUT###'].='/> %';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}




	function textview($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']=$display['value'];
		$markerArray['###ERROR###']='';
		return $markerArray;
	}



	function textarea_input_new($display){

		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='
		<textarea  name="'.$display['name'].'" id="'.$display['id'].'"  ';
		$markerArray['###INPUT###'].='style="width:'.$display['var_length'].'px;height:'.$display['max_length'].'px" >';
		
		$value=$display['value'];

		$value  = str_replace(chr(34),"&quot;",$value);
		$value  = str_replace("<","&lt;",$value);
		$value  = str_replace(">","&gt;",$value);
		
		$markerArray['###INPUT###'].=$value.'</textarea>';
		
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}




	function selector_new($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<select name="'.$display['name'].'" id="'.$display['id'].'" ';
		
		if($display['max_length'] > 0) {
			$markerArray['###INPUT###'].='	style="min-width:'.$display['max_length'].'px" ';
		}
		$markerArray['###INPUT###'].='>';

		if(strlen($display['var_length']) > 0) {
			$markerArray['###INPUT###'].='<option value="">Please Select</option>';
			$selector=explode("=",$display['var_length']);
			$sql="SELECT * FROM ".$selector[0];
			if($selector[2]!=""){
				$sql.=" ORDER BY ".$selector[2];			
			}
			$res=$this->settings['dbconn']->query($sql);
			while($row=$res->fetch( PDO::FETCH_ASSOC )){
				$markerArray['###INPUT###'].='<option value="'.$row[$selector[1]].'" ';
				if($display['value']==$row[$selector[1]]){
					$markerArray['###INPUT###'].='selected="selected" ';
				}
				$markerArray['###INPUT###'].='>'.stripslashes($row[$selector[2]]).'</option>';
			}
		}		
		$markerArray['###INPUT###'].='</select>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}
	



	function selector_new_noselect($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<select name="'.$display['name'].'" id="'.$display['id'].'" ';
		
		if($display['max_length'] > 0) {
			$markerArray['###INPUT###'].='	style="min-width:'.$display['max_length'].'px" ';
		}
		$markerArray['###INPUT###'].='>';

		if(strlen($display['var_length']) > 0) {
			$selector=explode("=",$display['var_length']);
			$sql="SELECT * FROM ".$selector[0];
			$res=$this->settings['dbconn']->query($sql);;
			while($row=$res->fetch( PDO::FETCH_ASSOC )){
				$markerArray['###INPUT###'].='<option value="'.$row[$selector[1]].'" ';
				if($display['value']==$row[$selector[1]]){
					$markerArray['###INPUT###'].='selected="selected" ';
				}
				$markerArray['###INPUT###'].='>'.stripslashes($row[$selector[2]]).'</option>';
			}
		}		
		$markerArray['###INPUT###'].='</select>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}


	function selector_generic($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<select name="'.$display['name'].'" id="'.$display['id'].'" >';

		if(strlen($display['var_length']) > 0) {
			$markerArray['###INPUT###'].='<option value="">Please Select</option>';
			$selector_array=explode("|",$display['var_length']);
			foreach($selector_array as $selector){
				$parts=explode("=",$selector);
				$markerArray['###INPUT###'].='<option value="'.$parts[0].'" ';
				if($display['value']==$parts[0]){
					$markerArray['###INPUT###'].='selected="selected" ';
				}
				$markerArray['###INPUT###'].='>'.$parts[1].'</option>';
			}
		}		
		$markerArray['###INPUT###'].='</select>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}


	function selector_generic_noselect($display){
		$markerArray=array();
		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']='<select name="'.$display['name'].'" id="'.$display['id'].'" >';

		if(strlen($display['var_length']) > 0) {
			$selector_array=explode("|",$display['var_length']);
			foreach($selector_array as $selector){
				$parts=explode("=",$selector);
				$markerArray['###INPUT###'].='<option value="'.$parts[0].'" ';
				if($display['value']==$parts[0]){
					$markerArray['###INPUT###'].='selected="selected" ';
				}
				$markerArray['###INPUT###'].='>'.$parts[1].'</option>';
			}
		}		
		$markerArray['###INPUT###'].='</select>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}




	
	function datepicker($display){
		if($display['value'] != 0) {
			//$datetime=date('d-m-Y',$display['value']);
			$datetime=date('d/m/Y',$display['value']);
		} else {
			$datetime="";
		}

		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###'].='
			 <script>
				$(function() {
					$( "#'.$display['id'].'" ).datepicker({
						showButtonPanel: true,
						closeText: "Set",
						changeMonth: true,
						changeYear: true,
						dateFormat: "dd/mm/yy",
						yearRange: "-100:+10",
    					beforeShow: function(){    
           					$(".ui-datepicker").css(\'font-size\', 10) 
    					}						
						
						
					});
				});
				</script>		
		';
				
		
		
		$markerArray['###INPUT###'].='<input type="text" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$datetime.'" ';
		if($display['var_length'] > 0) {
			$markerArray['###INPUT###'].='style="width:'.$display['var_length'].'px" ';
		}		
		$markerArray['###INPUT###'].='/>';
		//$markerArray['###INPUT###'].='<img src="images2/cal.gif" onclick="javascript:NewCssCal (\''.$display['id'].'\',\'ddMMyyyy\',\'dropdown\')" style="cursor:pointer"/>';
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}


	function dateview($display){
		if($display['value'] != 0) {
			$datetime=date('d-m-Y',$display['value']);
		} else {
			$datetime="";
		}

		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###']=$datetime;
		$markerArray['###ERROR###']='';
	
		return $markerArray;
	}



	
	function datepickerhour($display){
		if($display['value'] != 0) {
			//$datetime=date('d-m-Y h:iA',$display['value']);
			$datetime=date('d/m/Y h:iA',$display['value']);
		} else {
			$datetime="";
		}

		$markerArray['###LABEL###']=$display['label'];
		$markerArray['###INPUT###'].='
			 <script>
				$(function() {
					$( "#'.$display['id'].'" ).datetimepicker({
						showButtonPanel: true,
						closeText: "Set",					
						changeMonth: true,
						changeYear: true,
						dateFormat: "dd/mm/yy",
						timeFormat: "hh:mmTT",
						yearRange: "-100:+10",
    					beforeShow: function(){    
           					$(".ui-datepicker").css(\'font-size\', 10) 
    					}						

					});
				});
				</script>		
		';		
		
		$markerArray['###INPUT###'].='<input type="text" name="'.$display['name'].'" id="'.$display['id'].'" value="'.$datetime.'" ';
		if($display['var_length'] > 0) {
			$markerArray['###INPUT###'].='style="width:'.$display['var_length'].'px" ';
		}		
		$markerArray['###INPUT###'].='/>';
		//$markerArray['###INPUT###'].='<img src="images2/cal.gif" onclick="javascript:NewCssCal (\''.$display['id'].'\',\'ddMMyyyy\',\'dropdown\',true,\'12\')" style="cursor:pointer"/>';
		
		
		if(isset($display['error'])) {
			$markerArray['###ERROR###']='<br />'.$display['error'];
		} else {
			$markerArray['###ERROR###']='';
		}
	
		return $markerArray;
	}







	function delete_record(){
		$query = "SELECT ".$this->config['table'].".* FROM ".$this->config['table']." where uid='".$this->input['uid']."'";
		$res  = $this->settings['dbconn']->query($query);
		$row=$res->fetch( PDO::FETCH_ASSOC );

		if($this->config['title']!=""){
			$body.="\n".'<h1>'.$this->config['title'].'</h1>';
		}
		
		$error=array();
		if(is_array($this->config['delete_rel'])){
			foreach($this->config['delete_rel'] as $delete_rel){
				$parts=explode("|",$delete_rel);
				$query = "SELECT * FROM ".$this->config_all[$parts[0]]['table']." WHERE ".$parts[1]."='".$row[$parts[2]]."'";
				$res  = $this->settings['dbconn']->query($query);
				if($res->fetchColumn() > 0) {
					//$error[]="There are records in the ".$this->config_all[$parts[0]]['title']." Table with the field ".$parts[1]."=".stripslashes($row[$parts[2]])." and can't be deleted";
					$error[]="There are records in the ".$this->config_all[$parts[0]]['title']." Table pointing to this record. It can't be deleted."; 
				}
			}
		}
		
		if(count($error) > 0){
			$body.='<ul>';
			foreach($error as $value){
				$body.='<li>'.$value.'</li>';			
			}
			$body.='</ul>';
			
			$body.='<form name="del_no" action="'.$this->settings['page'].'" method="post">';
			$input=array();
			$input=$this->input;
			unset($input['action']);
			unset($input['uid']);
			$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'" />';		
			$body.='<input type="submit" value="<< back"  />';	  
			$body.='</form>';
			
			
			
			
			$this->body=$body;
			return;					
		}
			
		$body.='<div><p><b>';
		$body.='Are you sure you want to delete this record?';
		$body.='</b></p>';

		$body.='<div style="text-align:left">';

		$body.='<ul>';
		for($i=0;$i<count($this->config['var_display']);$i++){
			$pieces=explode(",",$this->config['var_display'][$i]);
			if(strlen($row[$pieces[1]]) > 0) {
				$body.='<li><b>'.$pieces[0].': </b>'.$row[$pieces[1]].'</li>';
			}
		}
		$body.='</ul>';
		$body.='</div>';


		$body.='<table border="0" cellpadding="5" cellspacing="0"><tr>';
		$body.='<td>';
		$body.='<form name="del_yes" action="'.$this->settings['page'].'" method="post">';
		$input=array();
		$input=$this->input;
		$input['action']="del2";
		$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'" />';
		$body.='<input type="submit" value="yes" class="button" />';
		$body.='</form>';
		$body.='</td>';

		$body.='<td>';
		$body.='<form name="del_no" action="'.$this->settings['page'].'" method="post">';
		$input=array();
		$input=$this->input;
		unset($input['action']);
		unset($input['uid']);
		$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'" />';		
		$body.='<input type="submit" value="no" class="button" />';	  
		$body.='</form>';
		$body.='</td>';
		$body.='</tr></table>';

		$body.='</div>';
		$this->body=$body;
	}





	function delete_record2(){
		$query = "SELECT ".$this->config['table'].".* FROM ".$this->config['table']." where uid='".$this->input['uid']."'";
		$res  = $this->settings['dbconn']->query($query);
		$row=$res->fetch( PDO::FETCH_ASSOC );
			
		$query = "DELETE FROM ".$this->config['table']."  where uid='".$this->input['uid']."'";
		$res  = $this->settings['dbconn']->query($query);
		unset($this->input['action']);
		unset($this->input['uid']);
		
		$method="post_delete_".$this->input['section_name'];
		if(method_exists($this,$method)) {
			$body.=$this->$method($row);
		}		
		
		$this->navigation_holder();		
		$this->gen_display();
	}




	function delete_attachment($uid,$directory){
		$sql="SELECT * FROM tx_onqmediamanager_uploaded_files WHERE uid='".$uid."' LIMIT 1";
		
		$res=$this->settings['dbconn']->query($sql);
		$row_attach = $res->fetch( PDO::FETCH_ASSOC );
	
		if(file_exists($directory.$row_attach['newname'])) {
	  		unlink($directory.$row_attach['newname']);
	  		
		}

		$res=$this->settings['dbconn']->query('DELETE FROM tx_onqmediamanager_uploaded_files WHERE uid = '.$uid);

	}





	function check_errors($ed) {
		$error=array();
		//need to check if I have to check for date
		unset($parts);

		for($i=0;$i<count($this->config['var_edit']);$i++){
			$parts=explode(",",$this->config['var_edit'][$i]);
			

			if($parts[3]=="DATEPICKERHOUR" || $parts[3]=="DATEPICKER") {
			
				for($j=0;$j<count($this->config['var_req']);$j++){
					$parts2=explode("|",$this->config['var_req'][$j]);
					if($parts2[2]==$parts[2] && $this->in[$this->config['table']][$parts2[2]]==0){
						$error[$parts2[2]]=$parts2[0];
					} else {
					
					}	
				}
			}
		}



		unset($parts);
		
		
		for($i=0;$i<count($this->config['var_req']);$i++){
			$parts=explode("|",$this->config['var_req'][$i]);
			if(isset($this->in[$parts[1]][$parts[2]]) && strlen($this->in[$parts[1]][$parts[2]])<=0){
				$error[$parts[2]]=$parts[0];
			} else {
				if(isset($this->input['mobile_id_frame']) && !isset($this->in[$this->config['table']]['mobile_number_id'])){
					$this->in[$this->config['table']]['mobile_number_id']=$this->input['mobile_id_frame'];
				}							

				if(isset($this->input['plan_id_frame']) && !isset($this->in[$this->config['table']]['plan_id'])){
					$this->in[$this->config['table']]['plan_id']=$this->input['plan_id_frame'];
				}							


			
			}		
		}

		$this->error=$error;
		

		
		$method='check_error_'.$this->input['section_name'];
	
		if(method_exists($this,$method)) {
			$this->$method($ed,$error);
		} 		
		
		//print_r($this->error);
		
		
	}


	function check_error_date($error,$var_name,$message){
		for($i=0;$i<count($this->config['var_edit']);$i++){
			$parts=explode(",",$this->config['var_edit'][$i]);
			if($parts[3]=="DATEPICKERHOUR" || $parts[3]=="DATEPICKER") {
				if($var_name==$parts[2] && $this->in[$this->config['table']][$var_name]==0){
					$error[$var_name]=$message;
				} 
			}
		}
		return $error;	
	}


	function iseven($number) {
		if (($number % 2)==0) {return false;} else {return true;};
	}



	function Currency ($n) {
		settype($n,"float");	
		$n=sprintf("%.2f",$n);
		return($n);
	}


	function update(){
		$this->check_errors('1');

		if(count($this->error) > 0){
			$this->edit_record('1');
		} else {
			
			if(method_exists($this,'pre_update_common')) {
				$this->pre_update_common();
			}			
			

			$query  = "SELECT * FROM ".$this->config['table']." ";
			$query .= " WHERE uid =".$this->input['uid'];
			$res  = $this->settings['dbconn']->query($query);
	
			$old_row[$this->config['table']] = $res->fetch( PDO::FETCH_ASSOC );
		
		

			unset($this->error);
			while (list($key[],$val[]) = each ($this->in[$this->config['table']])) { }
      	$query  = "UPDATE ".$this->config['table']." SET ";
			for($s=0;$s<count($key);$s++){
				if(strlen($key[$s]) > 0){
					$query .= $key[$s]."='".addslashes($val[$s])."'";;
					if($s<(count($key)-1)) $query.=",";
				}
			}
			if(substr($query,(strlen($query)-1),1)==",") $query=substr($query,0,(strlen($query)-1));
			$query .= " WHERE uid =".$this->input['uid'];
			
			$res  = $this->settings['dbconn']->query($query);
			
			
			
			$method="post_update_".$this->input['section_name'];
			if(method_exists($this,$method)) {
				$body.=$this->$method($this->input['uid']);
			}			
		

			$this->input['action']="";
			unset($this->in);
			


			$this->gen_display();
		}

	}








	function copytable($in,$copytable) {
		for($s=0;$s<count($in);$s++){
			unset($in[$s]);
		}

		unset($key);
		unset($val);

		while (list($key[],$val[]) = each ($in)) { }
			unset($in['uid']);
			unset($key);
			unset($val);
			reset($in);

			while (list($key[],$val[]) = each ($in)) { }


			$query  = "INSERT INTO ".$copytable." (";
			for($s=0;$s<count($key);$s++){
				if(strlen($key[$s]) > 0){
					$query .= $key[$s];
					if($s<(count($key)-1)) $query.=",";
				}
			}

			if(substr($query,(strlen($query)-1),1)==",") $query=substr($query,0,(strlen($query)-1));

			$query.=") \n";
			$query .= "VALUES("; 

			for($s=0;$s<count($key);$s++){
				if(strlen($key[$s]) > 0){
					$query .= "'".addslashes(strip_tags($val[$s]))."'";
					if($s<(count($key)-1)) $query.=",";
				}
			}

			if(substr($query,(strlen($query)-1),1)==",") $query=substr($query,0,(strlen($query)-1));
			$query.=")";
			$res  = $this->settings['dbconn']->query($query);
	}




	function remove($fileglob) {
		foreach (glob($fileglob) as $filename){
			unlink($filename);
		}
	}


	function show_attachments($parent_id,$section_name,$directory) {
		$body='';
		if(strlen($parent_id) > 0) {
		
			if($section_name=="attachments_devices"){
				$id=$parent_id;
			}					
		
		
		 
			$sql="SELECT * FROM tx_onqmediamanager_uploaded_files WHERE parent_id='".$parent_id."' AND section_name='".$section_name."'";
			$res=$this->settings['dbconn']->query($sql);;
			
			
 
 			if($res->fetchColumn() > 0) {
 		  		$body.='<table border="0" cellpadding="0" cellspacing="0">';
 		  		
 	  			while($row_attach = $res->fetch( PDO::FETCH_ASSOC )) {
 	  				$body.='<tr><td style="padding:3px">';
					$body.='<a href="./download_documents_devices.php?uid='.$row_attach['uid'].'&id='.$row_attach['id'].'&directory='.$directory.'">';
					if(strlen($row_attach['description']) > 0) {
						$body.=stripslashes($row_attach['description']);
					} else {
				  		$body.="No Description";
					}
					$body.='</a>';
					$body.='</td>';
				
					if($this->config['security_mode']=="administrator") {	
					
					
						$body.='<td style="padding:3px 3px 3px 20px">';
						$input=array();
						//$input=$this->input;
						$input['section_name']=$this->input['section_name'];
						if($section_name=="attachments_numbers"){
							$input['mobile_id_frame']=$id;
						}
						$input['attachment_uid']=$row_attach['uid'];
						$input['action']='delete_attachment';
						
						$body.='<a href="'.$this->settings['page'].'&'.$this->settings['ext_name'].'[all_saved]='.base64_encode(serialize($input)).'" onclick="javascript:return confirm(\'Are you sure you want to delete this file?\');" >';
						$body.='delete';
						$body.='</a>';
						$body.='</td>';
					}
 	  			}
 	  			$body.='</table>';
 			}
		}
		return $body;  
	}



	function send_email($text_version,$html_version,$subject,$to,$from,$from_name,$reply_to,$reply_to_name,$replace_fields,$file='',$file2='') {
		require_once "./t3lib/class.t3lib_htmlmail.php";
		$text_version1=$this->text_replace2($text_version,$replace_fields);
		$html_version1=$this->text_replace2($html_version,$replace_fields);
		$functions->short_links=false;
		//exit("$text_version1,$html_version1,$subject,$to,$from,$from_name,$reply_to,$reply_to_name,$file");
		if($this->validEmail($to)) {
			$headers=array();
			$headers[]="FROM: ".$from." <".$from_name.">";
			$htmlmail=t3lib_div::makeInstance("t3lib_htmlmail");
			$htmlmail->start();
			//$htmlmail->useBase64();
			$htmlmail->addPlain($text_version1);
			$htmlmail->setHtml($html_version1);
			if(strlen($file) > 0) {
				$htmlmail->addAttachment($file);
			}
			if(strlen($file2) > 0) {
				$htmlmail->addAttachment($file2);
			}
			$htmlmail->theParts["messageid"]=$htmlmail->messageid;
			$htmlmail->from_email=$from;
			$htmlmail->from_name=$from_name;
			$htmlmail->replyto_email=$reply_to;
			$htmlmail->replyto_name=$reply_to_name;
			$htmlmail->organisation="";
			//$htmlmail->return_path=$this->tsconfig['thankYouEmailAddressMailer'];
			$htmlmail->return_path="";
			$htmlmail->recipient=$to;
			$htmlmail->subject=$subject;
			$htmlmail->setHeaders();
			$htmlmail->setContent();
			$htmlmail->sendTheMail();
		}
	}


	function text_replace2 ($texto,$replace_fields){
		$texto=str_replace("###FIRSTNAME###",stripslashes($replace_fields['firstname']),$texto);
		$texto=str_replace("###SURNAME###",stripslashes($replace_fields['surname']),$texto);
		$texto=str_replace("###COST_CENTRE###",stripslashes($replace_fields['cost_centre']),$texto);
		$texto=str_replace("###FLEET_MANAGER_NAME###",stripslashes($replace_fields['fleet_manager_name']),$texto);
		$texto=str_replace("###FLEET_MANAGER_EMAIL###",stripslashes($replace_fields['fleet_manager_email']),$texto);
		$texto=str_replace("###FLEET_MANAGER_FAX###",stripslashes($replace_fields['fleet_manager_fax']),$texto);
		return $texto;
	}



	function validEmail($email) {
  		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	
	function save_attachment($parent_id,$section,$directory) {
		$body='';

		if (is_uploaded_file($_FILES['tx_onqmediamanager_pi1']['tmp_name']['document_attachment'])) {
			$in_file=array();
			$in_file['parent_id']=$parent_id;
			$in_file['section_name']=$section;
			$in_file['description']=$this->piVars['attachment_description'];
			$in_file['size']=$_FILES['tx_onqmediamanager_pi1']['size']['document_attachment'];
			$in_file['type']=$_FILES['tx_onqmediamanager_pi1']['type']['document_attachment'];
			$in_file['name']=$_FILES['tx_onqmediamanager_pi1']['name']['document_attachment'];
			//$in_file['document_attachment']['error']=$_FILES["tx_onqfeuseradmin_pi1"]['error']['document_attachment'];
		
			$name_parts=explode(".",$in_file['name']);
      	$file_extension=".".$name_parts[count($name_parts)-1];
        
		  
		  //The structure of the directory of attachments will be:
		  
		  //directory->parent_id->name_of_document
		  //the name of the document will be the id of the  parent
		  //with _attachx at the end
		  
		  //The system will look for a name that hasn't been taken 

			if(!is_dir($directory)) {
				if(!mkdir($directory,0770)) {
			  		exit("Could not create directory ".$directory);
			 	}		  
			}
			
			$folder='/'.$parent_id.'/';
		  
  
			//if the directory doesn't exist, will try to create it
		
			if(!is_dir($directory.$folder)) {
				if(!mkdir($directory.$folder,0770)) {
			  		exit("Could not create directory ".$directory.$folder);
			 	}		  
			}
		
			$file_number=1;
			$file_name=$parent_id.'_attach';
		
		
			while(file_exists($directory.$folder.$file_name.$file_number.$file_extension)) {
		 		 $file_number++;
			}
		
			if(!rename($_FILES['tx_onqmediamanager_pi1']['tmp_name']['document_attachment'],$directory.$folder.$file_name.$file_number.$file_extension)) {
				$body.="File Upload Failed ".$directory.$folder.$file_name.$file_number.$file_extension;
        	} else {
        		$in_file['newname']=$folder.$file_name.$file_number.$file_extension;
        		$in_file['id']=uniqid('');
				$res=$this->settings['dbconn']->query($this->generate_insert('tx_onqmediamanager_uploaded_files', $in_file));        		
        		
        		$body.="<br />File Upload was successful";
        	}
        

		}

		if(file_exists($_FILES['tx_onqmediamanager_pi1']['tmp_name']['document_attachment'])) {
	 		unlink($_FILES['tx_onqmediamanager_pi1']['tmp_name']['document_attachment']); 
		}
		return $body;
	}



	function save_new(){
		$this->check_errors('2');
		if(count($this->error) > 0){
			//print_r($this->error);
			$this->edit_record('2');
		} else {
			unset($this->error);

			$method="pre_save_new_".$this->input['section_name'];
			if(method_exists($this,$method)) {
				$body.=$this->$method();
			}

			
		
			foreach($this->in[$this->config['table']] as $name=>$value){
				//$this->in[$this->config['table']][$name]=$GLOBALS['TYPO3_DB']->quoteStr($value);
				$this->in[$this->config['table']][$name]=addslashes($value);
			}
			
			$sql=$this->generate_insert($this->config['table'], $this->in[$this->config['table']]);
			$res=$this->settings['dbconn']->prepare($sql);
			$res->execute();
			$id=$this->settings['dbconn']->lastInsertId();
			

			$method="post_save_new_".$this->input['section_name'];
			if(method_exists($this,$method)) {
				$body.=$this->$method($id);
			}

			$this->input['action']="";
			unset($this->in);
			
			if(!isset($this->config['var_display'])){
				if(isset($this->config['thankyou'])){
					$body.=$this->config['thankyou'];
					$body.=$this->nav_add();
					return $body;	
				} else {
					$this->edit_record('2');
				}			
			} else {			
				$this->gen_display();
			}			
			
		
		}

	}


	function generate_insert($table,$fields){
		
		foreach($fields as $name=>$value) {
			$field_names[]=$name;
			$field_values[]="'".addslashes($value)."'";					
		}
				
		$sql="INSERT INTO ".$table." (".implode(",",$field_names).") VALUES (".implode(",",$field_values).")";
		return $sql;
	}		



	function navigation_holder(){
		if($this->input['action']=="edit" || $this->input['action']=="new" || $this->input['action']=="create_new" || $this->input['action']=="update" || $this->input['action']=="del" || $this->input['action']=="del2"){
			return;
		}
	
		$template = $this->getSubpart($this->fileResource("templates/onqmediamanager_template.html"),"###NAVIGATION_HOLDER###");
		
		
		if($this->config['nav_menu'] == 1){
			$markerArray["###NAV_MENU###"]=$this->navigation_menu();
		} else {
			$markerArray["###NAV_MENU###"]="";		
		}
		$markerArray["###NAV_FILTERS###"]=$this->nav_filters();
		
		if($this->config['nav_search'] == 1){
			$markerArray["###NAV_SEARCH###"]=$this->nav_search();
		} else {
			$markerArray["###NAV_SEARCH###"]="";
		}
		
		if($this->config['nav_add'] == 1){
			$markerArray["###NAV_ADD###"]=$this->nav_add();
		} else {
			$markerArray["###NAV_ADD###"]="";
		}
		
		$template=$this->substituteMarkerArray($template, $markerArray);
		return $template;
	}	



	function navigation_menu(){
		$body="";
		$body.='
		<script language="JavaScript">
		<!--
			function change_section(form) {
			var myindex=form.section_name.selectedIndex
			location.href=(form.section_name.options[myindex].value);
			}

		//-->
		</script>
		';

		$body.='<form name="select_section" style="margin:0;padding:0">';
		$body.='<select name="section_name" onchange="change_section(this.form)" >';
		
		$i=0;
		$optgroup="";
		foreach($this->settings['sections'] as $section_parts){
			$parts=explode("|",$section_parts);
			$menu_type=$parts[0];
			$section=$parts[1];
			if($i==0 && $this->config['security_mode']=="administrator") {
				//$body.='<optgroup label="'.$menu_type.'">';
				$optgroup=$menu_type;	
			}
			
			if($optgroup!=$menu_type && $this->config['security_mode']=="administrator"){
				//$body.='</optgroup><optgroup label="'.$menu_type.'">';
				$optgroup=$menu_type;				
			}		
			
			$aux=array();			
			$body.='<option value="'.$this->settings['page'];
			if(!strstr($this->settings['page'],"?")) $body.="?";
			$aux['reset_filters']="1";
			$aux['section_name']=$section;
			$body.='&'.$this->settings['prefixId'].'[all_saved]='.base64_encode(serialize($aux));
			$body.='" ';
	      if($this->input['section_name'] == $section){
					 $body.='selected="selected" '; 
			}
			$body.= '>'.$this->config_all[$section]['title'].'</option>';			
			$i++;
		}
		
		if($this->config['security_mode']=="administrator"){
			//$body.='</optgroup>';
		}			
		$body.='</select>';
		$body.='</form>'; 
		return $body;
	}


	function nav_filters(){
		
		$body='';
		if(isset($this->config['filters']) && is_array($this->config['filters'])){
			foreach($this->config['filters'] as $filter){
				$filter_parts=explode("|",$filter);
				$do_method=0;
				if($filter_parts[2]!="date_var"){
					$do_method=1;			
				} else {
					$filter_vars=explode(",",$filter_parts[3]);
					if(count($filter_vars)> 1) {
						$body.=$this->filter_vars($filter_parts[0],$filter_parts[1],$filter_parts[2],$filter_parts[3]);
					} else {
						//set cookie here for single filter var
						if($filter_vars[0] !=''){
							$filter_vars_parts=explode(";",$filter_vars[0]);
							if($filter_vars_parts[1]!=""){
								if(!isset($GLOBALS["_COOKIE"][$filter_parts[0].'filter'])){
									setcookie($filter_parts[0].'filter',$filter_vars_parts[1],time()+(3600*24*10)); // delete cookie after 10 days
									$GLOBALS["_COOKIE"][$filter_parts[0].'filter']=$filter_vars_parts[1];							
								}
						
							}					
						}
					}
				}
			
				if($do_method==1){
					if($filter_parts[0]=="breaker"){
						$body.="<br />";
					} else {
						$method=$filter_parts[0].'_filter';
						if(method_exists($this,$method)) {
							$body.=$this->$method($filter_parts[0],$filter_parts[1],$filter_parts[2]);
						}
					}
				}
			}
		}
		return $body;
	}


	function filter_vars($name,$title,$type,$filter_vars){
		$body='';
		$body.='
			<script>
			<!--
				function change_filter_'.$name.'(form) {
					var myindex=form.switch_filter_'.$name.'.selectedIndex;
					location.href=(form.switch_filter_'.$name.'.options[myindex].value);
				}

			//-->
			</script>
			';
	

		$url=$this->settings['page'];
		$url.='&'.$this->settings['prefixId'].'[all_saved]=';
		$input=array();
		$input=$this->input;
		unset($input['page']);
		$url.=base64_encode(serialize($input));
		$url.='&'.$this->settings['prefixId'].'[action]=set_filter_'.$name;
		
		
		$body.='<form name="switch_filter_vars">';
		$body.='<span class="tx_onqmediamanager_filter_title">'.$title.'</span> <span class="tx_onqmediamanager_filter_select"><select name="switch_filter_'.$name.'" size="1" onchange="change_filter_'.$name.'(this.form)">'."\n";
		$body.='<option value="'.$url.'&'.$this->settings['prefixId'].'[filter]=all" ';
		$body.='>All</option>'."\n";
		
		$vars=explode(",",$filter_vars);
		
		foreach ($vars as $value){
			if($value!=''){
				$vars_split=explode(";",$value);
				$body.='<option value="'.$url.'&'.$this->settings['prefixId'].'[filter]='.$vars_split[1].'" ';
				if(isset($GLOBALS["_COOKIE"][$name.'filter'])){
					if($GLOBALS["_COOKIE"][$name.'filter']==$vars_split[1]) {
						$body.='selected="selected" ';
					}
				}
				$body.='>'.$vars_split[0].'</option>'."\n";
			} 		
		}
		$body.='</select></span></form>';
		return $body;				
	
	}


	function nav_search() {
		$body="";
		$body.='<form name="find" action="'.$this->settings['page'].'" method="post">';
		$input=array();
		$input=$this->input;
	
		if($this->input['section_name'] == 'vehicles') {
			$input['action']="gen_display_vehicles";
		} else {
			unset($input['action']);
		}

		$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'" />';
		
		if(isset($this->input['find']) && strlen($this->input['find']) > 0) {
			$body.='<input type="text"  name="'.$this->settings['ext_name'].'[find]" ';
			$body.="value=\"".stripslashes($this->input['find'])."\" "; 
			$body.=' style="width:80px;" />';
		}
		
		
		$body.='<input type="submit" value="'.$this->settings['find_button_caption'].'" class="onqmediamanager_button" />';
		$body.='</form>';
		return $body;
	}
	


	function nav_add() {
		$body='';
		$add_message='';
		$method='add_condition_'.$this->input['section_name'];
		if(method_exists($this,$method)) {
			$add_message=$this->$method('add');
		} 

		$body.='<form name="new" action="'.$this->settings['page'].'" method="post" ';
		if($add_message!='') {
			$body.=' onsubmit="alert(\''.addslashes($add_message).'\'); return false;" ';
		}
		$body.='>';
		$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[action]" value="';
  		$body.='new';
		$body.='" />';
		$input=array();
		$input['section_name']=$this->input['section_name'];
		$body.='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($this->input)).'" />';
		$body.='<input type="submit" value="Add Record" class="onqmediamanager_button" />';
		$body.='</form>';
		return $body;
	}
	


	function app_content(){
		return $this->body;
	}


	




	function editor_tabs_menu() {
		$body='';
		
		if(isset($this->config['tabs']) && count($this->config['tabs']) > 0){
		
			$body.='	
			<div id="onqmediamanager_additional_menu">
			<ul>
			';
			
			$i=0;			
			foreach($this->config['tabs'] as $tab){
				$tab_parts=explode("|",$tab);
				if($tab_parts[1]=="editor"){
					$body.='<li><a class="onqfancy" href="#'.$tab_parts[0].'_section_div" >'.$this->config_all[$tab_parts[0]]['title'].'</a></li>';
				}
				
				if($tab_parts[1]=="attachment"){
					$body.='<li><a class="onqfancy" href="#'.$tab_parts[0].'_section_div">'.$tab_parts[2].'</a></li>';
				}				
				
			}
			$body.='</ul></div>';
		}
		
		return $body;
	}

	function editor_tabs_content($ed,$row) {
		$body='';
		$pageid='271';
		$i=0;
		$onload='';
		$has_tabs=0;
		if(isset($this->config['tabs']) && is_array($this->config['tabs'])){			
			foreach($this->config['tabs'] as $tab){
				if($i>=0){
					$has_tabs=1;
					$tab_parts=explode("|",$tab);
												
					if($tab_parts[1]=="editor") {
	
						$body.='<div id="'.$tab_parts[0].'_section_div" class="iframe_div" style="z-index:2000000000;position:relative">';
						$body.='<h2 style="margin:0;padding:0;">'.$this->config_all[$tab_parts[0]]['title'];
					
						if($this->input['section_name']=="numbers"){
							$body.=' for '.$row['mobile_number'];
						}

						if($this->input['section_name']=="plans"){
							$body.=' '.$row['plan_name'];
						}

										
						$body.='</h2>';
						$body.='<iframe id="onqmediamanager_iframe_id'.$i.'" style="min-height:320px"  src="index.php?id='.$pageid.'&type=98&ver='.uniqid().'&tx_onqmediamanager_pi1[section_name]='.$tab_parts[0];
					
						if($this->input['section_name']=="numbers"){
							$body.='&tx_onqmediamanager_pi1[mobile_id_frame]='.$row['uid'];
						}						

						if($this->input['section_name']=="plans"){
							$body.='&tx_onqmediamanager_pi1[plan_id_frame]='.$row['uid'];
						}						


						$body.='" ></iframe>';

						$body.='</div>';
					}

					if($tab_parts[1]=="attachment") {
						$body.='<div id="'.$tab_parts[0].'_section_div"  class="iframe_div" style="z-index:2000000000;position:relative">';
						$body.='<h2 style="margin:0;padding:0">Attachments';
						if($this->input['section_name']=="drivers"){
							$body.=' for '.$row['firstname']." ".$row['surname']." empid:".$row['employee_id'];
						}
					
						if($this->input['section_name']=="vehicles"){
							$body.=' for '.$row['rego']." ".$row['make']." ".$row['model'];
						}
						$body.='</h2>';
						$body.='<iframe id="onqmediamanager_iframe_id'.$i.'" style="min-height:320px" src="index.php?id='.$pageid.'&type=98&ver='.uniqid().'&tx_onqmediamanager_pi1[section_name]='.$tab_parts[0].'&tx_onqmediamanager_pi1[action]=attachments';
					
						if($this->input['section_name']=="numbers"){
							$body.='&tx_onqmediamanager_pi1[mobile_id_frame]='.$row['uid'];
						}						

						if($this->input['section_name']=="plans"){
							$body.='&tx_onqmediamanager_pi1[plan_id_frame]='.$row['uid'];
						}						


						$body.='" ></iframe>';					
						$body.='</div>';
					}
				}
				$i++;
			}
		}
		
				
		return $body;
	}



	function attachments(){
	


		$section_name='';
		$title='';
		$directory;
		

		if(isset($this->input['mobile_id_frame'])){
			$section_name="numbers";
			$uid=$this->input['mobile_id_frame'];		
		}
		

		
		if($section_name=='' || $uid==''){
			return;		
		}
		


		if($section_name == "devices"){
			$sql="SELECT uid FROM tx_onqmediamanager_tbldevices WHERE uid='".$uid."'";

			$res=$this->settings['dbconn']->query($sql);
			if($res->fetchColumn() <= 0) {
				return;			
			} else {
				$row=$res->fetch( PDO::FETCH_ASSOC );
				foreach($this->config_all['devices']['tabs'] as $tab){
					$tab_parts=explode("|",$tab);
					if($tab_parts[0]==$this->input['section_name']){
						$title=$tab_parts[2];
						$directory=$tab_parts[3];
						if($directory=='') {
							return;
						}
					}
				}
			}
		}
	

		$markerArray=array();
		$markerArray['###PAGE###']=$this->settings['page'];
		$input=array();
		$input=$this->input;
		$input['action']="save_attachment";
		$markerArray['###FORM_PARAMS###']='<input type="hidden" name="'.$this->settings['ext_name'].'[all_saved]" value="'.base64_encode(serialize($input)).'">';		
		$markerArray['###ATTACHMENT_NAME###']=$title;
		


		$markerArray['###ADMIN_DISPLAY###']='display:none';
		if($this->config['security_mode']=="administrator") {
			$markerArray['###ADMIN_DISPLAY###']='';
		}
		
		$markerArray['###SAVE_ATTACHMENT###']='';
		if($this->input['action']=="save_attachment"){
			$markerArray['###SAVE_ATTACHMENT###']=$this->save_attachment($row['uid'],$this->input['section_name'],$directory);
			$this->input['action']="attachments";		
		}

		if($this->input['action']=="delete_attachment"){
			$this->delete_attachment($this->input['attachment_uid'],$directory);
			$this->input['action']="attachments";		
		}

		
		$markerArray['###ATTACHMENTS###']=$this->show_attachments($row['uid'],$this->input['section_name'],$directory);
		$template = $this->getSubpart($this->fileResource("templates/onqmediamanager_template.html"),"###ATTACHMENTS_TEMPLATE###");	
		$template=$this->substituteMarkerArray($template, $markerArray);
		$this->body=$template;	
	}


	function getAge($Birthdate){
		$YearDiff = date("Y") - date("Y",$Birthdate);
		$MonthDiff = date("m") -  date("m",$Birthdate);
		$DayDiff = date("d") - date("d",$Birthdate);
		// If the birthday has not occured this year
		if ($MonthDiff < 0 || ($MonthDiff == 0 && $DayDiff < 0)) {
			$YearDiff--;
 		}
 		return($YearDiff);
	}
	
	
	
	function generic_table_insert($table) {
		while (list($key[],$val[]) = each ($this->in[$table])) { }  
		$query  = "INSERT INTO ".$table." (";
		for($s=0;$s<count($key);$s++){
			if(strlen($key[$s]) > 0){
				$query .= $key[$s];
				if($s<(count($key)-1)) $query.=",";
			}
		}
		if(substr($query,(strlen($query)-1),1)==",") $query=substr($query,0,(strlen($query)-1));
		$query.=") \n";
		$query .= "VALUES("; 
		for($s=0;$s<count($key);$s++){
			if(strlen($key[$s]) > 0){
				$query .= "'".addslashes(strip_tags($val[$s]))."'";
				if($s<(count($key)-1)) $query.=",";
			}
		}
		if(substr($query,(strlen($query)-1),1)==",") $query=substr($query,0,(strlen($query)-1));
		$query.=")";
		//print_r($query);
		$res  = $this->settings['dbconn']->query($query);
			
		return $this->settings['dbconn']->lastInsertId();
	}
	
	

	function check_password($password) {
		if(strlen($password) < 7){
				return "The password must be at least 7 characters long";
		} elseif(!$this->check_number($password)){
				return "The password must contain at least one number";
		} elseif(!$this->check_upper($password)){
				return "The password must contain at least one upper case character";
		} elseif(!$this->check_lower($password)){
				return "The password must contain at least one lower case character";
		} else {
			return "";	
		}
	
	}



	//checks if there is a number in a string
	function check_number($text) {
		for($i=0;$i<strlen($text);$i++) {
			$ord=ord(substr($text,$i,1));
			if($ord >= 48 && $ord <=57) {
				return true;	
			}
		}
		return false;
	}


	function check_upper($text) {
	
		for($i=0;$i<strlen($text);$i++) {
			$ord=ord(substr($text,$i,1));
			if($ord >= 65 && $ord <=90) {
				return true;	
			}
		}
		return false;
	}


	function check_lower($text) {
		for($i=0;$i<strlen($text);$i++) {
			$ord=ord(substr($text,$i,1));
			if($ord >= 97 && $ord <=122) {
				return true;	
			}
		}
		return false;
	}


	function logout_box(){
			$body="";
			$body.='
			<div style="clear:both;padding-top:20px">			
			<p>Are you sure you want to log out?';
			
			
			$body.='</p>
			<form action="'.$this->settings['page'].'" target="_top" method="post">
			<table border="0">
			<tr><td>
			<input type="submit" id="login_button" name="submit" value="Logout" />
			<input type="hidden" name="'.$this->settings['prefixId'].'[logintype]" value="logout" />
			<input type="hidden" name="'.$this->settings['prefixId'].'[section_name]" value="logout_action" />
			</td></tr>
			</table>
			</form>
			</div>';
			return $body;		
	}


	
	function logout_action(){
		if(isset($this->input["logintype"]) && $this->input["logintype"]=="logout") {
			$sessionData=array();
			$_SESSION[$this->settings['prefixId'].'_login']=$sessionData;		
			//$this->reset_filters($this->input,$this->settings);
			unset($this->input["logintype"]);
		}		
	}


	function login_box() {
		$body='';
		$username='';
		$password='';
		$sessionData=$this->settings['sessiondata'];
		
		

		if(isset($this->input["user"]) && $this->input["user"] != "" && isset($this->input["pass"]) && $this->input["pass"] != ""){
			$sql="SELECT * FROM users WHERE username='".$this->input["user"]."' AND password='".$this->input["pass"]."'";
			$res  = $this->settings['dbconn']->query($sql);
			
			//fetchColumn()
			//fetchColumn()
			//fetch_assoc
			//fetch( PDO::FETCH_ASSOC )
			
			
			if($res->fetchColumn() <= 0 ){
				$body.='<p>The combination of user name and password is not correct, please try again.</p>';
				$sessionData=array();
				$sessionData['loggedin']=0;
			} else {
				$sql="SELECT * FROM users WHERE username='".$this->input["user"]."' AND password='".$this->input["pass"]."'";
				$res  = $this->settings['dbconn']->query($sql);				
				$sessionData = $res->fetch( PDO::FETCH_ASSOC );
				$sessionData['loggedin']=1;
			}

			$_SESSION[$this->settings['prefixId'].'_login']=$sessionData;
		}

	
	


		
		if((!isset($this->input["logintype"]) || (isset($this->input["logintype"]) && $this->input["logintype"]=="login")) && (!isset($sessionData['loggedin']) || (isset($sessionData['loggedin']) && $sessionData['loggedin']==0))) {
			$body.='
			<div>
			<p>Please enter your username and password:</p>			
			<form action="'.$this->settings['page'].'" target="_top" method="post">
			<table border="0">
			<tr><td>User name:</td><td><input type="text" name="'.$this->settings['prefixId'].'[user]" value="" /></td></tr>
			<tr><td>Password:</td><td><input type="password" name="'.$this->settings['prefixId'].'[pass]" value="" /></td></tr>
			<tr><td colspan="2" >
			<input type="submit" id="login_button" name="submit" value="Login" />
			<input type="hidden" name="'.$this->settings['prefixId'].'[section_name]" value="login" />
			<input type="hidden" name="'.$this->settings['prefixId'].'[logintype]" value="login" />
			</td></tr>
			</table>
			</form>
			</div>	
			';
		}
		


		return $body;
	}




	function getSubpart($content, $marker) {
		$start = strpos($content, $marker);
		if ($start === FALSE) {
			return '';
		}
		$start += strlen($marker);
		$stop = strpos($content, $marker, $start);
		if ($stop === FALSE) {
			return '';
		}
		$content = substr($content, $start, $stop - $start);
		$matches = array();
		if (preg_match('/^([^\\<]*\\-\\-\\>)(.*)(\\<\\!\\-\\-[^\\>]*)$/s', $content, $matches) === 1) {
			return $matches[2];
		}
		// Resetting $matches
		$matches = array();
		if (preg_match('/(.*)(\\<\\!\\-\\-[^\\>]*)$/s', $content, $matches) === 1) {
			return $matches[1];
		}
		// Resetting $matches
		$matches = array();
		if (preg_match('/^([^\\<]*\\-\\-\\>)(.*)$/s', $content, $matches) === 1) {
			return $matches[2];
		}
		return $content;
	}


	function fileResource($path){
		$myfile = fopen($path, "r") or die("Unable to open file!");
		$file_contents=fread($myfile,filesize($path));
		fclose($myfile);	
		return $file_contents; 
	}



	function substituteMarkerArray($template, $markerArray){
		foreach($markerArray as $marker=>$value){
			$template=str_replace($marker,$value,$template);
		}
		return $template;	
	}	



		
} //end of the class

?>