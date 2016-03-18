<?php
//require '../util.php';
require '../common.php';
require '../authcheck.php';
require 'calendar_util.php';
/*
$_start = !empty($_GET['start']) ? $_GET['start'] : mktime(0, 0, 0, date('n'), 1, date('Y'));
$_end = !empty($_GET['end']) ? $_GET['end'] : mktime(23, 59, 59, date('n')+1, date('t'), date('Y'));

//echo date('Y-m-d H:i:s', $_start) .' ---' . date('Y-m-d H:i:s', $_end);

$_day = date('j', $_start);
$_mon = date('n', $_start);
$_year = date('Y', $_start);    
*/




$json_errors = array(
    JSON_ERROR_NONE => 'No error has occurred',
    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
    JSON_ERROR_SYNTAX => 'Syntax error',
);

/*
$cwd = getcwd();
//putLog('log', array($cwd));
$last_view = (isset($_SESSION['last_view'])) ? $_SESSION['last_view'] : 0;
$check_instance = true;//($_end > $last_view);
$_SESSION['last_view'] = $_end;
$events = get_events($_start, $_end, $check_instance);
//putLog('events', $events);

$data = array();
$checks = array();

foreach ($events as $event){
    //if (in_array($event['id_event'], $checks)) continue;
    $checks[] = $event['id_event'];
    $data[] = array(
        'id' => $event['id_event'] . '-' . $event['start'],
        'allDay' => ($event['fullday'] == 1) ? true : false,
        'repeated' => ($event['repetition'] != REPEAT_NONE) ? true : false,
        'title' => $event['title'],
        'location' => $event['location_name'],
        'start' => $event['start'],
        'end' => $event['end'],
        'owner' => $event['id_user'],
        'editable' => (USERID!=$event['id_user']) ? false : true,
        //'url' => '../?mod=calendar&act=view&id='.$event['id_event'] . '&d='.$event['date_start']
    );
}
*/
$data = array();
$checks = array();

$query = query_service_request_by_all_status();
while($event = mysql_fetch_array($query)){
	if($event['status'] == "COMPLETED"){
		$color = "#419641";
	} else if ($event['status'] == "PENDING") {
		$color = "#CB2121";
	} else {
		$color = "#103BE5";
	}
	
	
	
	$end_date = date('Y-m-d', strtotime($event['end_loan']));
	$data[] = array(        
		'id' => $event['id_loan'],
		'allDay' => 0,
		'backgroundColor' => $color,
		'borderColor' => $color,
		'title' => $event['category_name'],
		'start' => date('Y-m-d H:i', strtotime($event['start_loan'])),        
		'editable' => ( USERID != $event['id_loan'] ) ? false : true,        
		'url' => "./?mod=service&sub=service&act=view_issue&id=".$event['id_loan'],
	);

}

//error_log($event['category_name'].mysql_error());
echo json_encode($data);

exit;
?>