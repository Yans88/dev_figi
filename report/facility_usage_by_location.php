<?php


$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');

$_facility = 0;
if (!empty($_GET['l'])) $_facility = $_GET['l'];
$_dtfilter = date('d-M-Y');
if (!empty($_GET['d'])){
	if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $_GET['d'], $matches)){
		if ($matches[1]<10) $matches[1] = "0$matches[1]";
		$_dtfilter = $matches[1].'-'.$short_month_names[$matches[2]-1].'-'.$matches[3];
	}
}

$_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : $_facility;
$_username = !empty($_POST['username']) ? $_POST['username'] : null;
$_dtfilter = !empty($_POST['dt_filter']) ? $_POST['dt_filter'] : $_dtfilter;
$_dtrange = !empty($_POST['dt_range']) ? $_POST['dt_range'] : $_dtfilter;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$facilities = get_facility_list();
/*
if ($_facility == 0 && count($facilities)>0){
    $keys = array_keys($facilities);
    $_facility = $keys[0];
}
*/
$facility_name = 'All Facilities';
if ($_facility>0)
    $facility_name = $facilities[$_facility];
/*
$query  = "SELECT COUNT(*) FROM facility_book_view fb ";
if ($_facility>0) $query .= ' WHERE id_facility = '.$_facility;
$res = mysql_query($query);
$col = mysql_fetch_row($res);
$book_count=$col[0];

$query  = "SELECT dt_start, dt_end, full_name, purpose, fullday, remark   
			FROM facility_book_view fb ";
if ($_facility>0) $query .= ' WHERE id_facility = '.$_facility;
$res = mysql_query($query);

while ($rec = mysql_fetch_array($res)) {   
	$result[] = $rec;
}
$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
*/

//extract date
$filter_tm = 0;
if (preg_match('/(\d{1,2})-(\w{3})-(\d{4})/', $_dtfilter, $matches)){
	$filter_tm = mktime(0, 0, 0, array_search($matches[2], $short_month_names)+1, $matches[1], $matches[3]);
}
$range_tm = 0;
if (preg_match('/(\d{1,2})-(\w{3})-(\d{4})/', $_dtrange, $matches)){
	$range_tm = mktime(23, 59, 59, array_search($matches[2], $short_month_names)+1, $matches[1], $matches[3]);
}
//echo date('Ymd', $filter_tm). '---'.date('Ymd', $range_tm);
$result = array();
$fmt = '%d-%b-%Y';
$book_count = 0;
if ($_facility > 0){
	$query = "SELECT DISTINCT fbw.id_book, fbw.*, fbi.*, DATE(FROM_UNIXTIME(fbi.start)) booked_date FROM facility_book_instances fbi 
				LEFT JOIN facility_book_view fbw ON fbw.id_book = fbi.id_book
				WHERE (DATE_FORMAT(FROM_UNIXTIME(fbi.start), '$fmt') = '$_dtfilter' OR 
				DATE_FORMAT(FROM_UNIXTIME(fbi.end), '$fmt') = '$_dtfilter')"; 
				
	$query = "SELECT DISTINCT fbw.id_book, fbw.*, fbi.*, DATE(FROM_UNIXTIME(fbi.start)) booked_date, 
				SUM(fbi.end-fbi.start) sumtime, period_duration duration  
				FROM facility_book_instances fbi 
				LEFT JOIN facility_book_view fbw ON fbw.id_book = fbi.id_book
				LEFT JOIN facility f ON fbw.id_facility = f.id_facility 
				WHERE fbi.start >= $filter_tm AND fbi.start <= $range_tm"; 
	/*
	$query = "SELECT *, DATE(FROM_UNIXTIME(dt_start)) booked_date FROM facility_book_view fbw 
				WHERE (DATE_FORMAT(FROM_UNIXTIME(dt_start), '$fmt') = '$_dtfilter' OR 
				DATE_FORMAT(FROM_UNIXTIME(dt_start), '$fmt') = '$_dtfilter' ) AND status IN ('BOOK') "; 
	*/
	if ($_facility > 0) $query .= ' AND fbw.id_facility = '.$_facility;
	if (!empty($_username)){
		$userid = get_user_id_by_fullname($_username);
		if ($userid > 0)
			$query .= ' AND id_user = ' . $userid ;
	}
	$query .= ' GROUP BY dt_start, fbw.id_book	ORDER BY book_date ';
	$res = mysql_query($query);
	//echo $query . mysql_error();
	if ($res)
		while ($rec = mysql_fetch_array($res)) $result[$rec['booked_date']][] = $rec;
	$book_count = count($result);
}
//print_r($result);


