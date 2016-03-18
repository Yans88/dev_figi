<?php


$this_year = date('Y');
$this_month = date('n');
$this_day = date('d');

$_user = !empty($_GET['id']) ? $_GET['id'] : 0;
$_year = !empty($_GET['y']) ? $_GET['y'] : $this_year;
$_month = !empty($_GET['m']) ? intval($_GET['m']) : $this_month;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_limit = RECORD_PER_PAGE;
$_start = 0;
$df = 'd-M-Y';
$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$users = get_user_list();
$username = $users[$_user];
$_mon = $month_names[$_month];

/*
$query  = "SELECT COUNT(*) FROM facility_book_view fb
			WHERE YEAR(FROM_UNIXTIME(dt_start)) = $_year AND MONTH(FROM_UNIXTIME(dt_start)) = $_month
			AND (status = 'CANCEL' OR status = 'DELETE') ";
$res = mysql_query($query);
$row = mysql_fetch_row($res);
$total_item = $row[0];
$total_page = ceil($total_item/$_limit);
if ($_page > 0)	$_start = ($_page-1) * $_limit;
if ($_page > $total_page) $_page = $total_page;
*/
$query  = "SELECT *, delete_remark reason, date_format(delete_timestamp, '%d-%b-%Y') delete_date 
			FROM facility_book_view fb  
            LEFT JOIN user u ON u.id_user = fb.id_user 
			LEFT JOIN facility_book_delete fbc ON fbc.id_book = fb.id_book 
			WHERE YEAR(FROM_UNIXTIME(dt_start)) = $_year AND MONTH(FROM_UNIXTIME(dt_start)) = $_month
			AND (status = 'CANCEL' OR status = 'DELETE') AND fb.id_user = $_user ";
            //LIMIT $_start, $_limit";
$res = mysql_query($query);
//echo mysql_error().$query;
$booking_users = array();
while ($rec = mysql_fetch_assoc($res)) {   
    $bookings[] = $rec;
}
	
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'No,Booked By,Booked Date,Request Date,Purpose,Repetition,Cancel Date,Cancel Remark'.$crlf;
	$no = 1;
    foreach ($bookings as $rec){ 
        $dt = date($df, $rec['dt_start']);
		$dtd = date($df, $rec['delete_date']);
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
			
			$content .= "$no,$rec[full_name],$dt,$bookedon,$rec[purpose],$repetition,$dtd,$rec[reason]$crlf";
        $no++;
	}
	$filename = "facility_book_cancel_by_user-$username-in-$_mon-$_year.csv";
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

<h2>Report Cancelled Facility Booking by User</h2>
<div style="width: 800px">
HEAD;
if (!empty($bookings)){
    echo <<<HEAD1
<form method="post">
<input type=hidden name=act>
</form>
<div style="float: clear; clear: both;">
<div class="leftcol" style="width: 700px"><h3 style="float: left">User "$username" in $_mon-$_year<h3></div>
<div class="" style="text-align:right"><a class="button" href="#" onclick="export_this()">Export</a></div>
</div>
<table class="report" width="800" cellpadding=2 cellspacing=1>
<tr><th width=20>No</th><th width=120>Booked By</th><th>Booked Date</th><th>Request Date</th><th>Purpose</th><th>Repetition</th><th>Cancel Date</th><th>Cancel Remark</th></tr>
HEAD1;
$row = 0;
foreach ($bookings as $rec){ // baris
    $row++;
//	print_r($rec);
	$class = ($row % 2 == 0) ? 'alt' : 'normal';
    if ($rec['status'] == 'CANCEL') $class .= ' cancelled';
	//$dtd = date('d-M-Y', $rec['delete_date']);
	$dtd = $rec['delete_date'];
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
			
	echo <<<REC
<tr class="$class">
    <td align="right">$row.</td>
    <td>$rec[full_name]</td>
    <td>$bd</td>
    <td>$bookedon</td>
    <td>$rec[purpose]</td>
    <td>$repetition</td>
    <td>$dtd</td>
    <td>$rec[reason]</td>
</tr>
REC;
}
if ($total_page > 1){
    echo '<tr ><td colspan=7 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=report&sub=facility&term=usage&by=user_detail&y='.$_year.'&m='.$_month.'&page=');
    echo  '</td></tr>';

}
echo'</table>';

} else { // empty $booking_users
    echo '<div class="error">Data is not available! </div>';
    
}
?>
</div>
<br/><br/>