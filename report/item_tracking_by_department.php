<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

// ambil daftar status
$res = mysql_query('SELECT id_status, status_name FROM `status`');
while ($rec = mysql_fetch_array($res)) 
	$statuses[$rec[0]] = $rec[1];

// ambil daftar department
$query = 'SELECT id_department, department_name 
			FROM `department` ';
if ($id_department > 0)
	$query .= " WHERE id_department = $id_department ";
$query .= " ORDER BY department_name ASC ";
$res = mysql_query($query);
while ($rec = mysql_fetch_array($res)) 
	$departments[$rec[0]] = $rec[1];

$query  = "SELECT id_status, item.id_department, COUNT(item.id_item) item_count 
			FROM item 
			LEFT JOIN category ON category.id_category = item.id_category 
			WHERE category_type = '$category_type' ";
if ($id_department > 0)
	$query .= " AND id_department = $id_department ";
$query .= " GROUP BY id_status, id_department";			
			//LEFT JOIN item_status ON item.id_status = item_status.id_status 
$res = mysql_query($query);
while ($rec = mysql_fetch_array($res)) {   
  $summary[$rec['id_status']][$rec['id_department']] = $rec['item_count'];
}

if (isset($_GET['act']) && $_GET['act'] == 'export'){
	$crlf = "\n";
	$content = 'Department';
	foreach ($statuses as $id_status => $status_name)
		$content .= ','.$status_name;
	$content .= ',Total'. $crlf;

	foreach ($departments as $id_department => $department_name){ // baris
		$content .= $department_name ;	
		$total = 0;

		foreach ($statuses as $id_status => $status_name) {
			if (!isset($summary[$id_status][$id_department]))
				$summary[$id_status][$id_department] = 0;
			$content .= ','.$summary[$id_status][$id_department];
			$total += $summary[$id_status][$id_department];
			// total tiap kolom/kategori
			if (!isset($status_total[$id_status]))
				$status_total[$id_status] = 0;
			$status_total[$id_status] += $summary[$id_status][$id_department];
		}
		$content .= ',' . $total. $crlf;// total tiap baris/status	
	}

	$content .= 'Total';
	$grand_total = 0;
	foreach ($statuses as $id_status => $status_name) {
		$grand_total += $status_total[$id_status];
		$content .= ',' . $status_total[$id_status];
	}
	$content .= ','.$grand_total;
	
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_tracking_by_department.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;	
}

echo <<<HEAD
<script>
function export_this(){
	location.href = "./?mod=report&sub=item&act=export&term=tracking&by=department";
}
</script>
<h3>Inventory Tracking Report</h3>
<table class="report" width="100%" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=9><h3>In Terms Of Department<h3></td>
	<td colspan=4 align="right"><a href="#" onclick="export_this()" class="button">Export</a></td>
</tr>
<tr><th>Department</th>
HEAD;
foreach ($statuses as $id_status => $status_name)
	echo '<th>'.$status_name.'</th>';
echo '<th>Total</th></tr>';

$row = 0;
foreach ($departments as $id_department => $department_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $department_name . '</td>';
	$total = 0;

	foreach ($statuses as $id_status => $status_name) {
		if (!isset($summary[$id_status][$id_department]))
			$summary[$id_status][$id_department] = 0;
		echo '<td align="center">'.$summary[$id_status][$id_department].'</td>';
		$total += $summary[$id_status][$id_department];
		// total tiap kolom/kategori
		if (!isset($status_total[$id_status]))
			$status_total[$id_status] = 0;
		$status_total[$id_status] += $summary[$id_status][$id_department];
	}
	echo '<td align="center">' . $total . '</td></tr>';// total tiap baris/status
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
