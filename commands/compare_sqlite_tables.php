<?php

//download new table to /var/www/openmediamanager/sqlite/openmediamanagernew.sqlite and then run this command

function group_concat_step($context,$idx,$string,$separator) {return ($context) ? ($context . $separator . $string) : $string;}
function group_concat_finalize($context) { return $context; } 

$dbconnold = new PDO("sqlite:/var/www/openmediamanager/sqlite/openmediamanager.sqlite");
$dbconnold->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);

$tableold=array();
$tablesquery = $dbconnold->query("SELECT name FROM sqlite_master WHERE type='table';");
$tables = $tablesquery->fetchAll(PDO::FETCH_ASSOC);
foreach($tables as $table){
    $table_name=(string)$table['name'];

    $sql="PRAGMA table_info(".$table_name.")";
    $fieldsquery = $dbconnold->query($sql);
    $table_fields = $fieldsquery->fetchAll(PDO::FETCH_ASSOC);
    foreach($table_fields as $table_field){
	$tableold[$table_name]['fields'][$table_field['name']]=$table_field;
    }
}


$dbconnnew = new PDO("sqlite:/var/www/openmediamanager/sqlite/openmediamanagernew.sqlite");
$dbconnnew->sqliteCreateAggregate('group_concat', 'group_concat_step', 'group_concat_finalize', 2);

$tablenew=array();
$tablesquery = $dbconnnew->query("SELECT name FROM sqlite_master WHERE type='table';");
$tables = $tablesquery->fetchAll(PDO::FETCH_ASSOC);
foreach($tables as $table){
    $table_name=(string)$table['name'];

    $sql="PRAGMA table_info(".$table_name.")";
    $fieldsquery = $dbconnnew->query($sql);
    $table_fields = $fieldsquery->fetchAll(PDO::FETCH_ASSOC);
    foreach($table_fields as $table_field){
	$tablenew[$table_name]['fields'][$table_field['name']]=$table_field;
    }
}

$sql=array();
foreach($tablenew as $table_name=>$table_parts){
    if(!isset($tableold[$table_name])){
	$sql[]=create_table($table_name,$table_parts);
    } else {
	foreach ($table_parts['fields'] as $field=>$value){
	    if(!isset($tableold[$table_name]['fields'][$field])){
		$sql[]=alter_table($table_name,$table_parts,$field);
	    }
	}
    }
}


foreach($sql as $sql_command){
    $command = $dbconnold->query($sql_command);
}




function create_table($table_name,$table_parts){
    $body="";
    $body.="CREATE TABLE ".$table_name." (";
    $columns=array();
    $i=0;
    foreach ($table_parts['fields'] as $field=>$value){
	$columns[$i]=$field." ".$value['type']." ";
	if($value['pk']==1){
	    $columns[$i].="PRIMARY KEY ";
	}

	if($value['notnull']==1){
	    $columns[$i].="NOTNULL ";
	}

	if($value['dflt_value']!= ""){
	    $columns[$i].="DEFAULT ".$value['dflt_value']." ";
	}

	$i++;
    }
    $body.=implode(",",$columns);

    $body.=")";

    return $body;
}




function alter_table($table_name,$table_parts,$field){
    $body="";
    $body.="ALTER TABLE ".$table_name." ADD COLUMN ";
    $value=$table_parts['fields'][$field];

    $column=$field." ".$value['type']." ";
    if($value['pk']==1){
	$column.="PRIMARY KEY ";
    }

    if($value['notnull']==1){
	$column.="NOTNULL ";
    }

    if($value['dflt_value']!= ""){
	$column.="DEFAULT ".$value['dflt_value']." ";
    }

    $body.=$column;

    return $body;
}




//print_r($tablenew);


?>
