<?php

if (!defined('FIGIPASS')) {
    ob_clean();
    header('Location: ./');
    ob_end_flush();
    return;
}
include_once 'calendar/calendar_util.php';
$now = time();
$dts = isset($_GET['dts']) ? $_GET['dts'] : $now;
$dte = isset($_GET['dte']) ? $_GET['dte'] : $now;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_title = isset($_GET['title']) ? $_GET['title'] : null;
if (isset($_POST['title'])) $_title = $_POST['title'];

$repetition_codes = array('NONE', 'DAILY', 'WEEKLY', 'MONTHLY','YEARLY');
function calendar_save_event($_id = 0){
    global $repetition_codes, $delete_commands, $dts, $dte;
    $userid = USERID;
    //$_booked_date = convert_date($_POST['date_start'], 'Y-m-d H:i:s');
    //$_times = !empty($_POST['times']) ? $_POST['times'] : array();
    $_idloc = !empty($_POST['id_location']) ? $_POST['id_location'] : 0;
    $saved = 0;
    // save / process request
    //print_r($_POST);
    $title = mysql_real_escape_string($_POST['event_title']);
    $description = mysql_real_escape_string($_POST['description']);
    $fullday = (isset($_POST['fullday']) && ($_POST['fullday'] == 'yes')) ? 1 : 0;
    
    $date_start = !empty($_POST['date_start']) ? $_POST['date_start']: date('Y-m-d');
    $date_finish = !empty($_POST['date_finish']) ? $_POST['date_finish']: date('Y-m-d');
    $time_start = !empty($_POST['time_start']) ? $_POST['time_start'] . ':00' : '00:00:00';
    $time_finish = !empty($_POST['time_finish']) ? $_POST['time_finish'] . ':00' : '01:00:00';
    
    //echo $date_start . ' ' .$time_start;
    $dt_start = strtotime($date_start . ' ' .$time_start);
    $dt_end = strtotime($date_finish. ' ' .$time_finish);
    $duration = round(($dt_end-$dt_start) / (24*60*60));
    $repetition = !empty($_POST['repetition']) ? $_POST['repetition'] : 'NONE';
    $interval = isset($_POST['interval']) ?  $_POST['interval'] : 1;
    //$repeat_period = isset($_POST['period']) ?  $_POST['period'] : 0;
    $repeat_until = isset($_POST['repeat_until']) ? $_POST['repeat_until'] : null;
    if ($repetition == 'WEEKLY'){
        $wd_start = "'" . implode(',', $_POST['repeat_option']) . "'";
    } else
        $wd_start = 'null';
    if ($repeat_until == 0) $dt_last = 'null'; // no end
    else {
        $date_until = isset($_POST['date_until']) ? $_POST['date_until'] : date('Y-m-d', $dt_end);
        if ($fullday>0) $dt_last = strtotime("$date_until 23:59:59");
        else $dt_last = strtotime("$date_until ".date('H:i', $dt_end).":59");
    }
    
    //echo "-- $repetition $dt_last --".$_POST['repetition'];
    $status = 0;
    $data = compact('userid', '_idloc', 'dt_start', 'dt_end', 'duration', 'fullday', 'status', 'repetition', 
                    'interval', 'dt_last', 'wd_start', 'title', 'description', '_id');
    if ($_id > 0){
        //print_r($_POST);
        $event = get_event_info($_id);
        $savepart = !empty($_POST['savepart']) ? $_POST['savepart'] : 'all-of-me';
        if ($savepart == 'only-me'){
            // create new event (and instanciate), remove old instance 
            $_id = save_new_event($data);
            delete_instance($event['id_event'], $dts);
            $saved++;
        } else 
        if ($savepart == 'me-follow'){
            // create new event (and instanciate), remove old instance and the rest, set the original dt_last before new dt_start
            $_id = save_new_event($data);
            delete_instance($event['id_event'], $dts, -1); // start at selected event
            $dt_instance = get_latest_instance($event['id_event']);
            $query = "UPDATE calendar_events SET dt_last = $dt_start-86400, mdate = now(), dt_instance = $dt_instance  
                        WHERE id_event=$event[id_event] AND (dt_instance IS NULL OR dt_instance > $dt_instance)"; 
            mysql_query($query);
            $saved++;
        } else 
        if ($savepart == 'all-of-me'){ // all-of-me
            $dt_start = $event['dt_start'];
            $dt_end = $event['dt_end'];
            $data = compact('userid', '_idloc', 'dt_start', 'dt_end', 'duration', 'fullday', 'status', 'repetition', 
                            'interval', 'dt_last', 'wd_start', 'title', 'description', '_id');

            $query = "UPDATE calendar_events SET 
                        id_location = $_idloc, dt_start = $dt_start, dt_end = $dt_end, duration = $duration,
                        fullday = $fullday, dt_last = $dt_last, repetition = '$repetition', `interval` = $interval, 
                        wd_start = $wd_start, title = '$title', description = '$description', 
                        mdate = now() 
                        WHERE id_event=$_id"; 
            mysql_query($query);
            if (mysql_affected_rows()>0){
                $need_instance_changes = ($dt_start != $event['dt_start']) || ($dt_end != $event['dt_end']) || ($dt_last != $event['dt_last'])
                                        || ($repetition != $event['repetition']) || ($interval != $event['interval']) 
                                        || ($wd_start != $event['wd_start']);
                
                //if ($need_instance_changes){
                    delete_instance($event['id_event']); // delete old instances
                    $dt_instance = 'null';
                    $instance_start = $event['dt_start'];
                    $instance_end = date_add_day($instance_start, 31);
                    $instance_start = $dt_start;
                //}
                //$instance_start = get_latest_instance($_id);
                $query = "UPDATE calendar_events SET dt_instance = $dt_instance WHERE id_event=$event[id_event]"; 
                mysql_query($query);
                generate_instances($_id, $instance_start, $instance_end, true);    
            }
            $saved++;
        }
    } 
    else { // new event
        $_id = save_new_event($data);
    }
    //echo $query.mysql_error();
    if ($saved > 0){
        $submitted = true;     
              
        // sending email notification 
        //send_submit_calendar_request_notification($_id);
    } else
        $submitted = false;
        
	return $submitted;
}


