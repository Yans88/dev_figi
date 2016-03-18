<?php
if (!defined('FIGIPASS')) exit;
$_id = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;

$_msg = null;

$query = 'SELECT * FROM facility WHERE id_facility = ' . $_id;
$rs = mysql_query($query);
$rec = mysql_fetch_assoc($rs);

$duration = $rec['period_duration'];
$time_start = mktime(intval(substr($rec['time_start'], 0, 2)), intval(substr($rec['time_start'], 3, 2)), 0);
$time_end = mktime(intval(substr($rec['time_end'], 0, 2)), intval(substr($rec['time_end'], 3, 2)), 0);

if ($time_start < $time_end) {
    $values = array();
    $duration_ms = $duration * 60 ;
    $stm = $time_start;
    while ($stm < $time_end){
        $tm_start = date('H:i', $stm);
        $stm += $duration_ms;
        $tm_end = date('H:i', $stm);
        $values[] = "($_id, '$tm_start', '$tm_end')";    
    }
    if (count($values) > 0){
        // delete existing time sheet
        $query = 'DELETE FROM facility_timesheet WHERE id_facility = '. $_id;    
        mysql_query($query);
        // insert new time sheet
        $query = 'INSERT INTO facility_timesheet(id_facility, time_start, time_end) VALUES '. implode(',', $values);    
        mysql_query($query);
        //echo mysql_error().$query;
    }
    $_msg = 'Generated ' . count($values) .' periods. Start from '. substr($rec['time_start'], 0, 5) . ' to ' . substr($rec['time_end'], 0, 5); 
} else
    $_msg = 'Invalid time period. Please check time usage of the facility.';

/*
ob_clean();
header('Location: ./?mod=facility&sub=timesheet&act=view&id=' . $_id);
ob_flush();
ob_end_flush();
*/
echo '<script>';
if ($_msg != null)
    echo 'alert("'.$_msg.'");';
    
echo 'location.href="./?mod=facility&sub=timesheet&act=view&id=' . $_id.'";</script>';
exit;

?>