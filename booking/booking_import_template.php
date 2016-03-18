<?php
$id_facility = isset($_POST['id_facility']) ? $_POST['id_facility'] : 0;
$facilities = array('0' => '* select a facility')+bookable_facility_list();
$crlf = "\r\n";

$facility_name = $facilities[$id_facility];
if (!empty($_POST['download']) && $id_facility>0){
	$dt = mktime(date('H'),date('i'),0,date('n'),date('j')+1,date('Y'));
	$sample_date = date('Y-m-d', $dt);
	
	$term = period_term_get(0, $id_facility, 1);
	//print_r($term);
	$periods = period_timesheet_rows($term['id_term']);
	$period_list = "id_facility,id_term,date,id_period,period,recurring,recurring_times,id_subject,reason,instruction$crlf";
	
	$next_day = date('d-M-Y', $dt);
	foreach($periods as $rec){
		$period = "$rec[start_time] - $rec[end_time]";
		$period_list .= "$id_facility,$term[id_term],\"$next_day\",$rec[id_time],\"$period\",NONE,0,,,$crlf";
	}	

	ob_clean();
	header('Content-type: application/csv');
	header('Content-length: '.strlen($period_list));
	header('Content-disposition: attachment; filename="figi booking template - '.$facility_name. ' - '.$term['term'].'.csv"');
	header('Pragma: no-cache');
	echo $period_list;
	ob_end_flush();
	exit;
}

?>
<div class="submod_wrap">
	<div class="submod_links">
	<?php
		if (defined('PORTAL')){
			echo '<a href="./?mod=portal&portal=facility" class="button" > Booking Calendar </a>';
		} else {
			echo '<a href="./?mod=booking" class="button" > Cancel </a> ';
			echo '<a href="./?mod=booking&act=import" class="button" > Import Booking </a> ';
			echo '<a href="./?mod=booking&act=list_subject" class="button" > Subject List </a>';
		}
	?>
	</div>
	<div class="submod_title"><h4 >Import Booking</h4></div>
	<div class="clear"> </div>
</div>

<form method="POST" id="frmdl">
<input type="hidden" name="download" value=1> 
<div class="center">
<h4>Generate CSV Template for Import Booking</h4>
<br/>
Facility / Room <?php echo build_combo('id_facility', $facilities)?>
<div style="width: 900px;" class="middle" >
<br>
<div class="left">Columns list will be like shown below:</div>
<br>
<table width="100%" border=1 class="itemlist grid">
 <tr>
	<td class="center"><span class="field-note">*</span> id_facility</td>
	<td class="center"><span class="field-note">*</span> facility_name</td>
	<td class="center"><span class="field-note">*</span> id_term</td>
	<td class="center"><span class="field-note">*</span> term_name</td>
	<td class="center">date</td>
	<td class="center"><span class="field-note">*</span> id_period</td>
	<td class="center"><span class="field-note">*</span> period</td>
	<td class="center">recurring</td>
	<td class="center">recurring_times</td>
	<td class="center"><span class="field-note">**</span> subject</td>
	<td class="center">reason</td>
	<td class="center">instruction</td>
`</tr>
</table>	
<div class="left"><span class="field-note">*</span> do not change the content </div>
<div class="left"><span class="field-note">**</span> check id for subject <a href="./?mod=booking&act=list_subject">here</a></div>
</div>
<br>
<p>
 <button type="button" id="download" > Download Template </button> 
</p>  
</div>
</form>
<script>
$('#download').click(function(){
	var frm = this.form;
	if (frm.id_facility.selectedIndex <= 0){
		alert('Please select the facility will be booked.');
	} else {
		$('#frmdl').submit();
	}
});
</script>
