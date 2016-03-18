<?php
$category_type = 'SERVICE';

$this_year = date('Y');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

					 // ambil daftar department
$query = 'SELECT id_department, department_name 
			FROM `department`
			ORDER BY department_name ASC';
$res = mysql_query($query);
while ($rec = mysql_fetch_array($res)) 
	$departments[$rec[0]] = $rec[1];

$query  = "SELECT MONTH(start_loan) service_month, id_department, COUNT(lr.id_loan) loan_count 
			FROM loan_request lr  
			LEFT JOIN department ON department.id_department = lr.id_department 
			LEFT JOIN category ON category.id_category = lr.id_category 
			WHERE YEAR(start_loan) = $_year AND category_type = '$category_type' 
			GROUP BY service_month, lr.id_department";			
$res = mysql_query($query);
echo mysql_error().$query; 	 
while ($rec = mysql_fetch_array($res))   
  $summary[$rec['service_month']][$rec['id_department']] = $rec['loan_count'];

$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
	
$year_combo = build_combo('y', $years, $this_year);
echo <<<HEAD
<h2>Service Report</h2>
<form method="Post">
<p style="color: #fff">
Select a year $year_combo <input type="submit" name="display" value=" Display ">
</p>
</form>
<table class="report" width="800" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms of Department<h3></td>
	<td colspan=10 align="right"><a href=".?mod=loan&sub=report&act=export&term=department">Export to Excel 2003</a></td>
</tr>
<tr><th>Department</th>
HEAD;
for ($id_month = 1; $id_month <= 12; $id_month++)
	echo '<th>'.substr($month_names[$id_month], 0, 3).'</th>';
echo '<th>Total</th></tr>';


$row = 0;
foreach ($departments as $id_department => $department_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $department_name . '</td>';
	$total = 0;

	for ($id_month = 1; $id_month <= 12; $id_month++){
		if (!isset($summary[$id_month][$id_department]))
			$summary[$id_month][$id_department] = 0;
		echo '<td align="center">'.$summary[$id_month][$id_department].'</td>';
		$total += $summary[$id_month][$id_department];
		// total tiap kolom/kategori
		if (!isset($month_total[$id_month]))
			$month_total[$id_month] = 0;
		$month_total[$id_month] += $summary[$id_month][$id_department];
	}
	echo '<td align="center" class="total_col">' . $total . '</td></tr>';// total tiap baris/status
}
// munculkan total tiap kolom
$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr '.$class.'><td style="text-align:left">Total</td>';
$grand_total = 0;
foreach ($statuses as $id_status => $status_name) {
	$grand_total += $status_total[$id_status];
	echo '<td align="center">' . $status_total[$id_status] . '</td>';
}
echo '<td align="center">'.$grand_total.'</td></tr>';
echo'</table>';
?>