if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'Date,Time,Repetition,Booked By,Booked On,Purpose,Special Requirements,Periods'.$crlf;
	foreach ($result as $bd => $recs){
		foreach($recs as $rec){
			$dts = date('d-M-Y H:i', $rec['dt_start']);
			$dte = date('d-M-Y H:i', $rec['dt_end']);
			if ($rec['fullday'] > 0) $delta = 24 * 60;
			else $delta = ($rec['dt_end']-$rec['dt_start']) / 60;
			$evts = date('Y-m-d', $rec['dt_start']);
			$evte = date('Y-m-d', $rec['dt_end']);
			$oneday = false;
			if ($evts == $evte) { 
				$oneday = true;
				$bd = date('D M d', $rec['dt_start']);
			}
			else 
				$bd = date('D M d', $rec['dt_start']) . ' - ' .date('D M d', $rec['dt_end']);
			if ($rec['fullday'] == 1) $delta = 'All day';
			else if ($oneday) $delta =  date('g:ia', $rec['dt_start']) . ' - ' .date('g:ia', $rec['dt_end']);
			$bookedon = date('M d', $rec['book_date']);
			$repetition = $repetition_labels[$rec['repetition']];
			$periods = round($rec['sumtime']/($rec['duration']*60));
			$content .= "$bd,$delta,$repetition,$rec[full_name],$bookedon,$rec[purpose],$rec[remark],$periods$crlf";
	
		}
	}
	
    $location_name = preg_replace('/ /', '-', $facility_name);
    $filename = 'facility_usage_for-'.$location_name;
    if (!empty($_username))
    	$filename .= '-by-' .$_username;
    $filename.= '-on-'.$_dtfilter.'.csv';
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

$facilities = array('0' => '-- select facility --')+$facilities;
$location_combo = build_combo('id_facility', $facilities, $_facility);
$filter_by_name = null;
if (!empty($_username))
	$filter_by_name = ' by "' . $_username . '"'	;

echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}

function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("user/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				
				var pos =  $('#username').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({left:pos.left});
				
			} else
                $('#suggestions').fadeOut();
		});
	}
}

</script>

<style type="text/css">
  .AnyTime-pkr { z-index: 9999 }
	#suggestions { margin-top: 1px; }
	#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<h2>Facility Usage List Filtered by Location</h2>
<form method="post">
<input type=hidden name=act>

<p style="color: #fff">
Location $location_combo &nbsp;
Date <input type="text" id="dt_filter" name="dt_filter" class="searchinput" size=10 value="$_dtfilter">  &nbsp;
 - &nbsp; <input type="text" id="dt_range" name="dt_range" class="searchinput" size=10 value="$_dtrange">  &nbsp;
<script>
    $('#dt_filter').AnyTime_noPicker().AnyTime_picker({format: "%d-%b-%Y"});
    $('#dt_range').AnyTime_noPicker().AnyTime_picker({format: "%d-%b-%Y"});
</script>
<br/>
User <input type="text" id="username" name="username" class="searchinput" size=15  value="$_username"  
    onKeyUp="suggest(this, this.value);" onBlur="fill('username', this.value);" autocomplete=off > &nbsp;
    <input type="submit" name="display" value="Display"> &nbsp;
    <input type="button" id="viewcalendar" value="View in Calendar">
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</p>
</form>
HEAD;
if ($book_count>0){
	echo <<<THEAD
<table class="report" style="min-width: 800px" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>Booking list for "$facility_name" $filter_by_name on $_dtfilter<h3></td>
	<td colspan=4 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr>
    <th width="140">Date</th>
    <th width="60">Time</th>
    <th>Repetition</th>
    <th>Booked By</th>
	<th>Booked On</th>
    <th>Purpose</th>
    <th>Special Requirements</th>
    <th>Periods</th>
</tr>
THEAD;
	$df = 'd-M-Y';
	$row = 0;
    foreach ($result as $bd => $recs){
    	//$prebd = $bd;
    	foreach($recs as $rec){
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
			//if ($bd == $prebd) $bd = null; else { $prebd = $bd; }
			$rowclass = (($row++ % 2) == 0) ? 'normal' : 'alt';
			echo '<tr class="'.$rowclass.'">';
			echo '<td align="left" >'.$bd.'</td>';
			/*
			if ($bd != null){ 
				if (count($recs)>1)
					echo '<td align="left" rowspan="'.count($recs).'">'.$bd.'</td>';
				else
					echo '<td align="left" >'.$bd.'</td>';
			}
			*/
			$bookedon = date($df.' g:ia', $rec['book_date']);
			if (date('Y',$rec['book_date'])!=date('Y'))
				$bookedon = date('Y ',$rec['book_date']). $bookedon;
			$repetition = $repetition_labels[$rec['repetition']];
			if ($rec['repetition'] != 'NONE'){
				if (!empty($rec['dt_last']))
					$repetition .= ' until ' . date($df, $rec['dt_last']);
				else
					$repetition .= ' forever';
			}		
			$periods = round($rec['sumtime']/($rec['duration']*60));
			echo <<<ROW
			
			<td align="center">$delta</td>
			<td align="center">$repetition</td>
			<td>$rec[full_name]</td>
			<td>$bookedon</td>
			<td>$rec[purpose]</td>
			<td>$rec[remark]</td>
			<td>$periods</td>
		</tr>
ROW;
		}
    }
} else if ($_facility>0){
	
    echo '<tr class="normal"><td colspan=7 align="center">Data is not available!</td></tr>';
}

?>
</table>
<br>&nbsp;
<br>&nbsp;
<script>

$('#viewcalendar').click(function(e){
	var dconv = new AnyTime.Converter({format: '%d-%b-%Y'});
	var dt = dconv.parse($('#dt_filter').val());
	var ymd = dt.getFullYear()+'-'+(dt.getMonth()+1)+'-'+dt.getDate();
	location.href = './?mod=facility&sub=booking&act=list&d='+ymd;
});
</script>