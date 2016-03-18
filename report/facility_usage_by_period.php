<?php


$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$facilities = get_facility_list();
	
$query  = "SELECT MONTH(from_unixtime(book_date)) book_month, id_facility, count(id_book) book_count 
			FROM facility_book_view fb 
			WHERE YEAR(from_unixtime(book_date)) = $_year ";
$query  = "SELECT count(DISTINCT fbi.id_book) book_count, MONTH(from_unixtime(fbi.start)) book_month, id_facility 
			FROM facility_book_instances fbi 
			LEFT JOIN facility_book_view fb ON fb.id_book = fbi.id_book 
			WHERE YEAR(from_unixtime(book_date)) = $_year ";			
$query .= " GROUP BY book_month, id_facility";
$res = mysql_query($query);
//echo $query.mysql_error();
while ($rec = mysql_fetch_array($res)) {   
	$summary[$rec['book_month']][$rec['id_facility']] = $rec['book_count'];
}

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

	foreach ($facilities as $id_facility => $facility_name){ // baris
		$content .= $facility_name ;	
		$total = 0;

		for ($id_month = 1; $id_month <= 12; $id_month++){
			if (!isset($summary[$id_month][$id_facility]))
				$summary[$id_month][$id_facility] = 0;
			$content .= ','.$summary[$id_month][$id_facility];
			$total += $summary[$id_month][$id_facility];
			// total tiap kolom/kategori
			if (!isset($month_total[$id_month]))
				$month_total[$id_month] = 0;
			$month_total[$id_month] += $summary[$id_month][$id_facility];
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
	header("Content-Disposition: attachment; filename=facility_usage_by_facility.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

$year_combo = build_combo('y', $years, $_year);

echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>

<h3>Facility (Periods) Usage Report</h3>
<form method="post">
<input type=hidden name=act>

<p style="color: #fff" class="center">
Select a year $year_combo <input type="submit" name="display" value="Display">
</p>
</form>
<table class="report middle" width=600 cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Facility<h3></td>
	<td colspan=10 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Facility</th>
HEAD;
for ($id_month = 1; $id_month <= 12; $id_month++){
	echo '<th>'.substr($month_names[$id_month], 0, 3).'</th>';
}
echo '<th>Total</th></tr>';

$row = 0;
foreach ($facilities as $id_facility => $facility_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $facility_name . '</td>';
	$total = 0;

	for ($id_month = 1; $id_month <= 12; $id_month++){
		if (!isset($summary[$id_month][$id_facility]))
			$summary[$id_month][$id_facility] = 0;
        if ($summary[$id_month][$id_facility] > 0){
            $link = "?mod=report&sub=facility&act=view&term=usage&by=facility&id=$id_facility&ym=$_year-$id_month";
            echo '<td align="center"><a href="'.$link.'">'.$summary[$id_month][$id_facility].'</a></td>';
		} else
            echo '<td align="center">'.$summary[$id_month][$id_facility].'</td>';
		$total += $summary[$id_month][$id_facility];
		// total tiap kolom/kategori
		if (!isset($month_total[$id_month]))
			$month_total[$id_month] = 0;
		$month_total[$id_month] += $summary[$id_month][$id_facility];
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
