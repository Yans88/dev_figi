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

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$facilities = get_facility_list();
	
function count_daily(&$res, $rec, $dtend, $fdt = 0){
    $dt = $rec['date_start_t'];
    while ($dt < $dtend){
        $cal_code = date('Ynj', $dt);
        $rec['cur_event_date'] = date('D, d M Y', $dt);
        if ($fdt > 0){
            if ($dt == $fdt){
                $res[$cal_code][] = $rec;
                break;
            }
        } else
            $res[$cal_code][] = $rec;
        $dt = date_add_day($dt, $rec['repeat_interval']);
    }
}


function count_monthly(&$res, $rec, $dtend, $fdt = 0){
    $dt = $rec['date_start_t'];
    $dte = $rec['date_finish_t'];
    $delta = $dte-$dt;
    $dom = date('md', $dt);
    while ($dt < $dtend){
        $sdt = $dt;
        $long = $dt+$delta;
        while ($dt <= $long){
            $cal_code = date('Ynj', $dt);
            $rec['cur_event_date'] = date('D, d M Y', $dt);
            if ($fdt > 0){
                if ($dt == $fdt){
                    $res[$cal_code][] = $rec;
                    break;
                }
            } else
                $res[$cal_code][] = $rec;
            $dt = date_add_day($dt, 1);
        }
        
        $dt = date_add_months($sdt, $rec['repeat_interval']);
    }
}

function count_weekly(&$res, $rec, $dtend, $fdt = 0){
    $dt = $rec['date_start_t'];
    $dte = $rec['date_finish_t'];
    $dte  = date_add_day($dt, 6);
    $delta = $dte-$dt;
    $options = array();
    if (!empty($rec['repeat_option']))
        $options = explode(',', $rec['repeat_option']);
    $dw = date('w', $dt);
    if (empty($options))
        $options = array($dw);
    
    while (!in_array($dw, $options)){ // find exptected dow
        $dt = date_add_day($dt, 1);
        $dw = date('w', $dt);
    }
    while ($dt < $dtend){
        $sdt = $dt;
        $long = $dt+$delta;
        while ($dt <= $long){
            $dw = date('w', $dt);
            if (in_array($dw, $options)){
                $cal_code = date('Ynj', $dt);
                $rec['cur_event_date'] = date('D, d M Y', $dt);
                if ($fdt > 0){
                    //echo "$dt : $fdt<br>";
                    if ($dt >= $fdt){
                        $res[$cal_code][] = $rec;
                        break;
                    }
                } else
                    $res[$cal_code][] = $rec;
            }
            $dt = date_add_day($dt, 1);
            //break;
        }
        
        $dt = date_add_day($sdt, $rec['repeat_interval']*7);
    }
}

$query = "SELECT MONTH(book_date) book_month, id_facility, count(id_book) book_count 
			FROM facility_book_view fb 
			WHERE YEAR(book_date) = $_year AND MONTH(book_date) = $_month AND id_facility = '$_facility' 
			GROUP BY book_month, id_facility";
            
$query = "SELECT *, date_format(date_start, '%Y%c%e') cal_code, UNIX_TIMESTAMP(repeat_until) repeat_until_t, 
            UNIX_TIMESTAMP(date_start) date_start_t, UNIX_TIMESTAMP(date_finish) date_finish_t, 
            date_format(date_start, '%d-%b-%Y') date_start_fmt, date_format(date_finish, '%d-%b-%Y') date_finish_fmt,  
            date_format(time_start, '%H:%i') time_start_fmt, date_format(time_finish, '%H:%i') time_finish_fmt  
            FROM facility_book_view ce 
            WHERE date_format(ce.date_start, '%Y-%c') <= '$_year-$_month' AND
            status IN ('BOOK', 'COMMENCE') ";//AND date_format(date_finish, '%Y-%c') <= '$y-$m'
            
$res = mysql_query($query);
//echo $query.mysql_error();


$start_date_of_the_month = mktime(0, 0, 0, $_month, 1, $_year);
$last_date_of_the_month = mktime(0, 0, 0, $_month, date('t', $start_date_of_the_month), $_year);
while ($rec = mysql_fetch_array($res)) {  
	//$summary[$rec['book_month']][$rec['id_facility']] = $rec['book_count'];
    $cal_code = $rec['cal_code'];
    //if (!is_array($result[$cal_code])) $result[$cal_code] = array();
    
    $dtend = $rec['repeat_until_t'];
    if (empty($dtend) || $dtend > $last_date_of_the_month)
        $dtend = $last_date_of_the_month;
    if ($rec['repetition'] == 1) { // daily 
        count_daily($result, $rec, $dtend);
    } else
    if ($rec['repetition'] == 2) { // weekly
        count_weekly($result, $rec, $dtend);
    } else
    if ($rec['repetition'] == 3) { // monthly
        count_monthly($result, $rec, $dtend);
    } else {
    
        $rec['cur_event_date'] = convert_date('D, d M Y', $rec['date_start']);
        $result[$cal_code][] = $rec;
    }
}
// calculate
$summary = array();
foreach($result as $cal_code => $recs){
    foreach ($recs as $rec){
        $hours = ceil((strtotime($rec['time_finish'])-strtotime($rec['time_start'])) / (60*60));
        $dt = strtotime($rec['cur_event_date']);
        $id_month = date('n', $dt);
        if (!isset($summary[$id_month])) $summary[$id_month] = array();
        if (!isset($summary[$id_month][$hours])) $summary[$id_month][$hours] = 0;
        $summary[$id_month][$hours]++;
    }
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
	header("Content-Disposition: attachment; filename=item_warranty_by_facility.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

$year_combo = build_combo('y', $years, $this_year);
$facility_combo = build_combo('id', $facilities, $_facility);
$timesheet = get_timesheets($_facility);

echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>
<style type="text/css">
  #ym { /*background-image:url("images/cal.jpg");*/
    background-position:right center; background-repeat:no-repeat; font-size: 16px;
    border:0;color:#fff;font-weight:bold;background-color: #103821;}
</style>

<h2>Facility Usage Report</h2>
<form method="Post">
<input type=hidden name=act>

<p style="color: #fff">
Select month: <input type="text" size=8 id="ym" name="ym" value="$_ym">
facility: $facility_combo 
<input type="submit" name="display" value=" Display ">
<script type="text/javascript">
    $('#ym').AnyTime_picker({format: "%Y-%c"});
</script>
</p>
</form>
<div style="width: 800px">
<div id="leftcol"><h3 style="float: left">In Terms of Hours in Month<h3></div>
<div id="rightcol" align="right"><a class="button" href="#" onclick="export_this()">Export</a></div>
<table class="report" width="800" cellpadding=2 cellspacing=1>
<tr><th>Hours</th>
HEAD;
for ($id_month = 1; $id_month <= 12; $id_month++)
	echo '<th>'.substr($month_names[$id_month], 0, 3).'</th>';
echo '<th>Total</th></tr>';

$row = 0;
for($id_date = 1; $id_date <= 8; $id_date++){ // baris
    $row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
	echo '<tr '.$class.'><td>' . $id_date. '</td>';
	$total = 0;

	for ($id_month = 1; $id_month <= 12; $id_month++){
		if (!isset($summary[$id_month][$id_date]))
			$summary[$id_month][$id_date] = 0;
		echo '<td align="center">'.$summary[$id_month][$id_date].'</td>';
		$total += $summary[$id_month][$id_date];
		// total tiap kolom/kategori
		if (!isset($month_total[$id_month]))
			$month_total[$id_month] = 0;
		$month_total[$id_month] += $summary[$id_month][$id_date];
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
</div>