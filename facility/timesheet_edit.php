<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_sid = isset($_GET['sid']) ? $_GET['sid'] : 0;
$_msg = null;

$facility_status = array('AVAILABLE' => 'Available','BOOKED' => 'Booked','CLOSED' => 'Closed');

if (isset($_POST['save'])) {
		
	$sid = ($_sid == 0) ? 'null' : $_sid;
	$time_start = $_POST['time_start'];
	$time_end = $_POST['time_end'];
	$query = "REPLACE INTO facility_timesheet (id_facility, id_time, time_start, time_end) 
			  VALUES ($_id, $sid, '$time_start', '$time_end')";
	$rs = mysql_query($query);
	if ($_sid == 0)
		$_sid = mysql_insert_id();
	
	$_msg = "Facility data has been updated!";
	echo '<script>alert("'.$_msg.'");location.href="./?mod=facility&sub=timesheet&act=view&id='.$_id.'"</script>';
	return;
		
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=facility&sub=timesheet&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
$facility_data = get_facility($_id);
$timesheet_data = get_timesheet($_id, $_sid);
   
$latest['time_start'] = '00:00';
$latest['time_end'] = '00:00';
if ($_sid == 0){
	$time_start = mktime(7, 0, 0);
	$time_end = mktime(8, 0, 0);
	$timesheet_data['time_start'] = date('H:i', $time_start);
	$timesheet_data['time_end'] = date('H:i', $time_end);
	$caption = 'Add New Period';
} else {
    $caption = 'Edit a Period';
    $timesheets = get_timesheets($_id);
    if (count($timesheets) > 0) {
        $latest = $timesheets[count($timesheets)-1];
    }
}

// logging

?>

<script type="text/javascript">
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
</script>
<style type="text/css">
  #time_start { background-image:url("images/clock.png");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:bold}
  #time_end { background-image:url("images/clock.png");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:bold}
	/*
  #AnyTime--time_start {background-color:#EFEFEF;border:1px solid #CCC}
  #AnyTime--time_start * {font-weight:bold}
  #AnyTime--time_start .AnyTime-btn {background-color:#F9F9FC;
    border:1px solid #CCC;color:#3090C0}
  #AnyTime--time_start .AnyTime-cur-btn {background-color:#FCF9F6;
      border:1px solid #FFC030;color:#FFC030}
  #AnyTime--time_start .AnyTime-focus-btn {border-style:dotted}
  #AnyTime--time_start .AnyTime-lbl {color:black}
  #AnyTime--time_start .AnyTime-hdr {background-color:#FFC030;color:white}
  */
</style>
<br/>
<br/>
<form method="POST">
<table width=400 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2><?php echo $caption?></th></tr>
<tr>
  <td width=150>Facility No </td>
  <td><?php echo $facility_data['facility_no']?></td>
</tr>
<tr valign="top" class="alt">
  <td>Time Usage / Day</td>
  <td><?php echo $facility_data['time_start'] . ' &nbsp; - &nbsp; '. $facility_data['time_end']?></td>
 </tr>
<tr valign="top">
  <td>Latest Period</td>
  <td><?php echo $latest['time_start'] . ' &nbsp; - &nbsp; '. $latest['time_end']?></td>
 </tr>
<tr valign="top" >
  <td style="margin-top: 20px">Duration / Period</td>
  <td><?php
		//echo "$start_hour_combo:$start_minute_combo to $end_hour_combo:$end_minute_combo ";
		?>
		  <input type="text" size=6 id="time_start" name="time_start" value="<?php echo $timesheet_data['time_start']?>" readonly>
		  &nbsp; - &nbsp;
		  <input type="text" size=6 id="time_end" name="time_end" value="<?php echo $timesheet_data['time_end']?>" readonly>
		  <script>
			$('#time_start').AnyTime_picker({format: "%H:%i"});
			$('#time_end').AnyTime_picker({format: "%H:%i"});
		  </script>

  </td>
 </tr>
<tr valign="middle">
  <th colspan=2><br/>
	<input type="submit" name="save" value=" Save Period ">
	<input type="button" name="cancel" value=" Cancel " onclick="location.href='<?php echo $_SERVER['HTTP_REFERER']?>';">

<?php
    
if ($_id > 0) {
echo <<<TEXT
<input type="submit" name="delete" value=" Delete " 
	onclick="return confirm('Are you sure delete period \'$facility_data[time_start] - $facility_data[time_end]\'?')">
TEXT;
}
?>
</td></tr>
</table>
<input type="hidden" name="back_to" value="<?php echo $_SERVER['HTTP_REFERER']?>" > 
<input type="hidden" name="id_facility" value="<?php echo $_id?>" > 
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