$timesheet = null;
if (isset($_POST['submitcode']) && $_POST['submitcode'] == 'submit'){
    //print_r($_POST);

    $msg = ($_id == 0) ? 'New event created successfully' : 'Event updated successfully';
    calendar_save_event($_id);
    
    $dttm = strtotime(@$_POST['date_start']);
    if ($from_portal)
        $url = './?mod=portal&portal=calendar&d='.date('Y-n-j', $dttm);
    else
        $url = './?mod=calendar&d='.date('Y-n-j', $dttm);
    echo <<<SCRIPT
    <script>
        alert("$msg");
        location.href = "$url";
    </script>
SCRIPT;
    return;
    /*
    ob_clean();
    header('Location: ./?mod=calendar&d='.date('Y-n-j', $dttm));
    ob_end_flush();
    exit;
    */
} 

if (isset($_GET['d']))
    if (($ts = strtotime($_GET['d'])) !== false){
        $dts = $ts;
        if ($dte == $now) $dte = $ts;
    }
//echo date('YmdHis', $dte);
$_t = date('His', $dts);
if ($_t == '000000'){ // no time set, default to be now
    
        
    if (date('i') <= 30){        
        $dts = mktime(date('G'), 30, date('s'), date('n', $dts), date('j', $dts), date('Y', $dts));
        if ($dte == $dts) $dte = date_add_sec($dts, 60*60); // add an hour
        else 
            $dte = mktime(date('G'), 30+60, date('s'), date('n', $dte), date('j', $dte), date('Y', $dte));
    } else {
        $dts = mktime(date('G'), 60, date('s'), date('n', $dts), date('j', $dts), date('Y', $dts));
        if ($dte == $dts) $dte = date_add_sec($dts, 60*60); // add an hour
        else 
            $dte = mktime(date('G'), 30+60, date('s'), date('n', $dte), date('j', $dte), date('Y', $dte));
        //$time_start_str = date('H:00');
        //$time_finish_str = date('H:00', $dte);
    }
    $time_start_str = date('H:i', $dts);
    $time_finish_str = date('H:i', $dte);
} else {
}

$event['time_start'] = $dts;
$event['time_finish'] = $dte;

$location_list = get_location_list();	
$repeat_until = 0;
$repeat_option = '';
$weekdays = array(date('w'));


