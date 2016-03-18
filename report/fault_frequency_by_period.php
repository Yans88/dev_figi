<?php

$dept = 0;
$today = date('d-M-Y');
$_start = !empty($_POST['time_start']) ? $_POST['time_start'] : $today;
$_end = !empty($_POST['time_end']) ? $_POST['time_end'] : $today;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$summary = array();
$categories = array(
				FAULT_NOTIFIED => 'Fault Notified',
				FAULT_PROGRESS => 'Under Rectification',
				FAULT_COMPLETED => 'Rectification Completed');

if (isset($_POST['act'])){
	$start_date = convert_date($_start, 'Y-m-d');
	$end_date = convert_date($_end, 'Y-m-d');
	$query = "SELECT MONTH(report_date) monkey, fault_status, count(id_fault) num_of_faults  
				FROM fault_report fr 
				WHERE UNIX_TIMESTAMP(report_date) >= UNIX_TIMESTAMP('$start_date')
				AND UNIX_TIMESTAMP(report_date) <= UNIX_TIMESTAMP('$end_date')
				GROUP BY monkey, fault_status ";
	$res = mysql_query($query);
	//echo mysql_error().$query;
	while ($rec = mysql_fetch_array($res)) {   
		$summary[$rec['monkey']][$rec['fault_status']] = $rec['num_of_faults'];
	}
}

if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'Fault Status';
	for ($i = 1; $i < 13; $i++)
		$content .= ','.substr($month_names[$i], 0, 3);
	$content .= ',Total' . $crlf;

	foreach ($categories as $id_cat => $cat_name){ // baris
		$num_of_times = (isset($summary[$id_cat])) ? $summary[$id_cat] : 0;
		$content .= $cat_name;
		$total = 0;
		for ($i = 1; $i < 13; $i++){
			$summary[$i][$id_cat] = isset($summary[$i][$id_cat]) ? $summary[$i][$id_cat] : 0;
			$content .= ',' . $summary[$i][$id_cat];
			$total += $summary[$i][$id_cat] ;
			if (!isset($month_total[$i]))
				$month_total[$i] = 0;
			$month_total[$i] += $summary[$i][$id_cat];			
		}
		$content .= ',' . $total . $crlf;
	}
	$content .= 'Total';
	$grand_total = 0;
	for ($i = 1; $i < 13; $i++) {
		$grand_total += $month_total[$i];
		$content .= ',' . $month_total[$i] ;
	}
	$content .= ','.$grand_total;
	
	$period = $start_date . '_' . $end_date;
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=fault_frequency_by_period_$period.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}



echo <<<HEAD
<h3>Summary of Fault Status in Period</h3>
<form method="Post">
<input type=hidden name=act>
<p>
	Period from
	<input size=12 type=text name=time_start id=time_start value="$_start">
	<button type="button" id="button_time_start"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/>a</button>
	<script>
	$('#button_time_start').click(
	  function(e) {
		$('#time_start').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
		e.preventDefault();
	  } );
	</script>
	 to 
	<input size=12 type=text name=time_end id=time_end value="$_end">
	<button type="button" id="button_time_end"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></button>
	<script>
	$('#button_time_end').click(
	  function(e) {
		$('#time_end').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
		e.preventDefault();
	  } );
	</script>
	<button type="submit" name="display" >Display</button>

</p>
</form>
HEAD;
if (count($summary) > 0){
echo <<<RESULT
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>
<table class="report middle" width="800" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Fault Status<h3></td>
	<td colspan=10 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Fault Status</th>
RESULT;

for ($i = 1; $i < 13; $i++)
	echo '<th>'.substr($month_names[$i], 0, 3).'</th>';
echo '<th>Total</th></tr>';

$row = 0; $total = 0;
foreach ($categories as $id_cat => $cat_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $cat_name . '</td>';
	
	for ($i = 1; $i < 13; $i++){
		$summary[$i][$id_cat] = isset($summary[$i][$id_cat]) ? $summary[$i][$id_cat] : 0;
		echo '<td align="center">' . $summary[$i][$id_cat] . '</td>';
		$total += $summary[$i][$id_cat];
		if (!isset($month_total[$i]))
			$month_total[$i] = 0;
		$month_total[$i] += $summary[$i][$id_cat];
	}
	echo '<td align="center" class="total_col">' . $total . '</td></tr>';
}

$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr class="normal total_row"><td style="text-align:left">Total</td>';
$grand_total = 0;
for ($i = 1; $i < 13; $i++) {
	$grand_total += $month_total[$i];
	echo '<td align="center" class="total_row">' . $month_total[$i] . '</td>';
}
echo '<td class="total_col" align="center" >'.$grand_total.'</td></tr>';
echo'</table>';
} //else 	echo '<div class="error">Data is not avaialable!</div>';

?>
<style>
</style>
