<div style="margin-bottom: 30px"></div>
<div id="tab_calendar" class="tabset_content history">
     <div class="leftcol" style="width: 300px; text-align: left; padding-left: 5px"><h2 style="color: #000; display: inline">Calendar</h2></div>
     <!--
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=calendar">Facility Booking Form</a> | 
        <a href="./?mod=portal&sub=history&portal=calendar">Facility Booking History</a>
     </div>
     -->
    <br>
    <br>
    <div class="portal_history" id="calendar_history">

<?php

if (empty($_act)) $_act = 'view_month';
$path = 'calendar_' . $_act . '.php';
require ($path);
/*
switch($_act){
    'edit':
        require './calendar/calendar_edit.php';
        break;
    default:
        require './calendar/calendar_view_month.php';
}
*/
?>
  </div>
  &nbsp;
</div>

<?php
function calendar_save_request(){
    $_booked_date = convert_date($_POST['date_start'], 'Y-m-d H:i:s');
    $_times = !empty($_POST['times']) ? $_POST['times'] : array();
    $_idloc = !empty($_POST['id_location']) ? $_POST['id_location'] : 0;
    $saved = 0;
    // save / process request
    $ids = array();
    $event_title = mysql_escape_string($_POST['event_title']);
    $description = mysql_escape_string($_POST['description']);
    $date_start = convert_date($_POST['date_start'], 'Y-m-d');
    $date_finish = convert_date($_POST['date_finish'], 'Y-m-d');
    $time_start = $_POST['time_start'] . ':00';
    $time_finish = $_POST['time_finish'] . ':00';
    $fullday = (isset($_POST['fullday']) && ($_POST['fullday'] == 'yes')) ? 1 : 0;
    $repetition = $_POST['repetition'];
    $repeat_interval = isset($_POST['interval']) ?  $_POST['interval'] : 0;
    $repeat_period = isset($_POST['period']) ?  $_POST['period'] : 0;
    $repeat_until = isset($_POST['repeat_until']) ? $_POST['repeat_until'] : 0;
    $date_until = convert_date(@$_POST['date_until']);
    //print_r($_POST);
    return;
    if ($repetition == 2 || $repetition == 3 ){
        $repeat_option = implode(',', $_POST['repeat_option']);
        if ($repetition == 2) { // weekly, anticipate for other days than selected date
            $dt = time_Ymd($date_start);
            $dow = date('j', $dt);
            $ro = $_POST['repeat_option'];
            sort($ro);
            $i = 0;
            while ($ro[$i] < $dow) $i++;
            $dt = date_add_day($dt, $ro[$i]-$dow);
            $date_start = date('Y-m-d', $dt);
            $dt = time_Ymd($date_finish);
            $dt = date_add_day($dt, $ro[$i]-$dow);
            $date_finish = date('Y-m-d', $dt);
        }
    } else
        $repeat_option = 'null';
    if ($repeat_until == 0)
        $date_until = '9999-00-00';
    //print_r($_POST);
    $query = "INSERT INTO calendar_events(id_user, id_location, date_start, date_finish, time_start, time_finish, 
                fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, title, description, status)
                VALUES ('$userid', $_idloc, '$date_start', '$date_finish', '$time_start', '$time_finish', 
                '$fullday', '$repetition', '$repeat_interval', '$repeat_period', '$date_until', '$repeat_option', 
                '$event_title', '$description', 'BOOK')"; 
    mysql_query($query);
    //echo $query.mysql_error();
    if (mysql_affected_rows()>0){
        $saved++;
        $_id = mysql_insert_id();
    }
    if ($saved > 0){
        $submitted = true;     
              
        // sending email notification 
        //send_submit_calendar_request_notification($_id);
        ob_clean();
        if (@strpos(@$_SERVER['HTTP_REFERER'], 'portal') === false)
            header('Location: ./?mod=calendar&act=view&id=' . $_id);
        else
            header('Location: ./?mod=portal&portal=calendar&act=view&id=' . $_id);
        ob_end_flush();
        exit;
    } else
        $submitted = false;
        
	return $submitted;
}


?>
