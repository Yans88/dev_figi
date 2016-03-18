<?php

$dept = 0;
$this_year = date('Y');
$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$summary = array();
$categories = array();
if (isset($_POST['act'])){
	$query = "SELECT MONTH(report_date) monkey, full_name, count(id_fault) num_of_faults  
				FROM fault_report fr 
				LEFT JOIN user u ON u.id_user = report_user 				
				WHERE YEAR(report_date) = $_year 
				GROUP BY monkey, full_name";
	$res = mysql_query($query);

	while ($rec = mysql_fetch_array($res)) {   
		$summary[$rec['monkey']][$rec['full_name']] = $rec['num_of_faults'];
		$categories[$rec['full_name']] = $rec['full_name'];
	}
}

if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'User';
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
	
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=fault_frequency_by_user.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}


$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
$year_combo = build_combo('y', $years, $this_year);

echo <<<HEAD
<h3>Number of Times of Fault Done</h3>
<form method="Post">
<input type=hidden name=act>
<p class="center">
Select a year $year_combo <button type="submit" name="display" >Display</button>
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
	<td colspan=4><h3>In Terms Of User<h3></td>
	<td colspan=10 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>User</th>
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
} //else 
	//echo '<div class="error">Data is not avaialable!</div>';

?>
