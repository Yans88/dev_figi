<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

// ambil daftar status
$res = mysql_query('SELECT id_status, status_name FROM `status`');
while ($rec = mysql_fetch_array($res)) 
	$statuses[$rec[0]] = $rec[1];

// ambil daftar category
/*
$query = "SELECT id_category, category_name FROM `category` 
			WHERE category_type = '$category_type' ";
if ($id_department > 0)
	$query .= " AND id_department = $id_department ";
$query .= " ORDER BY category_name ASC ";

$res = mysql_query($query);
while ($rec = mysql_fetch_array($res)) 
	$categories[$rec[0]] = $rec[1];
*/
//$id_department=0;
$categories = get_category_list($category_type, $id_department);
$query  = "SELECT id_status, item.id_category, COUNT(item.id_item) item_count 
			FROM item
			/*LEFT JOIN department_category dc ON dc.id_category = item.id_category*/
			LEFT JOIN category ON category.id_category = item.id_category 
			WHERE category_type = '$category_type' ";
			
if ($id_department > 0)
	$query .= " AND item.id_department = $id_department ";
	$query .= " GROUP BY id_status, item.id_category";		
	$res = mysql_query($query);
//echo $query.mysql_error();
while ($rec = mysql_fetch_array($res)) {   
  $summary[$rec['id_status']][$rec['id_category']] = $rec['item_count'];
}

/*EXPORT THE DATA*/
if (isset($_GET['act']) && $_GET['act'] == 'export'){

	$res = mysql_query('SELECT id_status, status_name FROM `status`');
	while ($rec = mysql_fetch_array($res)) 
	$statuses[$rec[0]] = $rec[1];


	$array = array($statuses[6], $statuses[4], $statuses[1], $statuses[2], $statuses[7], $statuses[3], $statuses[9], $statuses[5], $statuses[16], $statuses[8]);
	//echo $statuses[7];
	$array_id_status = array(6,4,1,2,7,3,9,5,16,8);
	
	$crlf = "\n";
	$content = 'Category';
	foreach ($array as $id_status => $status_name)
		$content .= ','.$status_name;
		$content .= ',Total'. $crlf;

	foreach ($categories as $id_category => $category_name){ // baris
		$content .= $category_name ;	
		$total = 0;

		foreach ($array_id_status as $id_status) {
			if (!isset($summary[$id_status][$id_category]))
				$summary[$id_status][$id_category] = 0;
			$content .= ','.$summary[$id_status][$id_category];
			$total += $summary[$id_status][$id_category];
			// total tiap kolom/kategori
			if (!isset($status_total[$id_status]))
				$status_total[$id_status] = 0;
			$status_total[$id_status] += $summary[$id_status][$id_category];
		}
		$content .= ',' . $total. $crlf;// total tiap baris/status	
	}

	$content .= 'Total';
	$grand_total = 0;
	foreach ($array_id_status as $id_status) {
		$grand_total += $status_total[$id_status];
		$content .= ',' . $status_total[$id_status];
	}
	$content .= ','.$grand_total;
	
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_tracking_by_category.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;	
}

/*END OF EXPORT THE DATA*/


echo <<<HEAD
<script>
function export_this(){
	location.href = "./?mod=report&sub=item&act=export&term=tracking&by=category";
}
</script>
<h3>Inventory Tracking Report</h3>
<table class="report" width="100%" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=11 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Category</th>
HEAD;

$array = array($statuses[6], $statuses[4], $statuses[1], $statuses[2], $statuses[7], $statuses[3], $statuses[9], $statuses[5], $statuses[16], $statuses[8]);
//echo $statuses[7];
$array_id_status = array(6,4,1,2,7,3,9,5,16,8);

foreach ($array as $id_status => $status_name)
	echo '<th>'.$status_name.'</th>';
	echo '<th>Total</th></tr>';


//echo "</tr>";
$row = 0;
foreach ($categories as $id_category => $category_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $category_name . '</td>';
	$total = 0;

	foreach ($array_id_status as $id_status) {
	$total_transferred = get_count_item_transferred($id_department, $id_category);
		if (!isset($summary[$id_status][$id_category]))
			$summary[$id_status][$id_category] = 0;
        if ($summary[$id_status][$id_category] > 0)
            echo '	
				<td align="center">
					<a class="statuslink" href="./?mod=report&sub=item&act=list_by_status&status='.$id_status.'&cat='.$id_category.'">'.$summary[$id_status][$id_category].'</a>
				</td>';
		//else if ($id_status == 16 && $summary[$id_status][$id_category] > 0)
		
		else if (($id_status == 16) && ($total_transferred > 0))
			echo '<td align="center">
					<a class="statuslink" href="./?mod=report&sub=item&act=list_by_status_transferred&id_category='.$id_category.'">'.$total_transferred.'</a>
				</td>';
		else
            echo '<td align="center">0</td>';//
			
		$total += $summary[$id_status][$id_category];
		// total tiap kolom/kategori
		if (!isset($status_total[$id_status]))
			$status_total[$id_status] = 0;
		$status_total[$id_status] += $summary[$id_status][$id_category];
	}
	echo '<td align="center">' . $total+=$total_transferred . '</td></tr>';// total tiap baris/status
}
// munculkan total tiap kolom
$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr '.$class.'><td style="text-align:left">Total</td>';
$grand_total = 0;
foreach ($array_id_status as $id_status) {
	$grand_total += $status_total[$id_status];
	echo '<td align="center">' . $status_total[$id_status] . '</td>';
}
echo '<td align="center">'.$grand_total.'</td></tr>';
echo'</table>';
?>
<br/>
<br/>

<?php
function  get_count_item_transferred($id_dept, $id_category){

	$query = "SELECT count(*) as total FROM item 
				
				WHERE id_category = '".$id_category."'
			";
				
	if($id_dept > 0){
		$query .= " AND id_owner= '$id_dept' AND id_department != '$id_dept'";
	}
	
	$rs = mysql_query($query);
	$rec = mysql_fetch_array($rs);
	
	return $rec['total'];

}
?>