<?php


$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$users = get_user_list();
	
$query  = "SELECT MONTH(FROM_UNIXTIME(dt_start)) book_month, u.id_user, u.full_name, count(id_book) book_count 
			FROM user u 
            LEFT JOIN facility_book_view fb ON u.id_user = fb.id_user 
			WHERE YEAR(FROM_UNIXTIME(dt_start)) = $_year   AND (status = 'BOOK' OR status = 'COMMENCE') ";
			
$query  = "SELECT count(DISTINCT fbi.id_book) book_count, MONTH(from_unixtime(fbi.start)) book_month, fb.id_user, fb.full_name, fbi.start  
			FROM facility_book_instances fbi 
			LEFT JOIN facility_book_view fb ON fb.id_book = fbi.id_book 
			WHERE YEAR(from_unixtime(fbi.start)) = $_year AND status IN('BOOK','COMMENCE')";			
$query .= " GROUP BY book_month, fb.id_user ORDER BY book_count DESC";
$res = mysql_query($query);
//echo $query.mysql_error();
$booking_users = array();
while ($rec = mysql_fetch_array($res)) {   
	$summary[$rec['book_month']][$rec['id_user']] = $rec['book_count'];
    $booking_users[] = array($rec['id_user'], $rec['full_name']);
}

//print_r($summary);
$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
	
	
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'Facility';
	for ($id_month = 1; $id_month <= 12; $id_month++)
		$content .= ','.substr($month_names[$id_month], 0, 3);
	$content .= ',Total'. $crlf;

	foreach ($users as $id_user => $user_name){ // baris
		$content .= $user_name ;	
		$total = 0;

		for ($id_month = 1; $id_month <= 12; $id_month++){
			if (!isset($summary[$id_month][$id_user]))
				$summary[$id_month][$id_user] = 0;
			$content .= ','.$summary[$id_month][$id_user];
			$total += $summary[$id_month][$id_user];
			// total tiap kolom/kategori
			if (!isset($month_total[$id_month]))
				$month_total[$id_month] = 0;
			$month_total[$id_month] += $summary[$id_month][$id_user];
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
	header("Content-Disposition: attachment; filename=facility_usage_by_user.csv");
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

<h2>Report Facility Booking by User</h2>
<form method="post">
<input type=hidden name=act>

<p style="color: #fff">
Select a year $year_combo <input type="submit" name="display" value=" Display ">
</p>
</form>
<div style="width: 800px">
HEAD;
if (!empty($booking_users)){
    echo <<<HEAD1
<div style="float: clear; clear: both;">
<div class="leftcol" style="width: 700px"><h3 style="float: left">In Terms Of User<h3></div>
<div class="" style="text-align:right"><a class="button" href="#" onclick="export_this()">Export</a></div>
</div>
<table class="report" width="800" cellpadding=2 cellspacing=1>
<tr><th>User</th>
HEAD1;
for ($id_month = 1; $id_month <= 12; $id_month++){
	echo '<th>'.substr($month_names[$id_month], 0, 3).'</th>';
}
echo '<th>Total</th></tr>';
//rint_r($booking_users);
$row = 0;
foreach ($users as $id_user => $user_name){ // baris
    
    
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $user_name . '</td>';
	$total = 0;

	for ($id_month = 1; $id_month <= 12; $id_month++){
		if (!isset($summary[$id_month][$id_user]))
			$summary[$id_month][$id_user] = 0;
        if ($summary[$id_month][$id_user]==0) $val = 0;
        else $val = '<a href="./?mod=report&sub=facility&term=usage&by=user_detail&y='.$_year.'&m='.$id_month.'&id='.$id_user.'" style="font-weight: bold">'.$summary[$id_month][$id_user].'</a>';
		echo '<td align="center">'.$val.'</td>';
		$total += $summary[$id_month][$id_user];
		// total tiap kolom/kategori
		if (!isset($month_total[$id_month]))
			$month_total[$id_month] = 0;
		$month_total[$id_month] += $summary[$id_month][$id_user];
	}
	echo '<td align="center" class="total_col" style="font-weight: bold">' . $total . '</td></tr>';// total tiap baris/status
}
// munculkan total tiap kolom
$row++;
$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
echo '<tr '.$class.'><td style="text-align:left;" class="total_row" >Total</td>';
$grand_total = 0;
for ($id_month = 1; $id_month <= 12; $id_month++) {
	$grand_total += $month_total[$id_month];
	echo '<td style="font-weight: bold" align="center" class="total_row">' . $month_total[$id_month] . '</td>';
}
echo '<td align="center" class="total_row" style="font-weight: bold">'.$grand_total.'</td></tr>';
echo'</table>';

} else { // empty $booking_users
    echo '<div class="error">Data is not available! </div>';
    
}
?>
</div>
&nbsp;<br>
&nbsp;<br>