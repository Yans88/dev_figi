<?php
//require '../util.php';
require '../common.php';
require '../authcheck.php';
require 'calendar_util.php';

$_start = !empty($_GET['start']) ? $_GET['start'] : mktime(0, 0, 0, date('n'), 1, date('Y'));
$_end = !empty($_GET['end']) ? $_GET['end'] : mktime(23, 59, 59, date('n')+1, date('t'), date('Y'));

//echo date('Y-m-d H:i:s', $_start) .' ---' . date('Y-m-d H:i:s', $_end);

$_day = date('j', $_start);
$_mon = date('n', $_start);
$_year = date('Y', $_start);    



function event_save($data)
{
    $result = false;
    
    $userid = USERID;
    $_idloc = 1;
    $dt_start = $data['start_xtime'];
    $dt_end = $data['finish_xtime'];
    $dt_last = $dt_end;
    $duration = ($dt_end - $dt_start) / (24 * 60 * 60);
    $interval = 1;
    $wd_start = 0;
    $repetition = 'NONE';
    $title = mysql_real_escape_string($data['title']);
    $description = null;
    $_id = !empty($data['id_event']) ? $data['id_event'] : 0;
    $query = null;
    if ($_id == 0)
        $query = "INSERT INTO calendar_events(id_user, id_location, dt_start, dt_end, 
                    repetition, `interval`, dt_last, wd_start, title, status)
                    VALUES ($userid, $_idloc, $dt_start, $dt_end, '$repetition', $interval, 
                    $dt_last, 0, '$title', 0)"; 
    else if ($_id > 0){
        // for update possibilities: move & resize -> change date and duration
        // must create a new instance as non-recurring event for recurring event
        $event = get_event_info($_id);
        if ($event['repetition'] > 0){
            $query = "UPDATE calendar_event_instances SET 
                        start = '$dt_start', end = '$dt_end' 
                        WHERE id_event=$_id && start = $data[cur_date]";  
            
            
            
        } else {        
            $query = "UPDATE calendar_events SET 
                        dt_start = '$dt_start', dt_end = '$dt_end', dt_last = '$dt_last', 
                        title = '$title',  mdate = now() 
                        WHERE id_event=$_id";  
            $query = "UPDATE calendar_event_instances SET 
                        start = '$dt_start', end = '$dt_end' 
                        WHERE id_event=$_id && start = $data[cur_date]";  
        }
    }
    if ($query != null){
    
        mysql_query($query);
        $result = mysql_affected_rows() > 0;
        if ($result && $_id == 0)
            $_id = mysql_insert_id();
        
        putLog('query', array($query, mysql_error(), $_id));
        generate_instances($_id);    
        
    }
    
    
    return $result;
}

function event_del($id, $cur_date)
{   /*
    $query = "DELETE FROM calendar_events WHERE id_event = $id";
    mysql_query($query);
    return mysql_affected_rows() > 0;
    */
    $remark = 'delete-event';
    $userid = USERID;
    $delpart = 1;
    $seldate = date('Y-m-d', $cur_date);
    
    return delete_event($id, $seldate, $userid, $remark, $delpart);
}


$json_errors = array(
    JSON_ERROR_NONE => 'No error has occurred',
    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
    JSON_ERROR_SYNTAX => 'Syntax error',
);

$buffer = null;
//putLog('server', $_SERVER);
putLog('get', $_GET);

$method = $_SERVER['REQUEST_METHOD'];
if ( ($method == 'PUT') || ($method == 'POST') ){
    $putdata = fopen("php://input", "r");
    while ($data = fread($putdata, 1024))
      $buffer .= $data;
    fclose($putdata);
    
    if (!empty($buffer)){
        $data = json_decode($buffer, true);
        $stm = strtotime($data['start']);
        $data['start_xtime'] = $stm;
        $data['start_time'] = date('Y-m-d H:i:s', $stm);
        if (empty($data['end'])) $data['end'] = $data['start'];
        $etm = strtotime($data['end']);
        $data['finish_xtime'] = $etm;
        $data['finish_time'] = date('Y-m-d H:i:s', $etm);
        
        
        if ($method == 'POST') { //new event
            putLog('new event', $data);
            event_save($data);
        } else if ($method == 'PUT') { //update event
            list($id_event, $cur_date) = explode('-', $data['id']);
            $data['id_event'] = $id_event;
            $data['cur_date'] = $cur_date;
            $i = 0;
            while (isset($data[$i])){ // check whether indexed data exists 
                if ($data[$i]['id']==$data['id']){
                    $data['original'] = $data[$i];
                    break;
                }
                $i++;
            }
            putLog('update event', $data);
            event_save($data);
        } 
    }
} else
if ($method == 'DELETE'){
    $path = $_SERVER['PATH_INFO'];
    if (preg_match('/\/(\d+)-(\d+)/', $path, $matches)){
        $id = $matches[1];
        $dt = $matches[2];
        putLog('delete id', $matches);
        event_del($id, $dt);
    }
} 

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

echo json_encode($data);
exit;
?>