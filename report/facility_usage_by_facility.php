<?php


$this_year = date('Y');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;
$_facility = !empty($_POST['id']) ? $_POST['id'] : 0;
if ($_facility == 0)
    $_facility = !empty($_GET['id']) ? $_GET['id'] : 0;
$_ym = !empty($_POST['ym']) ? $_POST['ym'] : null;
if ($_ym == null)
    $_ym = !empty($_GET['ym']) ? $_GET['ym'] : date('Y-n');
list($_year, $_month) = explode('-', $_ym);
$_time = mktime(0, 0, 0, $_month, 1, $_year);
$_limit = RECORD_PER_PAGE;
$_start = 0;


$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$facilities = get_facility_list();
$facility = get_facility($_facility);
$facility_name = $facility['facility_no'];
$month_year = date('M Y', $_time);
$fmt = '%Y-%c';			
/*
$query = "SELECT COUNT(DISTINCT fbw.id_book), fbw.*, fbi.*, DATE(FROM_UNIXTIME(fbi.start)) booked_date FROM facility_book_instances fbi 
			LEFT JOIN facility_book_view fbw ON fbw.id_book = fbi.id_book
			WHERE (DATE_FORMAT(FROM_UNIXTIME(fbi.start), '$fmt') = '$_ym' OR 
			DATE_FORMAT(FROM_UNIXTIME(fbi.end), '$fmt') = '$_ym')  AND id_facility = $_facility"; 
$res = mysql_query($query);
//echo $query.mysql_error();

$row = mysql_fetch_row($res);
$total_item = $row[0];
$total_page = ceil($total_item/$_limit);
if ($_page > 0)	$_start = ($_page-1) * $_limit;
if ($_page > $total_page) $_page = $total_page;
*/
$df = 'd-M-Y';

$query = "SELECT DISTINCT fbw.*, fbi.*, DATE(FROM_UNIXTIME(fbi.start)) booked_date FROM facility_book_instances fbi 
			LEFT JOIN facility_book_view fbw ON fbw.id_book = fbi.id_book
			WHERE (DATE_FORMAT(FROM_UNIXTIME(fbi.start), '$fmt') = '$_ym')  AND id_facility = $_facility  
            GROUP BY fbi.id_book ORDER BY book_date";
$res = mysql_query($query);
//echo mysql_error().$query;
$booking_users = array();
while ($rec = mysql_fetch_array($res)) {   
    $bookings[] = $rec;
}
	
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'No,Booked By,Booked Date,Booked On,Purpose,Repetition,Remark'.$crlf;
	$no = 1;
    foreach ($bookings as $rec){ 
        $dt = date('d-M-Y', $rec['dt_start']);
		$dts = date($df.' H:i', $rec['dt_start']);
		$dte = date($df.' H:i', $rec['dt_end']);
		if ($rec['fullday'] > 0) $delta = 24 * 60;
		else $delta = ($rec['dt_end']-$rec['dt_start']) / 60;
		$evts = date($df, $rec['dt_start']);
		$evte = date($df, $rec['dt_end']);
		$oneday = false;
		if ($evts == $evte) { 
			$oneday = true;
			$bd = date('d-M D', $rec['dt_start']);
		}
		else 
			$bd = date('d-M D', $rec['dt_start']) . ' - ' .date('D M d', $rec['dt_end']);
		if ($rec['fullday'] == 1) $delta = 'All day';
		else if ($oneday) $delta =  date('g:ia', $rec['dt_start']) . ' - ' .date('g:ia', $rec['dt_end']);
		if ($bd == $prebd) $bd = null;
		else { $prebd = $bd; }
		
	
		$bookedon = date($df.' g:ia', $rec['book_date']);
		if (date('Y',$rec['book_date'])!=date('Y'))
			$bookedon = date('Y ',$rec['book_date']). $bookedon;
		$repetition = $repetition_labels[$rec['repetition']];
		if ($rec['repetition'] != 'NONE')
			if (!empty($rec['dt_last']))
				$repetition .= ' until ' . date($df, $rec['dt_last']);
			else
				$repetition .= ' forever';            
        $content .= "$no,$rec[full_name],$bd,$bookedon,$rec[purpose],$repetition,$rec[remark]$crlf";
        $no++;
	}
	
	$filename = "facility_usage_by_facility-$facility_name-in-$month_year.csv";
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


echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.submit();
}
</script>

<h2>
Facility Usage Report by Facility<br/>
</h2>

<div style="width: 800px">
HEAD;
if (!empty($bookings)){
    echo <<<HEAD1
<form method="post">
<input type=hidden name=act>
</form>
<div style="float: clear; clear: both;">
<div class="leftcol" style="width: 700px"><h3 style="float: left">Facility "$facility_name"	in $month_year<h3></div>
<div class="" style="text-align:right"><a class="button" href="#" onclick="export_this()">Export</a></div>
</div>
<table class="report" width="800" cellpadding=2 cellspacing=1>
<tr>
	<th width=20>No</th>
	<th>Booked By</th>
	<th>Booked Date</th>
	<th>Request Date</th>
	<th>Purpose</th>
	<th>Repetition</th>
	<th>Remark</th>
</tr>
HEAD1;
$row = 0;

foreach ($bookings as $rec){ // baris
    $row++;
	$class = ($row % 2 == 0) ? 'alt' : 'normal';
    if ($rec['status'] == 'CANCEL') $class .= ' cancelled';
    $dt = date('d-M-Y', $rec['dt_start']);
	$dts = date($df.' H:i', $rec['dt_start']);
	$dte = date($df.' H:i', $rec['dt_end']);
	if ($rec['fullday'] > 0) $delta = 24 * 60;
	else $delta = ($rec['dt_end']-$rec['dt_start']) / 60;	
	$evts = date($df, $rec['dt_start']);
	$evte = date($df, $rec['dt_end']);
	$oneday = false;
	if ($evts == $evte) { 
		$oneday = true;
		$bd = date('d-M-Y', $rec['dt_start']);

	}
	else 
		$bd = date('d-M-Y g:ia', $rec['dt_start']) . ' - ' .date('d-M-Y g:ia', $rec['dt_end']);
	if ($rec['fullday'] == 1) $bd .= ', all day';
	else if ($oneday) $bd .=  date(', g:ia', $rec['dt_start']) . ' - ' .date('g:ia', $rec['dt_end']);
	if ($bd == $prebd) $bd = null;
	else { $prebd = $bd ; }
	

	$bookedon = date($df.' g:ia', $rec['book_date']);
	if (date('Y',$rec['book_date'])!=date('Y'))
		$bookedon = date('Y ',$rec['book_date']). $bookedon;
	$repetition = $repetition_labels[$rec['repetition']];
	if ($rec['repetition'] != 'NONE'){
		
		if ($rec['repetition'] == 'WEEKLY'){
			$wds = explode(',', $rec['wd_start']);
			$wdls = array();
			foreach ($wds as $wd)
				$wdls[] = $short_day_names[$wd];
			if (count($wdls)>0)
				$repetition .= '('.implode(',', $wdls).')';
		}
		
		if (!empty($rec['dt_last']))
			$repetition .= ' until ' . date($df, $rec['dt_last']);
		else
			$repetition .= ' forever';    
	}
	echo <<<REC
<tr class="$class">
    <td align="right">$row.</td>
    <td>$rec[full_name]</td>
    <td>$bd</td>
    <td>$bookedon</td>
    <td>$rec[purpose]</td>
    <td>$repetition</td>
    <td>$rec[remark]</td>
</tr>
REC;
}
/*
if ($total_page > 1){
    echo '<tr ><td colspan=5 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=report&sub=facility&term=usage&by=user_detail&y='.$_year.'&m='.$_month.'&page=');
    echo  '</td></tr>';

}
*/
echo'</table>';

} else { // empty $booking_users
    echo '<div class="error">Data is not available! </div>';
    
}
?>
</div>
<br/><br/>