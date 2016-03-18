<?php

$this_time = time();
$early_time = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

$this_week = date('W');
$_week = isset($_POST['w']) ? $_POST['w'] : false;
if (!$_week) $_week = !empty($_GET['w']) ? $_GET['w'] : null;// $this_week;

$_year = !empty($_POST['y']) ? $_POST['y'] : false;
if (!$_year) $_year = !empty($_GET['y']) ? $_GET['y'] : date('Y');


$_year = $year;
$_date = !empty($_POST['d']) ? $_POST['d'] : false;
if (!$_date) $_date = !empty($_GET['d']) ? $_GET['d'] : null;
//echo "$_week $_year $_date";
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
else if (empty($_week)) {
	if (!empty($year) && !Empty($month)){ // found year & month, get week on same date
		$_time = mktime(0, 0, 0, $month, date('j'), $year);
		$_week = date('W', $_time);
	} else
		$_week = $this_week;
}

if (isset($_POST['book_periods'])){
}
$td_week = (strlen($_week) < 2) ? "0".$_week : $_week;
//echo $td_week."<~ td week";
$start_date = strtotime($_year.'W'.$td_week);
$year = date('Y', $start_date);
$mon = date('n', $start_date);

$_dis = 24 * 3600;
$_sd = $start_date;

$period = date('d M Y', $_sd);

for($i=0;$i<7;$i++){
	$column_headers[$_sd] = date('D j/n', $_sd);
	$_sd += $_dis;
}

$_sd -= $_dis;
$period .= ' - '. date('d M Y - W', $_sd);

$term = period_term_get(0, $_facility, 1);

$periods = period_timesheet_rows($term['id_term'], $_facility);
$valid_from = strtotime($term['valid_from']);
$valid_to   = strtotime($term['valid_to']);
$_tp_periods = array();
$enabled_days = array();
$time_start_per_periode = array();

foreach($periods as $rec){
	$_tp_periods[$rec['id_time']] = "$rec[start_time] - $rec[end_time]";
	$raw_periods[$rec['id_time']] = $rec;
	$enabled_days[$rec['id_time']] = $rec['enabled_days'];
	$time_start_per_periode[$rec['id_time']] = "$rec[start_time]";
}
$booked_sheet = get_booked($_facility, $start_date, $_sd);

$msg = !empty($_SESSION['msg']) ? $_SESSION['msg'] : null;
if (!empty($msg)){
    $msg = unserialize($msg);
    display_message($msg );
    unset($_SESSION['msg']);
}
$today = date('Ynj');

?>
<form method="POST" id="bookingform">
<input type="hidden" name="mod" value="booking">
<input type="hidden" name="step" value=0>
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
	<th style="min-width: 50px; "></th>
	<th style="min-width: 80px;">Time Period</th>