if ($_id > 0){ // edit an event, get stored info
    $event = get_event_info($_id);
    //print_r($event);
    if (!empty($event)){
        //$dts = $event['dt_start'];
        //$dte = $event['dt_end'];
        $repeat_until = !empty($event['dt_last']) ? 1 : 0;
        if ($repeat_until == 0) 
            $repeat_until_str =  '';
        else
            $repeat_until_str = date('d-M-Y', $event['dt_last']);
        $weekdays = explode(',', $event['wd_start']);
        $id_location = $event['id_location'];
        $selected_repetition = $event['repetition'];
        $date_start_str = date('d-M-Y', $dts);
        $date_finish_str= date('d-M-Y', $dte);
    }
} 
else {
    $event['wd_start'] = 0;
    $event['interval'] = 0;
    $event['title'] = $_title;
    $event['description'] = null;
    $event['fullday'] = 1;
    $id_location = 0;
    $selected_repetition = REPEAT_NONE;
    $repeat_until = 0;
    $repeat_option = null;
    $date_start_str = date('d-M-Y', $dts);
    $date_finish_str= date('d-M-Y', $dte);
    $repeat_until_str = $date_finish_str;
}
$date_start_str = date('d-M-Y', $dts);
$date_finish_str= date('d-M-Y', $dte);
$time_start_str = date('H:i', $dts);
$time_finish_str = date('H:i', $dte);
//echo "$dts => $date_start_str , $dte => $date_finish_str, " .date('YmdHis', $dts) ." " .date('YmdHis', $dte);

$sheets = create_timesheet();
$timesheet_start_option = null;
$timesheet_finish_option= null;
$next_selected = false;

foreach ($sheets as $id_time => $sheet){
    if ($_t == $sheet['time_start']){
        $timesheet_start_option .= '<option selected>'.$sheet['time_start'].'</option>';
        $next_selected = true;
    } else {
        $timesheet_start_option .= '<option>'.$sheet['time_start'].'</option>';
    }
    if ($next_selected){
        $timesheet_finish_option .= '<option selected>'.$sheet['time_end'].'</option>';
        $next_selected = false;
    } else
        $timesheet_finish_option .= '<option>'.$sheet['time_end'].'</option>';
}

$caption = ($_id > 0) ? 'Edit Event' : 'Create New Event';
?>
<link rel='stylesheet' type='text/css' href='./style/default/jquery-ui-1.8.13.custom.css'/>	
<link rel='stylesheet' type='text/css' href='./style/default/application.css'/>
<script type='text/javascript' src='./js/jquery/jquery-ui-1.8.13.custom.min.js'></script>		

<style type="text/css">
  #date_start { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #date_finish{ background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #date_until{ background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
