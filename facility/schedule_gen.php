<?php
if (!defined('FIGIPASS')) exit;
$_id = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;

$_msg = null;

$query = 'SELECT * FROM facility WHERE id_facility = ' . $_id;
$rs = mysql_query($query);
$rec = mysql_fetch_assoc($rs);

$duration = $rec['period_duration'];
$start_time = mktime(intval(substr($rec['start_time'], 0, 2)), intval(substr($rec['start_time'], 3, 2)), 0);
$end_time = mktime(intval(substr($rec['end_time'], 0, 2)), intval(substr($rec['end_time'], 3, 2)), 0);

//echo $start_time .'-'.$end_time;
$values = array();
$duration_ms = $duration * 60 ;
$stm = $start_time;
while ($stm < $end_time){
    $tm_start = date('H:i', $stm);
    $stm += $duration_ms;
    $tm_end = date('H:i', $stm);
    $values[] = "($_id, '$tm_start', '$tm_end')";    
}
if (count($values>0)){
    // delete existing time sheet
    $query = 'DELETE FROM facility_schedule WHERE id_facility = '. $_id;    
    mysql_query($query);
    // insert new time sheet
    $query = 'INSERT INTO facility_schedule(id_facility, time_start, time_end) VALUES '. implode(',', $values);    
    mysql_query($query);
    //echo mysql_error().$query;
}


ob_clean();
header('Location: ./?mod=facility&sub=schedule&act=view&id=' . $_id);
ob_flush();
ob_end_flush();
exit;

?>