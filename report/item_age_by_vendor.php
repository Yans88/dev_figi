<?php
$category_type = 'EQUIPMENT';
/*
make random date in mysql
UPDATE item SET warranty_end_date = MAKEDATE(FLOOR(1 + (RAND() * 4))+2007, FLOOR(1 + (RAND() * 365))) WHERE year(warranty_end_date)=0
*/
// ambil daftar vendor
$query = "SELECT id_vendor, vendor_name FROM `vendor` 
			ORDER BY vendor_name ASC ";
$res = mysql_query($query);
while ($rec = mysql_fetch_array($res)) 
	$vendors[$rec[0]] = $rec[1];

$this_year = date("Y");
$query  = "SELECT ($this_year-YEAR(date_of_purchase)) item_age, item.id_vendor, count(item.id_item) item_count 
			FROM item 
			LEFT JOIN category ON category.id_category = item.id_category
			WHERE  category_type = '$category_type' 
			GROUP BY item_age, item.id_vendor ";
$res = mysql_query($query);
//echo mysql_error().$query;
while ($rec = mysql_fetch_array($res)) {   
  if ($rec['item_age'] > 5) continue; // cari hanya sampai 5 tahun
  if ($rec['item_age'] == 0) // termasuk berumur 1 tahun
	$summary['1'][$rec['id_vendor']] = $rec['item_count'];
  else
	$summary[$rec['item_age']][$rec['id_vendor']] = $rec['item_count'];
}

$age_list = array('0', '1', '2', '3', '4', '5');
$jml_umur = count($age_list)-1;
$jml_kategori = count($vendors);

if (isset($_GET['act']) && $_GET['act'] == 'export'){
	$crlf = "\n";
	$content = 'Category';
	foreach ($age_list as $age_name)	
		$content .= ','.$age_name;
	$content .= ',Total'. $crlf;

	foreach ($vendors as $id_vendor => $vendor_name){ // baris
		$content .= $vendor_name ;	
		$total = 0;

		foreach ($age_list as $age_name) {
			if (!isset($summary[$age_name][$id_vendor]))
				$summary[$age_name][$id_vendor] = 0;
			$content .= ','.$summary[$age_name][$id_vendor];
			$total += $summary[$age_name][$id_vendor];
			// total tiap kolom/kategori
			if (!isset($age_total[$age_name]))
				$age_total[$age_name] = 0;
			$age_total[$age_name] += $summary[$age_name][$id_vendor];
		}
		$content .= ',' . $total. $crlf;// total tiap baris/status	
	}

	$content .= 'Total';
	$grand_total = 0;
	foreach ($age_list as $age_name){
		$grand_total += $age_total[$age_name];
		$content .= ',' .  $age_total[$age_name];
	}
	$content .= ','.$grand_total;
	
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_age_by_vendor.csv");
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
	location.href = "./?mod=report&sub=item&act=export&term=age&by=vendor";
}
</script>

<h3>Item Age Report</h3>
<table class="report middle" cellpadding=2 cellspacing=1 width=600>
<tr>
	<td colspan=4><h3>In Terms Of Vendor<h3></td>
	<td colspan=4 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Vendor</th>
HEAD;
foreach ($age_list as $age_name)
	echo '<th width=50>'.$age_name.'</th>';
echo '<th>Total</th></tr>';

$row = 0;
foreach ($vendors as $id_vendor => $vendor_name){ // baris
	if (empty($vendor_name)) continue;
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $vendor_name . '</td>';
	$total = 0;

	foreach ($age_list as $age_name) {
		if (!isset($summary[$age_name][$id_vendor]))
			$summary[$age_name][$id_vendor] = 0;
		echo '<td align="center">'.$summary[$age_name][$id_vendor].'</td>';
		$total += $summary[$age_name][$id_vendor];
		// total tiap kolom/kategori
		if (!isset($age_total[$age_name]))
			$age_total[$age_name] = 0;
		$age_total[$age_name] += $summary[$age_name][$id_vendor];
	}
	echo '<td align="center">' . $total . '</td></tr>';// total tiap baris/status
}
// munculkan total tiap kolom
$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr '.$class.'><td style="text-align:left">Total</td>';
$grand_total = 0;
foreach ($age_list as $age_name){
	$grand_total += $age_total[$age_name];
	echo '<td align="center">' . $age_total[$age_name] . '</td>';
}
echo '<td align="center">'.$grand_total.'</td></tr>';

?>
</table>
<br/>&nbsp;
<br/>&nbsp;
