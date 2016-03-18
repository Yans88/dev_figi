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
*/$query = query_service_request_by_statuses(PENDING, "1", "20");$a = mysql_fetch_array($query);foreach ($a as $event){$end_date = date('Y-m-d', strtotime($event['end_loan']));$data[] = array(        'id' => $event['id_loan'],        'allDay' => 1,        'repeated' => ($event['repetition'] != REPEAT_NONE) ? true : 1,        'title' => $event['category_name'],        'location' => $event['location_name'],        'start' => date('Y-m-d', strtotime($event['start_loan'])),        'end' => $end_date,        'owner' => $event['requester'],        'editable' => (USERID!=$event['id_loan']) ? false : true,        //'url' => '../?mod=calendar&act=view&id='.$event['id_event'] . '&d='.$event['date_start']    );}
echo json_encode($data);
exit;
?>