<?php

include '../util.php';
include '../common.php';
include 'facility_util.php';

$id_facility= !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
    
$data = get_timesheets($id_facility);
$result['time_start'] = array();
$result['time_finish'] = array();
$result['info'] = get_facility($id_facility);
foreach ($data as $id => $rec){
	
	$result['time_start'][] = '<option value="'.$rec['time_start'].'">'.$rec['time_start'].'</option>';
	$result['time_finish'][] = '<option value="'.$rec['time_end'].'">'.$rec['time_end'].'</option>';
}

echo json_encode($result);
?>
