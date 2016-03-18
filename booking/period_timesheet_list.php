<?php
if (!defined('FIGIPASS')) exit;

$id_term = isset($_GET['id']) ? $_GET['id'] : 0;
$id_facility = isset($_GET['id_facility']) ? $_GET['id_facility'] : 0;
$term = period_term_get($id_term);

$id_term = isset($_GET['id_term']) ? $_GET['id_term'] : $term['id_term'];
if (!empty($_POST['save'])){

	$names = $_POST['names'];
	$days = $_POST['days'];
	$modified_by = USERID;
	foreach ($names as $id_time => $name){
		if (!empty($days[$id_time])){
			$enabled_days = implode('', array_keys($days[$id_time]));
			
			$query = "UPDATE facility_period_timesheet SET name='$name', modified_by = $modified_by, 
						enabled_days = '$enabled_days' 
						WHERE id_time = $id_time";
			mysql_query($query);
			
		}
	}
} else if (!empty($_POST['save_new_period'])){
	//error_log(serialize($_POST));
	$modified_by = USERID;
	$name = mysql_real_escape_string($_POST['name']);
	$enabled_days = $_POST['day'];//implode('', array_keys($days));
	$time_start = $_POST['start_time'];
	$time_end = $_POST['end_time'];
	$query = "INSERT INTO facility_period_timesheet(id_term, name, time_start, time_end,  modified_by, 
				enabled_days) VALUE($id_term, '$name', '$time_start', '$time_end', $modified_by, '$enabled_days')";
	mysql_query($query);
	//error_log(mysql_error().$query);
	$msg = 'New period has been added to the period term!';	
	redirect($modact_url.'&id='.$_POST['id_term'], $msg);
} else if (!empty($_POST['update'])){
	error_log(serialize($_POST));
	$modified_by = USERID;
	$name = mysql_real_escape_string($_POST['name']);
	$enabled_days = $_POST['day'];	
	$time_start = $_POST['start_time'];
	$time_end = $_POST['end_time'];
	$id_time = $_POST['id_time'];
	$query = "REPLACE INTO  facility_period_timesheet(id_time, id_term, name, time_start, time_end,  modified_by, 
				enabled_days) VALUE($id_time, $id_term, '$name', '$time_start', '$time_end', $modified_by, '$enabled_days')";
	mysql_query($query);
	error_log(mysql_error().$query);
	$msg = 'Period has been updated sucessfully!';	
	redirect($modact_url.'&id='.$_POST['id_term'], $msg);
} else if (!empty($_POST['dele'])){
	if ($_POST['dele']=='all')
		$query = "DELETE FROM facility_period_timesheet WHERE id_term = $_POST[id_term]";
	else
		$query = "DELETE FROM facility_period_timesheet WHERE id_time = $_POST[dele]";
	if (mysql_query($query)){
		$msg = 'Selected period has been deleted from system!';
	} else
		$msg = 'Period deletion has been failed. Please contact the adminisrator';
	redirect($modact_url.'&id='.$_POST['id_term'], $msg);
}

$sheets = period_timesheet_rows($id_term);
$item_count = count($sheets);
$term_caption = '<a href="'.$submod_url.'">'.$term['term'].'</a>';
$term_caption = $term['term'];
?>
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<script type="text/javascript" src="./js/anytimec.js"></script>

<h4>Manage Period Term : <?php echo $term_caption?></h4>
<br/>
<div align="left" valign="middle" style="">
<?php
if ($i_can_create) {
/*
?>
<a href="./?mod=facility&sub=facility&act=edit">Add Period</a>
<?php
*/
}
?>
</div>
<form method="post" id="frm_timesheet">
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30 valign="top">
  <th width=130>Name (optional)</th>
  <th width=80>Start Time</th>
  <th width=80>End Time</th>
  <th width=40>Mon<br><input type="checkbox" id="cbc-0" class="cbc"></th>
  <th width=40>Tue<br><input type="checkbox" id="cbc-1" class="cbc"></th>
  <th width=40>Wed<br><input type="checkbox" id="cbc-2" class="cbc"></th>
  <th width=40>Thu<br><input type="checkbox" id="cbc-3" class="cbc"></th>
  <th width=40>Fri<br><input type="checkbox" id="cbc-4" class="cbc"></th>
  <th width=40>Sat<br><input type="checkbox" id="cbc-5" class="cbc"></th>
  <th width=40>Sun<br><input type="checkbox" id="cbc-6" class="cbc"></th>
  <th >Modified By</th>
  <th width=50>Action</th>
