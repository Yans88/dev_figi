<?php

$this_time = time();
$early_time = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
$this_week = date('W');
$_week = isset($_POST['w']) ? $_POST['w'] : false;
if (!$_week) $_week = !empty($_GET['w']) ? $_GET['w'] : null;// $this_week;
$_year = !empty($_POST['y']) ? $_POST['y'] : false;
if (!$_year) $_year = !empty($_GET['y']) ? $_GET['y'] : date('Y');
$_date = !empty($_POST['d']) ? $_POST['d'] : false;
if (!$_date) $_date = !empty($_GET['d']) ? $_GET['d'] : null;
if (!empty($_date)&&empty($_week)){
	// a date defined
	if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $_date, $matches))
			list($none, $_year, $_mon, $_day) = $matches;
	else {
		$_year = date('Y'); $_mon = date('n'); $_day = date('j');
	}
	
	$_time = mktime(0, 0, 0, $_mon, $_day, $_year);
	$_week = date('W', $_time);
} 
else if (empty($_week)) $_week = $this_week;

//echo "$_year $_week $_date";
if (isset($_POST['book_periods'])){
}
$td_week =  ($_week<10) ? '0'.$_week : $_week;
$start_date = strtotime($_year.'W'.$td_week);
$_year = date('Y', $start_date);
$_mon = date('n', $start_date);
/*
$_wd = 7-$_week;
$_start = $_day-$_wd;
$_end = $_start+7;
$period = null;
$column_headers = array();
for($i=$_start;$i<$_end;$i++){

	$_sd = mktime(0, 0, 0, $_mon, $i, $_year);
	$column_headers[$_sd] = date('D j/n', $_sd);
	if (empty($period))
		$period = date('D M Y', $_sd);
}
$period .= ' - '. date('D M Y', $_sd);
*/
$_dis = 24 * 3600;
$_sd = $start_date;
$period = date('d M Y', $_sd);
for($i=0;$i<7;$i++){
	$column_headers[$_sd] = date('D j/n', $_sd);
	$_sd += $_dis;
}
$_sd -= $_dis;
$period .= ' - '. date('d M Y', $_sd);
/*
$_tp_interval = 35 * 60;
$_tp_start = mktime(7, 35, 0, $_mon, $_day, $_year);
$_tp_limit = $_tp_start + 10 * $_tp_interval;
$_tp_periods = array();
for($i=$_tp_start; $i<=$_tp_limit;$i+=$_tp_interval){
	$_tp_label = date('hi', $i).' '.date('hi',$i+$_tp_interval);;
	$_tp_periods[$i] = $_tp_label;	
}
*/
$_facility = isset($_POST['_facility']) ? $_POST['_facility'] : 0;
$facilities = get_facility_list();
if (count($facilities) == 0){
	$facilities[0] = '--none--';
} else {
	if (empty($_facility)){
		$k = array_keys($facilities);
		$_facility = $k[0];
	}
}
$periods = get_periods($_facility);
$_tp_periods = array();
foreach($periods as $rec){
	$_tp_periods[$rec['id_time']] = "$rec[time_start] - $rec[time_end]";
}
$booked_sheet = get_booked($_facility, $start_date, $_sd);

$msg = !empty($_SESSION['msg']) ? $_SESSION['msg'] : null;
if (!empty($msg)){
    $msg = unserialize($msg);
    display_message($msg );
    unset($_SESSION['msg']);
}

?>
<form method="POST" id="bookingform">
<input type="hidden" name="mod" value="booking">
<input type="hidden" name="act" value="list">
<input type="hidden" name="w" value="<?php echo $_week?>">
<input type="hidden" name="m" value="<?php echo $_mon?>">
<input type="hidden" name="y" value="<?php echo $_year?>">
<div style="padding: 5px 5px">
	<div style="float: left; padding: 5px 0">
	Facility / Room <?php echo build_combo('_facility', $facilities, $_facility)?> 	
	</div>

	<div id="calnav" style="float: right; ">
		<button class="weeknav" name="prev-week"> &larr; </button>
		<button class="weeknav" name="today"> Today </button>
		<button class="weeknav" name="next-week"> &rarr; </button>
		<!--
		<button name="weekly"> Week </button>
		<button name="monthly"> Month </button>
		-->
	</div>
