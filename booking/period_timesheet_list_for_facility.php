<?php
if (!defined('FIGIPASS')) exit;

$id_term = isset($_GET['id']) ? $_GET['id'] : 0;
$id_facility = isset($_GET['id_facility']) ? $_GET['id_facility'] : 0;
$facility = get_facility($id_facility);
$term = period_term_get($id_term);

$id_term = isset($_GET['id_term']) ? $_GET['id_term'] : $term['id_term'];
if (!empty($_POST['save'])){
	//print_r($_POST);
	$names = $_POST['names'];
	$days = $_POST['days'];
	$modified_by = USERID;
	foreach ($names as $id_time => $name){
		if (!empty($days[$id_time])){
			$enabled_days = implode('', array_keys($days[$id_time]));
			
			$query = "UPDATE facility_period_timesheet SET name='$name', modified_by = $modified_by, 
						enabled_days = '$enabled_days' 
						WHERE id_time = $id_time";
	//		echo mysql_error().$query;
			mysql_query($query);
			
		}
	}
} else if (!empty($_POST['dele'])){
	$query = "DELETE FROM facility_period_timesheet WHERE id_time = $_POST[dele]";
	if (mysql_query($query))
		$msg = 'Selected period has been deleted from system!';
	else
		$msg = 'Period deletion has been failed. Please contact the adminisrator';
	redirect('./?mod=facility&sub=period&act=timesheet_list&id='.$_POST['id_term'], $msg);
}

$sheets = period_timesheet_rows($id_term);
$item_count = count($sheets);
//print_r($term);
?>
<h4>View Period Timesheet "<?php echo $term['term']?>" for Facility "<?php echo $facility['facility_no']?>"</h4>
<br/>
<div align="left" valign="middle" style="">
<a href="./?mod=facility&sub=facility&act=view&id=<?php echo $id_facility?>">Facility Detail</a> &nbsp; 
<a href="./?mod=facility&sub=period&act=term_list_for_facility&id=<?php echo $id_facility?>">Period Term Detail</a>
</div>
<form method="post">
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30 valign="top">
  <th width=130>Name (optional)</th>
  <th width=80>Start Time</th>
  <th width=80>End Time</th>
  <th width=40>Mon</th>
  <th width=40>Tue</th>
  <th width=40>Wed</th>
  <th width=40>Thu</th>
  <th width=40>Fri</th>
  <th width=40>Sat</th>
  <th width=40>Sun</th>
  <th >Modified By</th>
</tr>
<?php
if ($item_count > 0){
	$row = 0;
	foreach ($sheets as $rec){
		$row++;
		$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
		$period_title = $rec['start_time'].'-'.$rec['end_time'];
		if (!empty($rec['name'])) $period_title .= " ($rec[name])";
		$link = '<a href="javascript:dele('.$rec['id_term'].')" title="delete period '.$period_title.'">x</a>';
		$enabled_days = '';
		for($i=0; $i<7; $i++){
			$checked = (!empty($rec['enabled_days'])&&strpos($rec['enabled_days'], "$i")>-1) ? '&#10003;': null; // &#10004; -> bolder check mark
			$enabled_days .= "<td class='center'>$checked</td>";
		}
		echo <<<ROW
<tr $class>
	<td class="left">$rec[name]</td>
	<td class="center">$rec[start_time]</td>
	<td class="center">$rec[end_time]</td>
	$enabled_days
	<td class="left">$rec[modified_by_name]</td>
</tr>

ROW;
	}
	//echo '<tr><td colspan=12 class="right"><button name="save" value=1> Save Period </button></td></tr>';
} else 
	echo '<tr><td colspan=12 class="center">Data is not available!</td></tr>';
?>
</table>
</form>
<form id="frm_dele" method="post">
<input type="hidden" name="id_term" value="<?php echo $id_facility?>">
<input type="hidden" name="id_term" value="<?php echo $id_term?>">
<input type="hidden" name="id_time" value="0">
<input type="hidden" name="dele" value="0">
</form>

<script>
$('.cbc').change(function(){
	var id = this.id.substr(4);
	var checked = this.checked;
	$('.cb-'+id).each(function(){
		this.checked = checked;
	});
});

function dele(id){
	if (confirm('Do you sure delete the period?')){
		var f = $('#frm_dele').get(0);
		f.id_time.value = id;
		f.dele.value  = id;
		f.submit();
	}
}
</script>
