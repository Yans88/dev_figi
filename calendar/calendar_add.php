<?php
include_once 'calendar/calendar_util.php';

function calendar_save_event(){
    $userid = USERID;
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


$timesheet = null;
if (isset($_POST['submitcode']) && $_POST['submitcode'] == 'submit'){
    calendar_save_event();
} 

$location_list = get_location_list();	
$repeat_until = 0;
$repeat_option = 0;
$weekdays = array(date('w'));

$_dt = 0;
$_d = isset($_GET['d']) ? $_GET['d'] : null;
if (!empty($_d)) $_dt = strtotime($_d);
if ($_dt <= 0) $_dt = time();
if ($_dt < time()) $_dt = time();

$date_start_str = date('j-M-Y',$_dt);
$date_finish_str = $date_start_str;

$sheets = create_timesheet();
$timesheet_start_option = null;
$timesheet_finish_option= null;
foreach ($sheets as $id_time => $sheet){
    $timesheet_start_option .= '<option>'.$sheet['time_start'].'</option>';
    $timesheet_finish_option .= '<option>'.$sheet['time_end'].'</option>';
}

?>
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
</style>
<div id="calendar_edit" class="calendar" >
     <form method="post" id="form_calendar">
     <input type="hidden" name="submitcode" value="">
     <input type="hidden" name="remove" value="">
     <table width="100%" class="itemlist" cellpadding=4 cellspacing=0>
      <tr>
        <th colspan=2><h3>Create New Event</h3></td>
      </tr>
      <tr class="normal">
        <td align="left" width=100>Title</td>
        <td align="left"><input type="text" size=55 id="event_title" name="event_title"></td>
      </tr>
      <tr class="alt">
        <td align="left">Date/Time</td>
        <td align="left">
          From: <input type="text" size=14 id="date_start" name="date_start" value="<?php echo $date_start_str?>" > 
         <input type="text" class="time" name="time_start" id="time_start">
         <select class="time" name="combo_start" id="combo_start"><?php echo $timesheet_start_option;?></select> &nbsp; 
          To: &nbsp; 
          <input type="text" size=14 id="date_finish" name="date_finish" value="<?php echo $date_start_str?>"  >
          <select class="time" name="time_finish" id="time_finish"><?php echo $timesheet_finish_option;?></select>
          <br/><input type="checkbox" id="fullday" name="fullday" value="yes"><label for="fullday">Full day event</label>
		  <script type="text/javascript">
                    var oneDay = 24*60*60*1000;
                    var dateFormat = "%e-%b-%Y";      
                    var today = new Date();
                    $('#date_start').AnyTime_picker({earliest: today, format: dateFormat});
                    var dateConv = new AnyTime.Converter({format:dateFormat});
                    //var fromDay = dateConv.parse($("#date_start").val()).getTime();
                    //var laterDay = new Date(fromDay);
                    $('#date_finish').AnyTime_picker({earliest: today, format: dateFormat});
		  </script>
        </td>
      </tr>
      <tr >
        <td align="left" valign="top">Repetition</td>
        <td align="left">
            <select id="repetition" name="repetition"><?php echo build_option($repetitions);?></select>
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
                    <input type="radio" name="repeat_option" value=0 <?php if ($repeat_option=='0') echo ' checked';?> >date of month
                    <input type="radio" name="repeat_option" value=1 <?php if ($repeat_option=='1') echo ' checked';?> >day of week
                </div>
            </div>
            <div class="leftcol">Repeat until:</div>
            <div class="rightcol">
            <ul >
                <li>
                <input type="radio" name="repeat_until" value=2 <?php if ($repeat_until==2) echo ' checked';?>>On 
                <input type="text" size=14 id="date_until" name="date_until" value="<?php echo $date_finish_str?>"  >
                </li>
                <!--
                <li><input type="radio" name="repeat_until" value=1 <?php if ($repeat_until==1) echo ' checked';?>>After <input type="text" value=2 name="occurrences" size=3> occurrences</li>
                -->
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
		<?php echo build_option($location_list)?>
		</select>
		</td>
      </tr>
      <tr class="normal" valign="top">
        <td align="left">Description</td>
        <td align="left"><textarea rows=4 cols=65 id="description" name="description"></textarea></td>
      </tr>
     <tr >
        <th colspan=2 align="right">
        <select class="time" name="combo_select" id="combo_select" style="display: none;" size=8><?php echo $timesheet_start_option;?></select>
        <button type="button" id="cancelbtn">Cancel</button>
        <button type="button" id="savebtn">Save Event</button>
        </th>
      </tr>
     </table>
     </form>
</div>
<script type="text/javascript" src="js/jquery.json.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript">
repetitions = '<?php echo implode(',', $repetitions);?>'.split(',');
var combo_shows = false;

if ($('#id_calendar').val()>0)	
    $('#id_calendar option').trigger("change");

fill_interval();
$('#date_start').trigger('change');

$('#repetition').change(function(e){
    if ($('#repetition :selected').val() > 0){
        var dateuntil = new Date(today+oneDay);
        $('#date_until').AnyTime_picker({earliest: dateuntil, format: dateFormat});

    }
});

$('#cancelbtn').click(function(e){
    location.href="./?mod=calendar";
});

$('#combo_edit').click(function(e){
    var pos = $(this).position();
    var off = $(this).offset();
    //$('#combo_edit').val(pos.top+','+pos.left+'. '+off.top+','+off.left);
    $('#combo_select').css('left',  pos.left);
    $('#combo_select').css('top',  pos.top+20);
    $('#combo_select').css('position',  'absolute');
    if (!combo_shows){
        $('#combo_select').show();
        combo_shows = !combo_shows;
    }
});

$('#combo_edit').keydown(function(e){
        if (combo_shows){
             $('#combo_select').hide();
            combo_shows = !combo_shows;
        }
});

$('body').click(
    function(e) {
        var o = e.target;
        if (combo_shows && (o.id != 'combo_edit') && (o.id != 'combo_select')){
             $('#combo_select').hide();
            combo_shows = !combo_shows;
        }
        //$('#combo_edit').val(o);
    }
);

$('document').click(
    function(e) {
        //$('#combo_select').hide();
    }
);

$('#combo_select').change(
    function(e) {
        if (combo_shows){
            var selected = $('#combo_select option:selected').val();
             $('#combo_edit').val(selected);
            $('#combo_select').hide();
            //combo_shows = !combo_shows;
        }
    }
);

setMinimumTime();
</script>