</tr>
<?php
if ($item_count > 0){
	$row = 0;
	foreach ($sheets as $rec){
		$row++;
		$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
		$id_time = $rec['id_time'];
		$period_title = $rec['start_time'].'-'.$rec['end_time'];
		if (!empty($rec['name'])) $period_title .= " ($rec[name])";
		$link = '<a href="javascript:save('.$id_time.')" title="save period changes">&#10004;</a> ';
		$link .= '<a href="javascript:reset('.$id_time.')" title="reset period">&#9851;</a> ';
		$link .= '<a href="javascript:dele('.$id_time.')" title="delete period">&#10008;</a>';
		$enabled_days = '';
		for($i=0; $i<7; $i++){
			$checked = (strpos($rec['enabled_days'], "$i")>-1) ? 'checked': null;
			$enabled_days .= "<td class='center'><input type='checkbox' $checked name='days[$id_time][$i]' class='cb cb-$i cbt-$id_time' id='cb-$id_time-$i'></td>";
		}
		echo <<<ROW
<tr $class>
	<td class="center"><input type="text" name="names[$id_time]" value="$rec[name]" id="name-$id_time" ></td>
	<td class="center"><input type="text" name="starts[$id_time]" value="$rec[start_time]" id="start-$id_time" size=4 ></td>
	<td class="center"><input type="text" name="ends[$id_time]" value="$rec[end_time]" id="end-$id_time" size=4></td>
	$enabled_days
	<td class="center">$rec[modified_by_name]</td>
	<td class="center">$link</td>
</tr>

ROW;
	}
	$row++;
	$modified_by_name = FULLNAME;
	$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';

echo <<<ROWX
<tr $class style="display: none; background: #F5FAFF" id="rowx">
	<td class="center"><input type="text" name="name_x" value="" ></td>
	<td class="center"><input type="text" name="start_time" id="start_time" size=6 ></td>
	<td class="center"><input type="text" name="end_time" id="end_time" size=6 ></td>
	<td class='center'><input type='checkbox' name='day[0]' class='' id='cbx0'></td>
	<td class='center'><input type='checkbox' name='day[1]' class='' id='cbx1'></td>
	<td class='center'><input type='checkbox' name='day[2]' class='' id='cbx2'></td>
	<td class='center'><input type='checkbox' name='day[3]' class='' id='cbx3'></td>
	<td class='center'><input type='checkbox' name='day[4]' class='' id='cbx4'></td>
	<td class='center'><input type='checkbox' name='day[5]' class='' id='cbx5'></td>
	<td class='center'><input type='checkbox' name='day[6]' class='' id='cbx6'></td>
	<td class="center">$modified_by_name</td>
	<td class="center"><a style="font-size: smaller" href="javascript:void(0)" id="save_new_period" >&#10004;</a> <a href="javascript:void(0);" id="cancel" >&#10008;</a></td>
</tr>
<script>
$('#cancel').click(function(){
	$('#rowx').hide();
});
$('#save_new_period').click(function(){
	var f = $('#frm_edit').get(0);
	with ($('#frm_edit')){
		append('<input type="hidden" name="name" value="'+$('input[name=name_x]').val()+'">');
		append('<input type="hidden" name="start_time" value="'+$('#start_time').val()+'">');
		append('<input type="hidden" name="end_time" value="'+$('#end_time').val()+'">');
		var d = '';
		for (var i=0; i<7; i++){
			var c = $('#cbx'+i+':checked');
			if (c.length>0) d += i;
		}
		append('<input type="hidden" name="day" value="'+d+'">');
		append('<input type="hidden" name="save_new_period" value=1>');
	}
	f.submit();
	$('#rowx').hide();
});

</script>
ROWX;

	echo <<<FOOT
<tr><td colspan=12 class="right" style="padding: 5px 5px">
	<button type="button" id="back"> Period Terms </button> 
	<button type="button" id="addperiod"> Add Period </button> 
	<button type="button" id="deleteall"> Delete All </button> 
	<button name="save" value=1> Save Periods </button>
</td></tr>
FOOT;

} else 
	echo '<tr><td colspan=12 class="center">Data is not available! Generate periods <a href="javascript:generate()">here</a></td></tr>';
