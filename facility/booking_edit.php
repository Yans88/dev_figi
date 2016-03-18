<?php

$_id = isset($_GET['id']) ? $_GET['id'] : 0;

function facility_save_request(){
    $userid = USERID;
    $_booked_date = convert_date($_POST['date_start'], 'Y-m-d H:i:s');
    $_times = !empty($_POST['times']) ? $_POST['times'] : array();
    $_id = !empty($_POST['id_facility']) ? $_POST['id_facility'] : array();
    $saved = 0;
	//$id = 0;
    if (isset($_POST['remove']) && ($_POST['remove'] > 0)){ 
        // remove request
        $query = "DELETE FROM facility_book WHERE id_book = '$_POST[remove]'";
        mysql_query($query);
        $query = "DELETE FROM facility_book_datetime WHERE id_book = '$_POST[remove]'";
        mysql_query($query);
        
        return;
    }  else 
    { // save / process request
		$ids = array();
		$purpose = mysql_escape_string($_POST['purpose']);
		$remark = mysql_escape_string($_POST['remark']);
        $date_start = convert_date($_POST['date_start'], 'Y-m-d');
        $date_finish = convert_date($_POST['date_finish'], 'Y-m-d');
        $time_start = $_POST['time_start'] . ':00';
        $time_finish = $_POST['time_finish'] . ':00';
        $fullday = ($_POST['fullday'] == 'yes') ? 1 : 0;
        $repetition = $_POST['repetition'];
        $repeat_interval = $_POST['interval'];
        $repeat_period = $_POST['period'];
        $repeat_until = $_POST['repeat_until'];
        $date_until = convert_date($_POST['date_until']);
        if ($repetition == 2)
            $repeat_option = $_POST['repeat_option'];
        else
            $repeat_option = $_POST['repeat_option'];
        if ($repeat_until == 0)
            $date_until = '9999-00-00';
        
		$query = "INSERT INTO facility_book(book_date, id_user, id_facility, date_start, date_finish, time_start, time_finish, 
                    fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, purpose, remark, status)
					VALUES (now(), '$userid', $_id, '$date_start', '$date_finish', '$time_start', '$time_finish', 
                    '$fullday', '$repetition', '$repeat_interval', '$repeat_period', '$date_until', '$repeat_option', '$purpose', '$remark', 'BOOK')"; 
		mysql_query($query);
        $saved=1;

        if ($saved > 0){
            $submitted = true;     
                  
            // sending email notification 
            //send_submit_facility_request_notification($ids);
        
        } else
            $submitted = false;
        
    }
	return $submitted;
}



$timesheet = null;
$id_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
if (isset($_POST['date_start'])){
    $facility = get_facility($id_facility);
    $date_start = convert_date($_POST['date_start'], 'Y-m-d');
    $date_start_str = convert_date($_POST['date_start'], 'd-M-Y');
    $date_finish_str = convert_date($_POST['date_finish'], 'd-M-Y');
    //$timesheet = build_timesheet_book($date_start);
} else {
    $date_start_str = date('d-M-Y');
    $date_finish_str = date('d-M-Y', time_add_days(time(), 1));
    $facility['max_period'] = 1;
    $facility['lead_time'] = 1;
}

$facility_list = get_facility_list(true);	

$book = get_booking_info($_id);
$caption = (!empty($book)) ? 'Edit existing booking request' : 'Create New Facility Booking';
if (!empty($book)){
    $id_facility = $book['id_facility'];
    $date_start_str = $book['date_start'];
    $date_finish_str = $book['date_finish'];
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
<br/>
<div id="facility_view">
     <form method="post" id="form_facility">
     <input type="hidden" name="submitcode" value="">
     <input type="hidden" name="remove" value="">
     
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821">
      <tr>
        <th colspan=2><h3><?php echo $caption?></h3></th>
      </tr>
      <tr>
        <td align="left" width=130>Facility/Room</td>
        <td align="left">
		<select id="id_facility" name="id_facility">
		<?php echo build_option($facility_list, $id_facility)?>
		</select>
		</td>
      </tr>
      <tr class="alt">
        <td align="left">Date/Time</td>
        <td align="left">
          From: <input type="text" size=14 id="date_start" name="date_start" value="<?php echo $date_start_str?>" > 
          <select class="time" name="time_start" id="time_start"> </select> &nbsp; 
          To: &nbsp; 
          <input type="text" size=14 id="date_finish" name="date_finish" value="<?php echo $date_start_str?>"  >
          <select class="time" name="time_finish" id="time_finish"> </select>
          <br/><input type="checkbox" id="fullday" name="fullday" value="yes"><label for="fullday">Book for full day</label>
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
                    <input type="radio" name="repeat_option[]" value=0 <?php if ($repeat_option=='0') echo ' checked';?> >date of month
                    <input type="radio" name="repeat_option[]" value=1 <?php if ($repeat_option=='1') echo ' checked';?> >day of week
                </div>
            </div>
            <div class="leftcol">Repeat until:</div>
            <div class="rightcol">
            <ul >
                <li>
                <input type="radio" name="repeat_until" value=2 <?php if ($repeat_until==2) echo ' checked';?>>On 
                <input type="text" size=14 id="date_until" name="date_until" value="<?php echo $date_finish_str?>"  >
                <script>
                    var dateuntil = new Date(today+oneDay);
                    $('#date_until').AnyTime_picker({earliest: dateuntil, format: dateFormat});
                </script>
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
        <td align="left">Purpose of Use</td>
        <td align="left"><input type="text" size=55 id="purpose" name="purpose"></td>
      </tr>
      <tr class="normal">
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><textarea rows=4 cols=60 id="remark" name="remark"></textarea></td>
      </tr>
<?php if (!empty($timesheet)){ ?>	  
       <tr class="alt" valign="top">
        <!-- <td align="left">Time sheet</td> -->
        <td align="left" colspan=2><?php echo $timesheet?></td>
      </tr>
<?php } ?>
     <tr >
        <td colspan=2 align="right"><button type="button" onclick="submit_facility_request(this.form)">Booking Facility</button></td>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
</div>
<script type="text/javascript" src="js/jquery.json.js"></script>
<script type="text/javascript" src="js/portal_facility.js"></script>
<script type="text/javascript">
max_period = '<?php echo $facility['max_period']?>';
lead_time = '<?php echo $facility['lead_time']?>';
repetitions = '<?php echo implode(',', $repetitions);?>'.split(',');

if ($('#id_facility').val()>0)	
    $('#id_facility option').trigger("change");

fill_interval();
$('#date_start').trigger('change');
</script>