.time_edit{ width: 50px; }
select.combo_time{ display: none; min-width: 80px; }
</style>
<div id="calendar_edit" class="calendar1" >
     <form method="post" id="form_calendar">
     <input type="hidden" name="submitcode" value="">
     <input type="hidden" name="remove" value="">
     <input type="hidden" name="savepart" id="savepart" value="">
     <table width="100%" class="itemlist" cellpadding=4 cellspacing=0 >
      <tr>
        <th colspan=2><h3><?php echo $caption?></h3></td>
      </tr>
      <tr class="normal">
        <td align="left" width=80>Title</td>
        <td align="left"><input type="text" size=55 id="event_title" name="event_title" value="<?php echo $event['title']?>"></td>
      </tr>
      <tr class="alt">
        <td align="left">Date/Time</td>
        <td align="left">
          From: <input type="text" size=14 class="date_combo"  id="date_start" name="date_start" value="<?php echo $date_start_str?>" > 
          <input type="text" class="time_edit" name="time_start" id="time_start" value="<?php echo $time_start_str?>">
          <select class="combo_time" name="combo_start" id="combo_start" size=7><?php echo $timesheet_start_option;?></select> &nbsp; 
          To: &nbsp; 
          <input type="text" size=14 class="date_combo" id="date_finish" name="date_finish" value="<?php echo $date_finish_str?>"  >
          <input type="text" class="time_edit" name="time_finish" id="time_finish" value="<?php echo $time_finish_str?>">
          <select class="combo_time" name="combo_finish" id="combo_finish" size=7><?php echo $timesheet_finish_option;?></select> &nbsp; 
          <input type="checkbox" id="fullday" name="fullday" value="yes"><label for="fullday">Full day event</label>
		  <script type="text/javascript">
            var oneDay = 24*60*60*1000;
            var dateFormat = "%d-%b-%Y";      
            var today = new Date();
            $('#date_start').AnyTime_picker({format: dateFormat});
            var dateConv = new AnyTime.Converter({format:dateFormat});
            //var fromDay = dateConv.parse($("#date_start").val()).getTime();
            //var laterDay = new Date(fromDay);
            $('#date_finish').AnyTime_picker({format: dateFormat});
		  </script>
        </td>
      </tr>
      <tr >
        <td align="left" valign="top">Repetition</td>
        <td align="left">
            <select id="repetition" name="repetition"><?php echo build_option($repetitions, $selected_repetition);?></select>
            <div id="repetition_option" style="display: none">
            <div id="repetition_interval" >
            <div class="leftcol">Repeat every:</div>
            <div class="rightcol"><select id="interval" name="interval" class="time"></select> <span id="interval_name" ></span></div>
            </div>
            <div id="repeat_option_weekly" style="display: none">
                <div class="leftcol">Repeat on: </div>
                <div class="rightcol">
                <input type="checkbox" name="repeat_option[]" value=0 <?php if (in_array(0, $weekdays)) echo ' checked';?>>S &nbsp;
                <input type="checkbox" name="repeat_option[]" value=1 <?php if (in_array(1, $weekdays)) echo ' checked';?>>M &nbsp;
                <input type="checkbox" name="repeat_option[]" value=2 <?php if (in_array(2, $weekdays)) echo ' checked';?>>T &nbsp;
                <input type="checkbox" name="repeat_option[]" value=3 <?php if (in_array(3, $weekdays)) echo ' checked';?>>W &nbsp;
                <input type="checkbox" name="repeat_option[]" value=4 <?php if (in_array(4, $weekdays)) echo ' checked';?>>T &nbsp;
                <input type="checkbox" name="repeat_option[]" value=5 <?php if (in_array(5, $weekdays)) echo ' checked';?>>F &nbsp;
                <input type="checkbox" name="repeat_option[]" value=6 <?php if (in_array(6, $weekdays)) echo ' checked';?>>S &nbsp;
                </div>
            </div>
            <div id="repeat_option_monthly" style="display: none">
                <div class="leftcol">Repeat per:</div>
                <div class="rightcol">
                    <input type="radio" name="repeat_option[]" value=0 <?php if ($repeat_option=='0') echo ' checked';?> >date of month
                    <input type="radio" name="repeat_option[]" value=1 <?php if ($repeat_option=='1') echo ' checked';?> >day of week
                </div>
            </div>
            <div class="leftcol">Repeat until:</div>
            <div class="rightcol">
            <ul >
                <li>
                <input type="radio" name="repeat_until" value=1 <?php if ($repeat_until==1) echo ' checked';?>>On 
                <input type="text" size=14 id="date_until" name="date_until" value="<?php echo $repeat_until_str?>"  >
                <script>
                    var dateuntil = new Date(today+oneDay);
                    $('#date_until').AnyTime_picker({format: dateFormat});
                </script>
                </li>
                <li><input type="radio" name="repeat_until" value=0 <?php if ($repeat_until==0) echo ' checked';?>>No ends</li>
            </ul>
            </div>
            </div>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Location</td>
        <td align="left">
		<select id="id_location" name="id_location">
		<?php echo build_option($location_list, $id_location)?>
		</select>
		</td>
      </tr>
      <tr class="normal" valign="top">
        <td align="left">Description</td>
        <td align="left"><textarea rows=4 cols=65 id="description" name="description"><?php echo $event['description']?></textarea></td>
      </tr>
     <tr >
        <th colspan=2 align="right">
        <button type="button" id="cancelbtn">Cancel</button>
        <button type="reset" id="resetbtn">Reset</button>
<?php
    if ($_id > 0)
        echo '<button type="reset" id="willdelbtn">Delete</button> ';
