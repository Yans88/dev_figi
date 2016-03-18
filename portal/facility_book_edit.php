<?php

$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$timesheet = null;
$id_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
if ($id_facility == 0)
    $id_facility = !empty($_GET['id_facility']) ? $_GET['id_facility'] : 0;
$d = !empty($_POST['d']) ? $_POST['d'] : 0;
if ($d == 0)
    $d = !empty($_GET['d']) ? $_GET['d'] : date('Y-m-d');
$tm = strtotime($d);
$_t = isset($_GET['t']) ? $_GET['t'] : null;
if ($_t == null){
    $dttm = $tm;
    if (date('i', $dttm) <= 30){
        $dttm = mktime(date('G'), 30,intval(date('s')),date('n'),date('j'),date('Y'));
        $_t = date('H:', $dttm) . '30';
    } else {
        $dttm = mktime(date('G'), 0,intval(date('s')),date('n'),date('j'),date('Y'));
        $_t = date('H:', $dttm).'00';
    }
} else {
    list($h, $m) = explode(':', $_t);
    $dttm = mktime($h, $m,intval(date('s')),date('n'),date('j'),date('Y'));
}

$time_start_str = $_t;
$dttm = mktime(date('G', $dttm)+1,intval(date('i', $dttm)),intval(date('s', $dttm)),date('n', $dttm),date('j', $dttm),date('Y', $dttm));
$time_finish_str = date('H:i', $dttm);    

if (isset($_POST['date_start'])){
    $facility = get_facility($id_facility);
    $date_start = convert_date($_POST['date_start'], 'Y-m-d');
    $date_start_str = convert_date($_POST['date_start'], 'j-M-Y');
    $date_finish_str = convert_date($_POST['date_finish'], 'j-M-Y');
} else {
    $date_start_str = date('j-M-Y', $tm);
    $date_finish_str = date('j-M-Y', time_add_days($tm, 1));
    $facility['max_period'] = 1;
    $facility['lead_time'] = 1;
}

$facility_list = get_facility_list(true);	
$repeat_until = 0;
$repeat_option = 0;
$weekdays = array(date('w'));

if (!empty($_POST['submitcode']) && ($_POST['submitcode']=='submit')){
    $userid = USERID;
    $_booked_date = convert_date($_POST['date_start'], 'Y-m-d H:i:s');
    $_times = !empty($_POST['times']) ? $_POST['times'] : array();
    $id_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
    $saved = 0;
    $repeat_option = @implode(',', @$_POST['repeat_option']);
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
		$purpose = mysql_escape_string(@$_POST['purpose']);
		$remark = mysql_escape_string(@$_POST['remark']);
        $date_start = convert_date($_POST['date_start'], 'Y-m-d');
        $date_finish = convert_date($_POST['date_finish'], 'Y-m-d');
        $time_start = $_POST['time_start'] . ':00';
        $time_finish = $_POST['time_finish'] . ':00';
        $fullday = (isset($_POST['fullday']) && ($_POST['fullday'] == 'yes')) ? 1 : 0;
        $repetition = $_POST['repetition'];
        $repeat_interval = @$_POST['interval'];
        $repeat_period = @$_POST['period'];
        $repeat_until = @$_POST['repeat_until'];
        //$date_until = @convert_date(@$_POST['date_until']);
        $date_until = convert_date(@$_POST['date_until'], 'Y-m-d');
        if ($repetition == 2 || $repetition == 3 ){
            if ($repetition == 2) { // weekly, anticipate for other days than selected date
                /*
                $dt = time_Ymd($date_start);
                $dow = date('w', $dt);
                $ro = $_POST['repeat_option'];
                sort($ro);
                $i = 0;
                while ($ro[$i] < $dow) $i++;
                $dt = date_add_day($dt, $ro[$i]-$dow);
                $date_start = date('Y-m-d', $dt);
                $dt = time_Ymd($date_finish);
                $dt = date_add_day($dt, $ro[$i]-$dow);
                $date_finish = date('Y-m-d', $dt);
                */
            }
        } else        
            $repeat_option = null;
        if ($repeat_until == 0)
            $date_until = '9999-00-00';
            
        $id_conflict = check_conflict_book($id_facility, $date_start, $time_start);
        if ($id_conflict>0){
            echo '<script>alert("Conflict with other booking for timing")</script>';
        } else {
            $query = "UPDATE facility_book SET id_facility='$id_facility', date_start='$date_start', date_finish='$date_finish', time_start='$time_start', 
                        time_finish='$time_finish', fullday=$fullday, repetition='$repetition', repeat_interval='$repeat_interval', 
                        repeat_period='$repeat_period', repeat_until='$date_until', repeat_option='$repeat_option', purpose='$purpose', 
                        remark='$remark' 
                        WHERE id_book = '$_id'"; 
            mysql_query($query);
            echo mysql_error().$query;
            $saved = 1; 
        }
        if ($saved > 0){
            $submitted = true;     
                  
            // sending email notification 
            //send_submit_facility_request_notification($ids);
            
        
        } else
            $submitted = false;
        if ($submitted){
        
            echo '
                <script>
                    alert("Booking information has been saved!");
                    location.href = "./?mod=portal&sub=history&portal=facility&act=view_month&id_facility='.$id_facility.'";
                </script>';
        
            return true;
        }
    }
    return false;
}