<?php 
	$i=0;
	$disabled_columns = array();
	$disabled_periods = array();
	foreach($column_headers as $_t => $_ch){
		$disabled = ($_t < $early_time) ? 'disabled' : null;
		$disabled = (in_range($_t, $valid_from, $valid_to)) ? $disabled: 'disabled';
		/*
		if (!$bookable_days[$i]) // check available weekdays
			$disabled = 'disabled';
		*/
		$cbc = '<div><input type="checkbox" id="cbc-'.$i.'" class="cbc" '.$disabled.'><div>';
		$_class = '';
		if ($today==date('Ynj', $_t)) $_class = 'today';
		echo '<th class="'.$_class.'">'.$_ch.$cbc.'</th>';
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
		$_name = $raw_periods[$_tp]['name'];
		echo '<tr><td style="text-align:left">'.$_name.'</td><td>'.$_label.'</td>';
		$avail = $enabled_days[$_tp]; 
		//$disabled = (strpos($enabled_days[$id_, $valid_from, $valid_to)) ? $disabled: 'disabled';
		
		for ($i=0; $i<7; $i++){
			
			$cb = 'NA';
			
			$classes = array();
			if (strpos($avail, "$i")>-1){ // check available weekdays
				$_t = $columns[$i];
				
				$date_db = date('Y-m-d', $_t);
				$time_db = date('H:i:s', strtotime($time_start_per_periode[$_tp]));
				$date_time_db = $date_db." ".$time_db;
				$now = date('Y-m-d H:i:00');
				$str_now = strtotime($now);
				$str_date_time_db = strtotime($date_time_db);
				
				
				$id_book = $booked_sheet[$_t][$_tp]['id_book'];
				$id_user = $booked_sheet[$_t][$_tp]['id_user'];
				$booked_date = $_t;
				$id_time = $_tp;
				$name_of_booking_by = $booked_sheet[$_t][$_tp]['booked_by'];
				
				$link_view = "./?mod=booking&act=view&id=$id_book";
				$link_delete = "./?mod=booking&act=remove&id=$id_book&booked_date=$booked_date&id_time=$id_time";
				
				
				if(ALTERNATE_PORTAL_STATUS == 'enable'){
					if(USERGROUP == GRPASSETADMIN || USERGROUP == GRPASSETOWNER || USERGROUP == 1 && $str_date_time_db >= $str_now){
						$delete_button = "
						<div style='float:left;margin-left:4px;' class='confirm_delete' id='$id_book'>
						<a href='$link_delete' style='cursor:pointer;color:red;text-decoration:none;'>x</a>
						</div>";
						$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
					} else if((USERGROUP == GRPTEADM || USERGROUP == GRPTEA)){
					
						if( USERID == $id_user && $str_date_time_db >= $str_now){
						$delete_button = "
						<div style='float:left;margin-left:4px;' class='confirm_delete' id='$id_book'>
						<a href='$link_delete' style='cursor:pointer;color:red;text-decoration:none;'>x</a>
						</div>";
						$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
						} else {
							$delete_button = "";
							$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
						}
					} else {
						$delete_button = "";
						$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
					}
				} else {
					if(USERGROUP == GRPASSETADMIN || USERGROUP == GRPASSETOWNER || USERGROUP == 1 && $str_date_time_db >= $str_now){
						$delete_button = "
						<div style='float:left;margin-left:4px;' class='confirm_delete' id='$id_book'>
						<a href='$link_delete' style='cursor:pointer;color:red;text-decoration:none;'>x</a>
						</div>";
						$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
					} else if((USERGROUP == GRPTEADM || USERGROUP == GRPTEA)){
					
						if( USERID == $id_user && $str_date_time_db >= $str_now){
						$delete_button = "
						<div style='float:left;margin-left:4px;' class='confirm_delete' id='$id_book'>
						<a href='$link_delete' style='cursor:pointer;color:red;text-decoration:none;'>x</a>
						</div>";
						$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
						} else {
							$delete_button = "";
							$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
						}
					} else {
						$delete_button = "";
						$view_button = "<a style='color:black;text-decoration:none;font-size:12px;' href='".$link_view."' target='parent'>".$name_of_booking_by."</a>";
					}
				}
				
				$x = $name_of_booking_by ? "<div><div style='line-height:15px;float:left;'><b>Booked by :</b> <div style='text-align:center;'>".$view_button."</div></div>".$delete_button."</div>" : "NA"; 
				
				$cb = $x;
				/*$cb = $_t."--".$_tp;*/
				if (!isset($disabled_columns[$i])) { // check lapsed date
				
					if (! (isset($booked_sheet[$_t]) && isset($booked_sheet[$_t][$_tp]))){  // check booked period
							
							if($str_date_time_db <= $str_now){
								$cb = "NA";
							} else {
								$cb = '<input type="checkbox" name="periods[]" class="cb-'.$i.'" value="'.$_t.'-'.$_tp.'">';
							}
						
					}
				} 
				if ($today==date('Ynj', $_t)) $classes[] = 'today';
			} //else $classes[]='dayoff';
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

$('.confirm_delete').click(function(){

	var a = confirm("Are you sure want to delete this data ?");
	if(a){
		return true;
	} else {
		return false;
	}
});

</script>