?>
</table>
</form>
<form id="frm_dele" method="post">
<input type="hidden" name="id_term" value="<?php echo $id_facility?>">
<input type="hidden" name="id_term" value="<?php echo $id_term?>">
<input type="hidden" name="id_time" value="0">
<input type="hidden" name="dele" value="0">
</form>
<form id="frm_edit" method="post">
<input type="hidden" name="id_term" value="<?php echo $id_term?>">
</form>

<script type="text/javascript" src="./js/jquery.fancybox.pack.js?v=2.0.6"></script>
<link rel="stylesheet" type="text/css" href="./style/default/jquery.fancybox.css?v=2.0.6" media="screen" />

<script>
$('.cbc').change(function(){
var id = this.id.substr(4);
var checked = this.checked;
$('.cb-'+id).each(function(){
	this.checked = checked;
});
});

$('#deleteall').click(function(){
if (confirm('Do you sure delete all periods from the term?')){
	var f = $('#frm_dele').get(0);
	f.dele.value  = 'all';
	f.submit();
}
});

function save(id){
var f = $('#frm_edit').get(0);
with ($('#frm_edit')){
	append('<input type="hidden" name="name" value="'+$('#name-'+id).val()+'">');
	append('<input type="hidden" name="start_time" value="'+$('#start-'+id).val()+'">');
	append('<input type="hidden" name="end_time" value="'+$('#end-'+id).val()+'">');
	var d = '';
	for (var i=0; i<7; i++){
		var c = $('#cb-'+id+'-'+i+':checked');
		if (c.length>0) d += i;
	}
	append('<input type="hidden" name="day" value="'+d+'">');
	append('<input type="hidden" name="id_time" value="'+id+'">');
	append('<input type="hidden" name="update" value=1>');
}
f.submit();
}

function reset_element(id){
	var	e = $(id).get(0);
	if (e.type == 'checkbox' || e.type == 'radio')
		e.checked = e.defaultChecked;
	else
		e.value = e.defaultValue;
}

function reset(id){
	var f = $('#frm_timesheet').get(0);
	reset_element('#name-'+id);
	reset_element('#start-'+id);
	reset_element('#end-'+id);
	for(var i=0; i<7; i++)
		reset_element('#cb-'+id+'-'+i);
		
}

function dele(id){
	if (confirm('Do you sure delete the period?')){
		var f = $('#frm_dele').get(0);
		f.id_time.value = id;
		f.dele.value  = id;
		f.submit();
	}
}

function generate(){
	$.fancybox.open({
		href: "<?php echo $submod_url.'&act=generate&id='.$id_term?>",
		type: 'iframe',
		width: 300,
		padding: 5
		});
}

$('#back').click(function (){
	location.href = '<?php echo $mod_url?>&sub=period_term&act=list';
});

$('#addperiod').click(function (){
	$('#rowx').show();

	$('#start_time').AnyTime_noPicker().AnyTime_picker({format: "%H:%i"});
	$('#end_time').AnyTime_noPicker().AnyTime_picker({format: "%H:%i"});
	$('input[name=name_x]').focus();
	/*
	$.fancybox.open({
		href: "./facility/period_timesheet_add.php?id=<?php echo $id_term?>",
		type: 'iframe',
		width: 300,
		padding: 5
		});
		*/
});

</script>
