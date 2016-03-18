<?php

$category_type = 'EQUIPMENT';
$this_year = date('Y');
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$categories = get_category_list($category_type, $id_department );
	
$query  = "SELECT MONTH(warranty_end_date) expire_month, item.id_category, COUNT(item.id_item) item_count 
			FROM item   
			LEFT JOIN category ON category.id_category = item.id_category 
			WHERE YEAR(warranty_end_date) = $_year AND category_type = '$category_type' 
			GROUP BY expire_month, item.id_category ";			
			//LEFT JOIN item_status ON item.id_month = item_status.id_month 
$res = mysql_query($query);

while ($rec = mysql_fetch_array($res)) {   
  $summary[$rec['expire_month']][$rec['id_category']] = $rec['item_count'];
}

$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
	
	
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'Category';
	for ($id_month = 1; $id_month <= 12; $id_month++)
		$content .= ','.substr($month_names[$id_month], 0, 3);
	$content .= ',Total'. $crlf;

	foreach ($categories as $id_category => $category_name){ // baris
		$content .= $category_name ;	
		$total = 0;

		for ($id_month = 1; $id_month <= 12; $id_month++){
			if (!isset($summary[$id_month][$id_category]))
				$summary[$id_month][$id_category] = 0;
			$content .= ','.$summary[$id_month][$id_category];
			$total += $summary[$id_month][$id_category];
			// total tiap kolom/kategori
			if (!isset($month_total[$id_month]))
				$month_total[$id_month] = 0;
			$month_total[$id_month] += $summary[$id_month][$id_category];
		}
		$content .= ','.$total . $crlf;// total tiap baris/status
	}

	$content .= 'Total';
	$grand_total = 0;
	for ($id_month = 1; $id_month <= 12; $id_month++) {
		$grand_total += $month_total[$id_month];
		$content .= ',' . $month_total[$id_month] ;
	}
	$content .= ','.$grand_total;
	
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_warranty_by_category.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

$year_combo = build_combo('y', $years, $this_year);

echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>

<h3>Warranty Expiry Report</h3>
<form method="Post">
<input type=hidden name=act>

<p style="color: #fff" class="center">
Select a year $year_combo <input type="submit" name="display" value=" Display ">
</p>
</form>
<table class="report" width="100%" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=10 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Category</th>
HEAD;
for ($id_month = 1; $id_month <= 12; $id_month++)
	echo '<th>'.substr($month_names[$id_month], 0, 3).'</th>';
echo '<th>Total</th></tr>';

$row = 0;
foreach ($categories as $id_category => $category_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $category_name . '</td>';
	$total = 0;

	for ($id_month = 1; $id_month <= 12; $id_month++){
		if (!isset($summary[$id_month][$id_category]))
			$summary[$id_month][$id_category] = 0;
		$value = 0;
		if ($summary[$id_month][$id_category] > 0)			
			$value = '<a class="itemval" href="./?mod=report&sub=item&term=warranty&act=list_item&ew='.$_year.$id_month.'&cat='.$id_category.'">'.$summary[$id_month][$id_category].'</a>';
		echo '<td align="center">'.$value.'</td>';
		$total += $summary[$id_month][$id_category];
		// total tiap kolom/kategori
		if (!isset($month_total[$id_month]))
			$month_total[$id_month] = 0;
		$month_total[$id_month] += $summary[$id_month][$id_category];
	}
	echo '<td align="center" class="total_col">' . $total . '</td></tr>';// total tiap baris/status
}
// munculkan total tiap kolom
$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr '.$class.'><td style="text-align:left" class="total_row">Total</td>';
$grand_total = 0;
for ($id_month = 1; $id_month <= 12; $id_month++) {
	$grand_total += $month_total[$id_month];
	echo '<td align="center" class="total_row">' . $month_total[$id_month] . '</td>';
}
echo '<td align="center" class="total_row">'.$grand_total.'</td></tr>';
echo'</table>';
?>
<br>&nbsp;<br>