$facility_list = get_facility_list(true);	
$book = get_booking_info($_id);
print_r($book);
if (!empty($book)){
    $facility = get_facility($book['id_facility']);
    $date_start_str = date('d-M-Y', $dts);
    $date_finish_str= date('d-M-Y', $dte);
    $time_start_str = date('H:i', $dts);
    $time_finish_str = date('H:i', $dte);
    $repeat_until = !empty($book['dt_last']) ? 1 : 0;
    if ($repeat_until == 0) 
        $repeat_until_str =  '';
    else
        $repeat_until_str = date('d-M-Y', $book['dt_last']);
    $weekdays = explode(',', $book['repeat_option']);
    //$id_location = $event['id_location'];
    $selected_repetition = $book['repetition'];
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
<h4><br/>Edit Facility Booking</h4>
<div id="facility_view">
     <form method="post" id="form_facility">
     <input type="hidden" name="submitcode" value="">
     <input type="hidden" name="remove" value="">
     
        <table width="98%" class="itemlist" cellpadding=4 cellspacing=0 style="border: 1px solid #103821">
      <tr>
        <td align="left" width=130>Facility/Room</td>
        <td align="left">
		<select id="id_facility" name="id_facility">
		<?php echo build_option($facility_list, $book['id_facility'])?>
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
                    var earliestDate = new Date("<?php echo $book['date_start_fmt']?>");
                    $('#date_start').AnyTime_picker({earliest: earliestDate, format: dateFormat});
                    $('#date_finish').AnyTime_picker({earliest: earliestDate, format: dateFormat});

		  </script>
        </td>
      </tr>
      <tr >
        <td align="left" valign="top">Repetition</td>
        <td align="left">
            <select id="repetition" name="repetition"><?php echo build_option($repetitions, $book['repetition']);?></select>
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
                <input type="text" size=14 id="date_until" name="date_until" value="<?php echo $book['repeat_until_fmt']?>"  >
                <script>
                    var dateuntil = new Date(earliestDate+oneDay);
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
        <td align="left"><input type="text" size=55 id="purpose" name="purpose" value="<?php echo $book['purpose']?>"></td>
      </tr>
      <tr class="normal">
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><textarea rows=4 cols=60 id="remark" name="remark"><?php echo $book['remark']?></textarea></td>
      </tr>
     <tr >
        <td colspan=2 align="right">
            <br/>
            <button type="button" id="savebtn">Save</button>
            <br/>&nbsp;
        </td>
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
interval = "<?php echo $book['repeat_interval']?>";
tm_start = "<?php echo $time_start_fmt?>";
tm_finish = "<?php echo $time_finish_fmt?>";

if ($('#id_facility').val()>0)	
    $('#id_facility option').trigger("change");

fill_interval();
$('#date_start').trigger('change');

var fullday = "<?php echo ($book['fullday']==1) ? 1 : 0;?>";
if (fullday == 1)
	$('#fullday').trigger("click");
$('#repetition').trigger("change");

$('#interval option').each(function(){
    if ($(this).val() == interval)
        $(this).attr('selected', 'selected');
});
    
$('#savebtn').click(function (event){

    var dateFormat = "%e-%b-%Y";      
    var dateConv = new AnyTime.Converter({format:dateFormat});
    var sd = new Date(dateConv.parse($("#date_start").val()).getTime());
    var dd = new Date(dateConv.parse($("#date_finish").val()).getTime());
    if (!$('#fullday').attr('checked')){
        var sh = $('#time_start option:selected').val();
        var hm = sh.split(':');
        sd.setHours(hm[0]);
        sd.setMinutes(hm[1]);
        var dh = $('#time_finish option:selected').val();
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
        alert("It is not allowed book a facility for previous date/time");
        return false;
    }
    if ($('#purpose').val() == ''){
		alert("Write your purpose of booking the facility!");
		return false;
	}
    var text = "Do you want to confirm booking?\n";
    if (confirm(text)){
        frm.submitcode.value = "submit" ;
        frm.submit();
    }
}


</script>

