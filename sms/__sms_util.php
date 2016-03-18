<?php

function get_sms_list($search = 0){
	$result = array();
    $query = "select * from sms_management ";    
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}

function get_sms_by_id($id = 0){
    $query = "select * from sms_management where id_sms_school = $id";    
    $rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_assoc($rs);
	}
	return $rec;
}

function get_sms_count($month = 0){
	$result = array();
    $query = "select count(*) as cnt, module, msg_status from notification_message where msg_type='sms' ";
	if(!empty($month) && $month > 0){
		$query .= " and month(process_time) = '$month' ";
	}
    $query .=" group by module";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}


?>