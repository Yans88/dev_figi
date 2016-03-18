<?php

ob_start();
require '../util.php';
require '../common.php';
require '../authcheck.php';
require 'facility_util.php';

$_start = !empty($_GET['start']) ? $_GET['start'] : mktime(0, 0, 0, date('n'), 1, date('Y'));
$_end = !empty($_GET['end']) ? $_GET['end'] : mktime(23, 59, 59, date('n')+1, date('t'), date('Y'));
$_facility = !empty($_GET['id_facility']) ? $_GET['id_facility'] : 0;

//echo date('Y-m-d H:i:s', $_start) .' ---' . date('Y-m-d H:i:s', $_end);

$_day = date('j', $_start);
$_mon = date('n', $_start);
$_year = date('Y', $_start);    



function booking_save($data)
{
    global $_start, $_end;
    $result = false;
    //error_log(serialize($data));
    $userid = USERID;
    $dt_start = $data['start_xtime'];
    $dt_end = $data['finish_xtime'];
    $dt_last = $dt_end;
    $duration = ($dt_end - $dt_start) / (24 * 60 * 60);
    $interval = 1;
    $wd_start = 0;
    $repetition = 'NONE';
    $title = mysql_real_escape_string($data['title']);
    $_facility = $data['id_facility'];
    $_id = !empty($data['id_book']) ? $data['id_book'] : 0;
    $query = null;
	$fullday = ($data['allDay']) ? 1 : 0;
    if ($_id == 0)
        $query = "INSERT INTO facility_book(id_user, id_facility, dt_start, dt_end, fullday, 
                    repetition, `interval`, dt_last, wd_start, purpose, status, book_date)
                    VALUES ($userid, $_facility, $dt_start, $dt_end, $fullday, '$repetition', $interval, 
                    $dt_last, 0, '$title', 'BOOK', unix_timestamp())"; 
    else if ($_id > 0){
        // for update possibilities: move & resize -> change date and duration
        // must create a new instance as non-recurring event for recurring event
        $event = get_booking_info($_id);
        if ($event['repetition'] != 'NONE'){
            $query = "UPDATE facility_book_instances SET 
                        start = '$dt_start', end = '$dt_end' 
                        WHERE id_book=$_id && start = $data[cur_date]";  
            
            
            
        } else {        
            $query = "UPDATE facility_book SET 
                        dt_start = '$dt_start', dt_end = '$dt_end', dt_last = '$dt_last', 
                        purpose = '$title',  mdate = now() 
                        WHERE id_book=$_id";  
            /*
            $query = "UPDATE facility_book_instances SET 
                        start = '$dt_start', end = '$dt_end' 
                        WHERE id_book=$_id && start = $data[cur_date]";  
            */
            delete_booking_instance($_id);
        }
    }
    if ($query != null){
    
        mysql_query($query);
        $result = mysql_affected_rows() > 0;
        //error_log($query.mysql_error().mysql_affected_rows());
        if ($result && $_id == 0){
            $_id = mysql_insert_id();
            //error_log('ID: '.$_id. ' send notification');
            send_submit_facility_request_notification($_id);
        }
        
        $num_of_days = 30 * 20;
        $instance_start = $dt_start;
        $instance_end = date_add_day($instance_start, $num_of_days);
        generate_booking_instances($_id, $instance_start, $instance_end, true);
        //generate_booking_instances($_id);    
        
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
    
    return delete_book($id, $seldate, $userid, $remark, $delpart);
}


$json_errors = array(
    JSON_ERROR_NONE => 'No error has occurred',
    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
    JSON_ERROR_SYNTAX => 'Syntax error',
);

$buffer = null;

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
        //$data['id_facility'] = $_facility;
        
        
        if ($method == 'POST') { //new event
			if ($data['allDay']){
				$facility = get_facility($data['id_facility']);
				// assume a fullday book
				$dte = date('Y-m-d ', $etm).$facility['time_end']; 
				$dts = date('Y-m-d ', $stm).$facility['time_start']; 
				$data['finish_xtime'] = strtotime($dte);
				$data['start_xtime'] = strtotime($dts);
			}
        	//error_log("dts: $dts [$data[start_xtime]], dte: $dte [$data[finish_xtime]]");
            booking_save($data);
        } else if ($method == 'PUT') { //update event
            list($id_book, $cur_date) = explode('-', $data['id']);
            $data['id_book'] = $id_book;
            $data['cur_date'] = $cur_date;
            $i = 0;
            while (isset($data[$i])){ // check whether indexed data exists 
                if ($data[$i]['id']==$data['id']){
                    $data['original'] = $data[$i];
                    break;
                }
                $i++;
            }
            booking_save($data);
        } 
    }
} else
if ($method == 'DELETE'){
    $path = $_SERVER['PATH_INFO'];
    if (preg_match('/\/(\d+)-(\d+)/', $path, $matches)){
        $id = $matches[1];
        $dt = $matches[2];
        event_del($id, $dt);
    }
} 

$cwd = getcwd();
$last_view = (isset($_SESSION['last_view'])) ? $_SESSION['last_view'] : 0;
$check_instance = true;//($_end > $last_view);
$_SESSION['last_view'] = $_end;
$events = get_bookings($_start, $_end, $_facility, $check_instance);
$data = array();

foreach ($events as $event){
	$editable = false;
	if (USERGROUP == GRPADM) 
		$editable  = (($event['id_group'] == GRPTEA) || ($event['id_group'] == GRPHOD));
	if (!empty($event['purpose_instance']) && ($event['purpose_instance']!=$event['purpose'])) $event['purpose'] = $event['purpose_instance'];
	if (!empty($event['remark_instance']) && ($event['remark_instance']!=$event['remark'])) $event['remark'] = $event['remark_instance'];
    $data[] = array(
        'id' => $event['id_book'] . '-' . $event['start'],
        'allDay' => ($event['fullday'] == 1) ? true : false,
        'repeated' => ($event['repetition'] != REPEAT_NONE) ? true : false,
        'title' => $event['purpose'],//.date('-YmdHis',$event['start']),
        'location' => $event['location_name'],
        'start' => $event['start'],
        'end' => $event['end'],
        'owner' => $event['id_user'],
        'full_name' => $event['full_name'],
        'id_facility' => $event['id_facility'],
        'editable' => (USERID!=$event['id_user']) ? $editable : true,
        //'url' => '../?mod=calendar&act=view&id='.$event['id_event'] . '&d='.$event['date_start']
    );
}
ob_clean();
echo json_encode($data);
ob_end_flush();
exit;
?>
