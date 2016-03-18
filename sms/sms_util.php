<?php

function get_sms_list($month = 0){
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