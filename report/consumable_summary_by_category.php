<?php

$category_type = 'CONSUMABLE';
$this_year = date('Y');
$this_month = date('n');

require_once('item/item_util.php');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;
$_month = !empty($_POST['m']) ? $_POST['m'] : $this_month;
$_category = !empty($_POST['c']) ? $_POST['c'] : 0;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$categories = get_category_list($category_type, USERDEPT);
/*
if ($_category == null){
	$values = array_values($categories);
	$_category = $values[0];
}
*/
$_display = false;
$summary = array();
if (isset($_POST['display'])){
	$_display = true;
	
	$query  = "SELECT ci.id_category cat, MONTH(trx_time) mon, SUM(quantity) qty 
				FROM consumable_item_out cio 
				LEFT JOIN consumable_item_out_list ciol ON ciol.id_trx = cio.id_trx 
				LEFT JOIN consumable_item ci ON ci.id_item = ciol.id_item 
				LEFT JOIN category c ON c.id_category = ci.id_category 
				WHERE YEAR(trx_time) = $_year AND ci.id_category IS NOT NULL 
				ORDER BY category_name ASC";			
	$res = mysql_query($query);
	//echo mysql_error().$query;
	$i = 0;
	while ($rec = mysql_fetch_assoc($res))
		$summary[$rec['mon']][$rec['cat']] = $rec['qty'];
}

$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
$months = array();
for ($i = 1; $i <= 12; $i++)
	$months[$i] = $month_names[$i];
$item_count = count($summary);

if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\r\n";
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
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=consumable_summary-$_year.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

$year_combo = build_combo('y', $years, $_year);
$month_combo = build_combo('m', $months, $_month);
$category_combo = build_combo('c', $categories, $_category);
echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>
<h2>Consumable Item Usage Summary</h2>
<form method="Post">
<input type=hidden name=act>
<p style="color: #fff">
Select year to display $year_combo <input type="submit" name="display" value=" Display ">
</p>
</form>
HEAD;

if ($item_count > 0){
	echo <<<HEAD1
<table class="report" width="900" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=5><h3>In Terms Of Category<h3></td>
	<td colspan=9 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Category</th>
HEAD1;
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
			$value = '<a class="itemval" href="./?mod=report&sub=consumable&term=transaction&act=list&ew='.$_year.$id_month.'">'.$summary[$id_month][$id_category].'</a>';
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
}
?>