?>
        <button type="button" id="savebtn" disabled>Save Event</button>
        </th>
      </tr>
     </table>
     </form>
    <div id="deleteDialog" class="dialog ui-helper-hidden">
        <form>
        <div>Write some reason why you want to delete this event?</div>
        <div>
            <textarea name='reason' id='reason' cols=42 rows=4></textarea>
            <br/>&nbsp;
        </div>
        <div>Which event do you want to delete, all event, this, or this and following?</div>
        <div style="text-align: center">
            <button type="button" button class="deleteme" id="only-me">This Event Only</button><br/>
            <button type="button" class="deleteme" id="me-follow">This Event and Following</button><br/>
            <button type="button" class="deleteme" id="all-of-me">All Event in Series</button>
        </div>
        </form>
    </div>
    <div id="saveDialog" class="dialog ui-helper-hidden">
        <form>
        <div>Changes will be applied to this event only, this and following or all event?</div>
        <div style="text-align: center">
            <button type="button" button class="saveme" id="only-me">This Event Only</button><br/>
            <button type="button" class="saveme" id="me-follow">This Event and Following</button><br/>
            <button type="button" class="saveme" id="all-of-me">All Event in Series</button>
        </div>
        </form>
    </div>
</div>

 &nbsp; <br/>
<script type="text/javascript" src="js/jquery.json.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript">
repetitions = '<?php echo implode(',', $repetitions);?>'.split(',');
interval = "<?php echo $event['interval']?>";
tm_start = "<?php echo date('H:i:s', $dts)?>";
tm_finish = "<?php echo date('H:i:s', $dte)?>";
var id_event = "<?php echo $_id; ?>";
var combo_shows = false;
var active_combo = '';
var key_escape = 27;
var key_tab = 7;
var key_down = 40;

fill_interval();
$('#date_start').trigger('change');
$('#repetition').trigger("change");

$('#interval option').each(function(){
    if ($(this).val() == interval)
        $(this).attr('selected', 'selected');
});
    
$('#cancelbtn').click(function(e){
    if (location.href.search(/portal/)>-1)
        location.href="./?mod=portal&portal=calendar";
    else
        location.href="./?mod=calendar";
});


$('.time_edit').click(function(e){
    var pos = $(this).position();
    var off = $(this).offset();
    var id = e.target.id.substring(5);
    $('#combo_'+id).css('left',  pos.left);
    $('#combo_'+id).css('top',  pos.top+20);
    $('#combo_'+id).css('position',  'absolute');
    if (!combo_shows){
        active_combo = id;
        $('#combo_'+id).show();
        $('#combo_'+id).trigger('focus');
        combo_shows = !combo_shows;
    } else
    if (active_combo!=id){
        $('#combo_'+active_combo).hide();
        $('#combo_'+id).show();
        $('#combo_'+id).trigger('focus');
        active_combo = id;
        //combo_shows = !combo_shows;
    }
});

$('.time_edit').keydown(function(e){
    var id = e.target.id.substring(5);
    var keynum;
    if(window.event) // IE8 and earlier
	{
        keynum = e.keyCode;
	}
    else if(e.which) // IE9/Firefox/Chrome/Opera/Safari
	{
        keynum = e.which;
	}
    var keychar = String.fromCharCode(keynum);
    if (combo_shows){
        if (keynum == key_escape){            
            $('#combo_'+id).hide();
            combo_shows = !combo_shows;
        }
        else if ((keynum == key_down)||(keynum == key_tab)){
            $('#combo_'+id).trigger('focusin');
            //alert('focus')
        }
    }
});

$('.time_edit').blur(function(e){
    var id = e.target.id.substring(5);
    if (combo_shows){
        //$('#combo_'+id).trigger('change');
        //$('#combo_'+id).hide();
        //combo_shows = !combo_shows;
    }
});

$('.combo_time').keydown(
    function(e) {
        var id = e.target.id.substring(6);
        if (e.keyCode == key_escape){
            $('#combo_'+id).trigger('change');
        }
    /*
        var o = e.target;
        if (combo_shows && (o.id != 'combo_edit') && (o.id != 'combo_select')){
             $('#combo_select').hide();
            combo_shows = !combo_shows;
        }
        //$('#combo_edit').val(o);
        */
    }
);

$('.combo_time').focusin(
    function(e) {
        var id = e.target.id.substring(6);
        var t = $('#time_'+id).val();
        var options = $('#combo_'+id+' option');
        for(var i=0; i<options.length; i++)
            options[i].selected = false;
        for(var i=0; i<options.length; i++){
            if ((options[i].value == t)||(options[i].value <= t && options[i+1].value >= t)){
                options[i].selected = true;
                //$('#combo_'+id).scrollTop(400);
                break;
            }
        }
    }
);

$('.date_combo').change(function(e){
    time_check();
});

