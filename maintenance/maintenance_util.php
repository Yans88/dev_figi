<?php

function category_rows($is_enabled_only = false)
{
	$result = array();
	$query = "SELECT * FROM checklist_category";
    if ($is_enabled_only)
        $query .= " WHERE enabled = 1 ";
    $query .= ' ORDER BY linkable_item ASC, category_name ASC';
	$rs = mysql_query($query);
	while($rec = mysql_fetch_assoc($rs))
		$result[] = $rec;
	return $result;
}

function category_list($is_enabled_only = false)
{
	$result = array();
	$rows = category_rows($is_enabled_only);
	foreach($rows as $rec)
		$result[$rec['id_category']] = $rec['category_name'];
	return $result;
}

function get_category($id)
{
	$result = array();
	$query = "SELECT *  FROM checklist_category WHERE id_category = '$id'";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0)
		$result = mysql_fetch_assoc($rs);
	return $result;
}


function get_max_checking(){
	$result = array();
	$query = "select id_check, modified_on, id_location, modified_by, full_name from checklist_checking as cc 
			  left join user u on u.id_user = cc.modified_by where modified_on = (select max(modified_on) 
			  from checklist_checking as b where cc.id_location = b.id_location)";
	$res = mysql_query($query);	
	while($rec = mysql_fetch_assoc($res)){
		$result[$rec['id_location']]['id_check'] = $rec['id_check'];
		$result[$rec['id_location']]['fullname'] = $rec['full_name'];
	}
	return $result;
}

function get_location_checking($searchtext = null, $order = 'l.location_name', $sort = null, $start=0, $limit=10){
	$result = array();
	$fmt = '%e-%b-%Y %H:%i';		
	//$query_check = "select cc.id_check, MAX(cc.modified_on) from checklist_checking cc, location l where l.id_location = cc.id_location";
	//$result = mysql_query($query);	
	
	$query = "SELECT l.id_location, l.location_name, 
			(select MAX(cc.modified_on) from checklist_checking cc where l.id_location = cc.id_location) AS modified_on_format
			from location l";
	
	if(!empty($searchtext)){
		$query .=" where l.location_name like '%$searchtext%' ";
	}
	
	$query .=" order by $order $sort LIMIT $start,$limit ";
	
	//echo $query.mysql_error();
	$result = mysql_query($query);	
	return $result;
}


function cnt_location_checking($searchtext = null){
	$result = array();
	$fmt = '%e-%b-%Y %H:%i';	
	$query = "SELECT l.id_location, l.location_name, 
			(select MAX(cc.modified_on) from checklist_checking cc 
			where l.id_location = cc.id_location) AS modified_on_format from location l";
	if(!empty($searchtext)){
		$query .=" where l.location_name like '%$searchtext%' ";
	}
	$rs = mysql_query($query);	
	if ($rs && mysql_num_rows($rs)>0)
		$result = mysql_num_rows($rs);
	return $result;
}

function get_data_checking($id = array()){
	$id_check = array();
	foreach($id as $_id){
		$my_id = explode('_', $_id);
		$_id_check = $my_id[0];
		$_id_location = $my_id[1];
		$id_check[] = $_id_check;
		$id_location[] = $_id_location;		
	}
	$id_checks = implode(',', $id_check);
	$id_locations = implode(',', $id_location);
	$query = "SELECT l.location_name, cc.modified_on, u.full_name, i.asset_no, i.model_no, ccr.id_item, ccr.result, ccr.remark from checklist_checking cc
			  left join checklist_checking_result ccr on ccr.id_check = cc.id_check
			  left join location l on l.id_location = cc.id_location
			  left join user u on u.id_user= cc.modified_by
			  left join item i on i.id_item = ccr.id_item where cc.id_check in ($id_checks) and cc.id_location in ($id_locations)";
	//echo $query.mysql_error();
	$rs = mysql_query($query);	
	return $rs;
}

function export_data_maintanance($id=array()){
	$rs = get_data_checking($id);
	$crlf = "\r\n";
	ob_clean();
    ini_set('max_execution_time', 60);
    $today = date('dMY');
	$fname = 'figi_maintenance_checklist-$today.csv';
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=$fname");
    header("Pragma: no-cache");
    header("Expires: 0");
	echo 'Location_name,Modified by, Modified Date,Asset No, Model No,Result,Remarks'.$crlf;
	while ($rec = mysql_fetch_assoc($rs)){
		echo "$rec[location_name],$rec[full_name],$rec[modified_on],$rec[asset_no],$rec[model_no],";
		echo "$rec[result],$rec[remark]$crlf";
	}
	ob_end_flush();
    exit;
}
