<?php

$category_type = 'CONSUMABLE';
$this_year = date('Y');
$this_month = date('n');

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
$result = array();
if (isset($_POST['display'])){
	$_display = true;
	
	$query  = "SELECT id_item, asset_no, serial_no, brand_name,	model_no, date_of_purchase, 
				warranty_end_date, location, full_name issued_to, vendor_name, status_name  
				FROM item   
				LEFT JOIN user ON user.id_user = item.issued_to 
				LEFT JOIN brand ON brand.id_brand = item.id_brand 
				LEFT JOIN vendor ON vendor.id_vendor = item.id_vendor 
				LEFT JOIN status ON status.id_status = item.id_status  
				WHERE YEAR(warranty_end_date) = $_year AND MONTH(warranty_end_date) = $_month  
				AND id_category = $_category
				ORDER BY date_of_purchase DESC";			
	$res = mysql_query($query);
	//echo mysql_error().$query;
	$i = 0;
	while ($rec = mysql_fetch_array($res))
		$result[$i++] = $rec;
}

$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
$months = array();
for ($i = 1; $i <= 12; $i++)
	$months[$i] = $month_names[$i];
$item_count = count($result);

if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = '"Asset No","Serial No","Brand","Model No","Date of Purchase","Location","Issued To","Vendor","Status"' . $crlf;
	foreach ($result as $rec){ // baris	
		$content .=<<<CONTENT
"$rec[asset_no]","$rec[serial_no]","$rec[brand_name]","$rec[model_no]","$rec[date_of_purchase]","$rec[location]","$rec[issued_to]","$rec[vendor_name]","$rec[status_name]"$crlf
CONTENT;
	}
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_warranty_filtered.csv");
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
<h2>Warranty Expire Filtered by Category and Month</h2>
<form method="Post">
<input type=hidden name=act>
<p style="color: #fff">
Category $category_combo Month $month_combo $year_combo <input type="submit" name="display" value=" Display ">
</p>
</form>
HEAD;

if ($item_count > 0){
	echo <<<HEAD1
<table class="report" width="1024" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=5><h3>In Terms Of Category<h3></td>
	<td colspan=6 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr>
	<th>Asset No</th>
	<th>Serial No</th>
	<th>Brand</th>
	<th>Model No</th>
	<th width=40>Date of Purchase</th>
	<th>Location</th>
	<th>Issued To</th>
	<th>Vendor</th>
	<th width=40>Status</th>
</tr>
HEAD1;
	//<th width=20>Detail</th>

	for ($row = 0; $row < $item_count; $row++){ // baris
		$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
		$rec = $result[$row];
		echo <<<ROW
<tr $class>
	<td>$rec[asset_no]</td>
	<td><a href="./?mod=item&act=view&id=$rec[id_item]" title="view detail item" class="item">$rec[serial_no]</a></td>
	<td>$rec[brand_name]</td>
	<td>$rec[model_no]</td>
	<td>$rec[date_of_purchase]</td>
	<td>$rec[location]</td>
	<td>$rec[issued_to]</td>
	<td>$rec[vendor_name]</td>
	<td>$rec[status_name]</td>
ROW;
	//<td><a href="./?mod=item&act=view&id=$rec[id_item]">view</a></td>
	}
	echo '</table>';
} else
	echo '<p class="error" height=50 align="center">Data is not available!</p>';

?>
