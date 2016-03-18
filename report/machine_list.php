<?php

$category_type = 'EQUIPMENT';
$this_year = date('Y');
$this_month = date('n');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$_ewyr = $this_year;
$_ewmo = $this_month;
if (!empty($_GET['ew'])){
	$_ewyr = intval(substr($_GET['ew'], 0, 4));
	$_ewmo = intval(substr($_GET['ew'], 4, 2));
}

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$categories = get_category_list($category_type);
	
$query  = "SELECT serial_no, model_no, brand_name, status_name 
			FROM item 
			LEFT JOIN brand ON brand.brand_id = item.brand_id  
			LEFT JOIN status ON status.id_status = item.id_status 
			WHERE YEAR(warranty_end_date) = $_ewyr  AND MONTH(warranty_end_date) = $_ewmo  
			ORDER BY serial_no";			
$rs = mysql_query($query);
	
	
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

$year_month = $month_names[$_ewmo] . ', ' . $_ewyr;
echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>

<h2>Items with Warranty Expire on $year_month</h2>
<br/>
<table class="report" width="600" cellpadding=2 cellspacing=1>
<!--
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=10 align="right"><a href="#" onclick="export_this()">Export</a></td>
</tr>
-->
<tr>
	<th>Serial No</th>
	<th>Brand</th>
	<th>Model No</th>
	<th>Status</th>
</tr>
HEAD;
$row = 1;
if ($rs && mysql_num_rows($rs)>0){
	while ($rec = mysql_fetch_assoc($rs)){
		$class = ($row++ % 2 == 0) ? 'class="alt"' : 'class="normal"';
		echo <<<ROW
<tr $class>
	<td>$rec[serial_no]</td>
	<td>$rec[brand_name]</td>
	<td>$rec[model_no]</td>
	<td>$rec[status_name]</td>
</tr>
ROW;
	}
}
?>
</table>