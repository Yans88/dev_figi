<?php


$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$users = get_user_list();

$query  = "SELECT DISTINCT MONTH(FROM_UNIXTIME(dt_start)) book_month, u.id_user, u.full_name, count(id_book) book_count 
			FROM user u 
            LEFT JOIN facility_book_view fb ON u.id_user = fb.id_user 
			WHERE YEAR(FROM_UNIXTIME(dt_start)) = $_year AND (status = 'CANCEL' OR status = 'DELETE') ";
$query .= " GROUP BY book_month, u.id_user ORDER BY book_count DESC";
/*
$query  = "SELECT MONTH(FROM_UNIXTIME(start)) book_month, fb.id_user, fb.full_name, count(fbi.id_book) book_count 
            FROM facility_book_view fb 
            LEFT JOIN facility_book_instances fbi ON fbi.id_book = fb.id_book 
			WHERE YEAR(FROM_UNIXTIME(start)) = $_year AND (status IN ('CANCEL', 'DELETE')) ";
$query .= " GROUP BY book_month, fb.id_user ORDER BY book_count DESC";
*/
$res = mysql_query($query);
//echo mysql_error().$query;
$booking_users = array();
while ($rec = mysql_fetch_array($res)) {   
	$summary[$rec['book_month']][$rec['id_user']] = $rec['book_count'];
    $booking_users[$rec['id_user']] = $rec['full_name'];
}

$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
	
	
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'Username';
	for ($id_month = 1; $id_month <= 12; $id_month++)
		$content .= ','.substr($month_names[$id_month], 0, 3);
	$content .= ',Total'. $crlf;

	foreach ($booking_users as $id_user => $user_name){ // baris
		$content .= $user_name ;	
		$total = 0;

		for ($id_month = 1; $id_month <= 12; $id_month++){
			if (!isset($summary[$id_month][$id_user]))
				$summary[$id_month][$id_user] = 0;
			if ($summary[$id_month][$id_user]==0) $val = 0;
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
	$filename = 'facility_book_cancel_by_user-'.$_year.'.csv';
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=\"$filename\"");
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

<h3>Report Cancelled Booking</h3>
<form method="post">
<input type=hidden name=act>
<p style="color: #fff" class="center">
Select a year $year_combo <input type="submit" name="display" value=" Display ">
</p>
</form>
HEAD;

if (!empty($summary)){
    echo <<<HEAD1
<br>
<table class="report middle" width="800" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=12><h3>In Terms Of User<h3></td>
	<td colspan=3 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>User</th>
HEAD1;
for ($id_month = 1; $id_month <= 12; $id_month++){
	echo '<th>'.substr($month_names[$id_month], 0, 3).'</th>';
}
echo '<th>Total</th></tr>';
$row = 0;
foreach ($booking_users as $id_user => $user_name){ // baris

	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $user_name . '</td>';
	$total = 0;

	for ($id_month = 1; $id_month <= 12; $id_month++){
		if (!isset($summary[$id_month][$id_user]))
			$summary[$id_month][$id_user] = 0;
        if ($summary[$id_month][$id_user]==0) $val = 0;
        
        else $val = '<a href="./?mod=report&sub=facility&term=cancel&by=user_detail&y='.$_year.'&m='.$id_month.'&id='.$id_user.'" style="font-weight: bold">'.$summary[$id_month][$id_user].'</a>';
		echo '<td align="center">'.$val.'</td>';
		//echo '<td align="center">'.$summary[$id_month][$id_user].'</td>';
		$total += $summary[$id_month][$id_user];
		// total tiap kolom/kategori
		if (!isset($month_total[$id_month]))
			$month_total[$id_month] = 0;
		$month_total[$id_month] += $summary[$id_month][$id_user];
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
} else { // empty $booking_users
    echo '<div class="error center">Data is not available! </div>';
}
?>
