<?php
/*
require '../common.php';
require '../user/user_util.php';
require '../authcheck.php';
require './facility_util.php';
*/

$id_term  = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;
$term = period_term_get($id_term);

$_msg = null;
if (!empty($_POST['generate'])){

	$time_start = mktime(intval(substr($_POST['time_start'], 0, 2)), intval(substr($_POST['time_start'], 3, 2)), 0);
	$time_end = mktime(intval(substr($_POST['time_end'], 0, 2)), intval(substr($_POST['time_end'], 3, 2)), 0);
	$duration = !empty($_POST['interval']) ? $_POST['interval'] : 30;
	$modified_by = USERID;
	if ($time_start < $time_end) {
		$values = array();
		$duration_ms = $duration * 60 ;
		$stm = $time_start;
		while ($stm < $time_end){
			$tm_start = date('H:i', $stm);
			$stm += $duration_ms;
			$tm_end = date('H:i', $stm);
			$values[] = "($id_term, '$tm_start', '$tm_end', $modified_by)";    
		}
		if (count($values) > 0){
			// delete existing time sheet
			$query = 'DELETE FROM facility_period_timesheet WHERE id_term= '. $id_term;    
			mysql_query($query);

			// insert new time sheet
			$query = 'INSERT INTO facility_period_timesheet(id_term, time_start, time_end, modified_by) VALUES '. implode(',', $values);    
			mysql_query($query);
			error_log(mysql_error().$query);
		}
		$_msg = 'Generated ' . count($values) .' periods. Start from '. substr($_POST['time_start'], 0, 5) . ' to ' . substr($_POST['time_end'], 0, 5); 
	} else
		$_msg = 'Invalid time period. Please check time usage of the facility.';

	echo '<script>alert("'.$_msg.'");';
	//echo 'javascript:parent.jQuery.fancybox.close();';
	echo 'parent.location.reload()';
	echo '</script>';
	//redirect( './?mod=facility&sub=period&act=timesheet_list&id=' . $id_term, $_msg);
	exit;
}

ob_clean();
?>

<link rel="stylesheet" type="text/css" href="./style/default/figi.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<link rel='stylesheet' type='text/css' href='./style/default/jquery-ui-1.8.13.custom.css'/>	
<script type="text/javascript" src="./js/jquery/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="./js/anytimec.js"></script>
<script type="text/javascript" src="./js/moment.min.js"></script>
<form id="generate_form" method="post">
<table style="width: 100%; font-size: 10pt; margin:10px">
<tr >
	<th colspan=2><h4> Generate Period Timesheet</h4> </th>
</tr>
<tr >
	<td width=80>Term</td>
  	<td><?php echo $term['term']?></td>
</tr>
<tr >
	<td>Start Time</td>
  	<td>
		  <input type="text" size=6 id="time_start" name="time_start" value="">
		  <script>
			$('#time_start').AnyTime_picker({format: "%H:%i"});
		  </script>

  	</td>
</tr>
<tr >
	<td>End Time</td>
  	<td>
		  <input type="text" size=6 id="time_end" name="time_end" value="" readonly>
		  <script>
			$('#time_end').AnyTime_picker({format: "%H:%i"});
		  </script>

  	</td>
</tr>
<tr >
	<td>Interval</td>
  	<td>
		  <input type="text" size=4 id="interval" name="interval" value=""> <cite>minutes</cite>
	</td>
</tr>
<tr valign="middle">
  	<td colspan=2 class="center">	
	&nbsp;<br>
  	<button type="button" name="generate" >  Generate Period </button>
	</td>
</tr>

</form>
<script>
$('button[name=generate]').click(function(){
	var ok = true;
	var t = $('input[name=time_start]').val();
	if (t.length==0){
		alert('Please set the start time!');
		$('input[name=time_start]').focus();
		ok = false;
	}
	t = $('input[name=time_end]').val();
	if (t.length==0){
		alert('Please set the end time!');
		$('input[name=time_end]').focus();
		ok = false;
	}
	t = $('input[name=interval]').val();
	if (t.length==0){
		alert('Please set the interval in minute!');
		$('input[name=interval]').focus();
		ok = false;
	}
	if (ok){
		$('#generate_form').append('<input type="hidden" name="generate" value=1>');
		$('#generate_form').submit();
	}

});

</script>
<?php
ob_end_flush();
?>