<div class="clear"></div>
</div>
<div class="calwrap">
<div id="caltop">
	<div id="period_label"><?php echo $period;?></div>
	<div id="book_periods_wrap"><button type="button" id="book_periods"> Book Periods </button></div>
	<div class="clear"></div>
</div>
<table id="agenda_weekly" class="agenda">
<thead>
<tr class="column_header">
	<th width=30 rowspan=2>No</th>
	<th width=80 rowspan=2>Booked On</th>
	<th width=120 colspan=2>Booked Date</th>
	<th width=60>Start</th>
	<th width=60>Finish</th>
	<th width=160>Booked By</th>
	<th width=200 >Facility/Room</th>
	<th >Reason</th>
</tr>
<?php 
	$i=0;
	$disabled_columns = array();
	$disabled_periods = array();
	foreach($column_headers as $_t => $_ch){
		//echo "$_t < $early_time<br>";
		$disabled = ($_t < $early_time) ? 'disabled' : null;
		if (!$bookable_days[$i]) // check available weekdays
			$disabled = 'disabled';
		$cbc = '<div><input type="checkbox" id="cbc-'.$i.'" class="cbc" '.$disabled.'><div>';
		echo '<th>'.$_ch.$cbc.'</th>';
		if ($disabled) $disabled_columns[$i] = true;
		if (isset($booked_sheet[$_t])){ //booked
			$disabled_periods[$i] = $booked_sheet[$_t];
		}
		$i++;
	}
?>
</tr>
</thead>
<tbody>
<?php
	$columns = array_keys($column_headers);
	foreach($_tp_periods as $_tp => $_label){
		echo '<tr><td>'.$_label.'</td>';
		for ($i=0; $i<7; $i++){
			$cb = 'NA';
			$classes = array();
			if ($bookable_days[$i]){ // check available weekdays
				$_t = $columns[$i];
				if (!isset($disabled_columns[$i])) { // check lapsed date
					if (! (isset($booked_sheet[$_t]) && isset($booked_sheet[$_t][$_tp])))  // check booked period
						$cb = '<input type="checkbox" name="periods[]" class="cb-'.$i.'" value="'.$_t.'-'.$_tp.'">';
				
				} 
			} else $classes[]='dayoff';
			echo '<td class="'.implode(' ', $classes).'">'.$cb.'</td>';
		}
		echo '</tr>';
	}
?>
</tbody>
<tfoot>
</tfoot>
</table>
</div>
</form>

<style>
.agenda { width: 100%; background: #B0DEAE; border-spacing: 0; empty-cells: show; border: 1px solid #1e5335}
.agenda th,td { text-align: center; }
.agenda .column_header th { background: #ccc;  }
.agenda tbody { background: #fff; }
.agenda td,th { padding: 5px 5px; color: #000; border-bottom: 1px solid #B0DEAE; min-height: 40px;}
#calnav button {padding: 5px 10px}
.calwrap { padding: 3px 3px; background: #B0DEAE; border: 1px solid #0f3821}
.calwrap #caltop {padding: 5px 10px;}
#period_label { padding: 5px 0; color: navy; font-weight: bold; float: left; }
#book_periods_wrap { float: right; }
#book_periods { font-weight: bold; font-size: 10pt}
</style>
<script type='text/javascript'>
var cw = <?php echo $_week; ?>;
$('#_facility').change(function(e){
	$('#bookingform').submit();
});

$('.cbc').change(function(){
	var id = $(this).attr('id');
	var check_me = this.checked;
	var col = id.split('-');
	$('.cb-'+col[1]).each(function(){
		if (check_me)
			$(this).attr('checked', true);
		else
			$(this).removeAttr('checked');
	});
});

$('#book_periods').click(function(){
	$('input[name=step]').val(1);
	$('#bookingform').append('<input type="hidden" name="book_periods" value="1">');
	$('#bookingform').submit();
});

$('.weeknav').click(function(){
	var id = this.name;
	if (id=='prev-week') cw--;
	else if (id=='next-week') cw++;
	else if (id=='today') {
		cw = <?php echo $this_week?>;
		var d = new Date();
		$('#bookingform').append('<input type="hidden" name="y" value="'+d.getFullYear()+'">');
	}
	if (cw>0){
		$('input[name=w]').val(cw);
		$('#bookingform').submit();
	}
});

</script>
