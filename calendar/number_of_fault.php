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

$dept = (!SUPERADMIN) ? USERDEPT : 0;
$query = total_fault_per_month_by_status('NOTIFIED', $dept);
while($event = mysql_fetch_array($query)){


	$data[] = array(        
		'id' => date('Y-m-d', strtotime($event['report_date'])),
		'title' => 'Total today: '.$event['total_per_day'],
		'allDay' => 1,
		'start' => date('Y-m-d', strtotime($event['report_date']))
		/*'editable' => ( USERID != $event['id_loan'] ) ? false : true*/
		//'url' => '../?mod=calendar&act=view&id='.$event['id_event'] . '&d='.$event['date_start']    
	);

}

error_log($query.mysql_error());
echo json_encode($data);

exit;
?>