$('.combo_time').change(
    function(e) {
        var id = e.target.id.substring(6);
        if (combo_shows){
            var selected = $('#combo_'+id+' option:selected').val();
            if (selected!=''){
                $('#time_'+id).val(selected);
            }
            time_check();
        }
    }
);

// check duration
function time_check(){
    //return;
    var oldval = $('#date_finish').val();
    var stm = dateConv.parse($("#date_start").val()).getTime();
    var sdt = new Date(stm);
    var etm = dateConv.parse($("#date_finish").val()).getTime();
    var edt = new Date(etm);
    if (!$('#fullday').attr('checked')){
        var col = $("#time_start").val().split(':');
        sdt.setHours(col[0]);
        sdt.setMinutes(col[1]);
        col = $("#time_finish").val().split(':');
        edt.setHours(col[0]);
        edt.setMinutes(col[1]);
    }
    //alert(sdt.getTime()+"\n"+edt.getTime())
    if (edt.getTime()-sdt.getTime() < 0){
        alert("You can not create event that ends before it starts.");
        $('#savebtn').attr('disabled', 'disabled');
        //return;
    } else
        $('#savebtn').removeAttr('disabled');
    
} ;

$('.combo_time').click(
    function(e) {
        var id = e.target.parentNode.id.substring(6);
        if (combo_shows){
            var selected = $('#combo_'+id+' option:selected').val();
            if (selected!=''){
                $('#time_'+id).val(selected);
                $(this).hide();
                combo_shows = !combo_shows;
            }
        }
    }
);
var fullday = "<?php echo ($event['fullday']==1) ? 1 : 0;?>";
if (fullday == 1){
	$('#fullday').trigger("click");
	$('#fullday').trigger("change");
}

$('.time_edit').trigger('change');

time_check();

var _recur = <?php echo (!empty($event['repetition']) && $event['repetition']!='NONE') ? 'true' : 'false'?>;
if (id_event > 0 && _recur){
    $('#willdelbtn').click(function (e){
        $('#deleteDialog').dialog({
            modal: true,
            title: 'Delete Confirmation',
            //buttons: buttons,
            width: 450
        });

    });
    $('.deleteme').click(function(e){
        var id = id_event<?php if (isset($_GET['dts'])) echo "+'-$_GET[dts]'";?>;
        var url = "calendar/delete-event.php?id="+id+"&opt="+e.target.id+"&reason="+$('#reason').val();
        var reason = $('#reason').val();
        if (reason.length > 1){
        $('#deleteDialog').dialog('close');
            $.post('calendar/event_delete.php', {id: id, reason: reason, opt: e.target.id, userid: "<?php echo USERID?>", submitcode: "remove"}, function(data){
                if (data == 'OK'){
                    alert('Selected event has been deleted!');
                    location.href = './?mod=calendar';
                } else
                    alert('Fail to delete selected event!');
            });
        } else
            alert('Please provide reason of deletion of the event to proceed');
    });

}

$('#savebtn').click(function (e){
    var sd = new Date(dateConv.parse($("#date_start").val()).getTime());
    var dd = new Date(dateConv.parse($("#date_finish").val()).getTime());
    if (!$('#fullday').attr('checked')){
        var sh = $('#time_start').val();
        var hm = sh.split(':');
        sd.setHours(hm[0]);
        sd.setMinutes(hm[1]);
        var dh = $('#time_finish').val();
        hm = dh.split(':');
        dd.setHours(hm[0]);
        dd.setMinutes(hm[1]);
    } else {
        sd.setHours(0);
        sd.setMinutes(0);
        dd.setHours(23);
        dd.setMinutes(59);
    }
    if (dd-sd < 1){
        alert("It is not allowed create backdate event");
        return false;
    }
    
	if ($('#event_title').val() == ''){
		alert("Write the event title!");
		return false;
	}
    var saveme = false;
    if (id_event > 0){
        $('#saveDialog').dialog({
            modal: true,
            title: 'Save option',
            width: 450
        });
        $('.saveme').click(function(e){
            $('#savepart').val(e.target.id);
            saveme_please();
        });
    } else {
        saveme = confirm("Do you want to confirm save event?\n");
        if (saveme) saveme_please();
    }
});

function saveme_please(){
    var frm = document.getElementById("form_calendar");
    frm.submitcode.value = "submit" ;
    frm.submit();
}
</script>
