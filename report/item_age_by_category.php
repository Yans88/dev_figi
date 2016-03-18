<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;


/*
make random date in mysql
UPDATE item SET warranty_end_date = MAKEDATE(FLOOR(1 + (RAND() * 4))+2007, FLOOR(1 + (RAND() * 365))) WHERE year(warranty_end_date)=0
*/
$categories = get_category_list($category_type, 0 );

$this_year = date("Y");
$query  = "SELECT ($this_year-YEAR(date_of_purchase)) item_age, item.id_category, count(item.id_item) item_count 
			FROM item 
			LEFT JOIN category ON category.id_category = item.id_category
			WHERE  category_type = '$category_type'";
	if(!empty($id_department))
		$query .= " AND item.id_department = ".$id_department."";
			
		$query	.= " GROUP BY item_age, item.id_category ";
		
$res = mysql_query($query);
//echo mysql_error().$query;
while ($rec = mysql_fetch_array($res)) {   
  if ($rec['item_age'] > 5) continue; // cari hanya sampai 5 tahun
  if ($rec['item_age'] == 0) // termasuk berumur 1 tahun
	$summary['1'][$rec['id_category']] = $rec['item_count'];
  else
	$summary[$rec['item_age']][$rec['id_category']] = $rec['item_count'];
}
//print_r($summary);
$age_list = array('0', '1', '2', '3', '4', '5','6','7','8','9','10');
$jml_umur = count($age_list)-1;
$jml_kategori = count($categories);
//print_r($categories);

if (isset($_GET['act']) && $_GET['act'] == 'export'){
	$crlf = "\n";
	$content = 'Category';
	foreach ($age_list as $age_name)	
		$content .= ','.$age_name;
	$content .= ',Total'. $crlf;

	foreach ($categories as $id_category => $category_name){ // baris
		$content .= $category_name ;	
		$total = 0;
        $age_no = 0;
		foreach ($age_list as $age_name) {
			if (!isset($summary[$age_no][$id_category]))
				$summary[$age_no][$id_category] = 0;
			$content .= ','.$summary[$age_no][$id_category];
			$total += $summary[$age_no][$id_category];
			// total tiap kolom/kategori
			if (!isset($age_total[$age_no]))
				$age_total[$age_no] = 0;
			$age_total[$age_no] += $summary[$age_no][$id_category];
            $age_no++;
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
	header("Content-Disposition: attachment; filename=item_age_by_category.csv");
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
	location.href = "./?mod=report&sub=item&act=export&term=age&by=category";
}
</script>
<h3>Item Age Report</h3>
<table class="report item-list middle" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=9 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Category</th>
HEAD;

foreach ($age_list as $age_name)
	echo '<th width=50>'.$age_name.'</th>';
echo '<th>Total</th></tr>';

$row = 0;
foreach ($categories as $id_category => $category_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $category_name . '</td>';
	$total = 0;
    $age_no = 0;
	foreach ($age_list as $age_name) {
		$var = $summary[$age_no][$id_category];
		if ($var != 0){
			$variable = "<a href='./?mod=report&sub=item&act=view&term=tracking&by=agecategory&cat=".$id_category."&no=".$age_no."'><b>".$var."</b></a>";
		} else {
			$variable = 0;
		}
	
		if (!isset($summary[$age_no][$id_category]))
			$summary[$age_no][$id_category] = 0;
		echo '<td align="center">'.$variable.' </td>';
		$total += $summary[$age_no][$id_category];
		// total tiap kolom/kategori
		if (!isset($age_total[$age_no]))
			$age_total[$age_no] = 0;
		$age_total[$age_no] += $summary[$age_no][$id_category];
        $age_no++;
	}
	echo '<td align="center">' . $total . '</td></tr>';// total tiap baris/status
}
// munculkan total tiap kolom
$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr '.$class.'><td style="text-align:left">Total</td>';
$grand_total = 0;
//$age_no = 0;
foreach ($age_list as $age_name){
	$grand_total += $age_total[$age_name];
	echo '<td align="center">' . $age_total[$age_name] . '</td>';
}
echo '<td align="center">'.$grand_total.'</td></tr>';

?>
</table>
<br/>&nbsp;
<br/>&nbsp;
