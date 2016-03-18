<?php

include '../util.php';
include '../common.php';
include 'facility_util.php';

$today = date('Y-m-d');
$id_facility= !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
$date_start = !empty($_POST['date_start']) ? $_POST['date_start'] : $today;
$date_finish = !empty($_POST['date_finish']) ? $_POST['date_finish'] : $today;
$readonly= !empty($_POST['readonly']) ? ($_POST['readonly']==1) : false;
    
$starttime = time_dMY($date_start);
$finishtime = time_dMY($date_finish);

//$data = get_timesheets($id_facility);
echo build_timesheet($id_facility, convert_date($date_start,'Y-m-d'), convert_date($date_finish,'Y-m-d'), $readonly);
?>
