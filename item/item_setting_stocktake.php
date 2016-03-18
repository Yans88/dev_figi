
<?php

if (!defined('FIGIPASS')) exit;
/*
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
*/

$type_list = array('daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly');
$dow_list = array(0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat');
$dom_list = range(1,31);
$hour = range(0,23);
$months = array(0=>'Jan',1=>'Feb',2=>'Mar',3=>'Apr',4=>'May',5=>'Jun',6=>'Jul', 7 =>'Aug', 8=>'Sep', 9=>'Oct', 10=>'Nov', 11=>'Des');
$id_group = USERGROUP;
$id_department = USERDEPT;
$data_available = check_group_in_notification_frequency_table($id_group, $id_department);
$arr_data_available = explode("|", $data_available);
$freq_in_db = $arr_data_available[1] ? $arr_data_available[1] : $config['item_frequency_day'];
$status_active = $arr_data_available[0];
//echo $data_available."<br/>";
?>
<h4>Setting Notifications</h4>

<?php 
	//echo "Department: ".USERDEPT.", Usergroup : ".USERGROUP . ", Id User : " . USERID; 
	
?>
<form method="POST" >
<div style="width: 600px;" class="middle">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Option</legend>
<br/>
<table>
<tr>
    <td align="right">Enable Email  Notification</td>
    <td align="left">
        <input type="radio" name="notification_email" value="1" <?php echo ($status_active =='1') ? ' checked ' : null ?> >Yes
        <input type="radio" name="notification_email" value="0" <?php echo ($status_active !='1') ? ' checked ' : null ?> >No
    </td>
</tr>
<tr>
    <td align="right">Frequency</td>
    <td align="left"><?php echo build_combo("item_frequency_type", $type_list, $freq_in_db)?></td>
</tr>
<?php if($freq_in_db == "weekly"){  ?>
<tr id="dow_row">
    <td align="right">Execution Day</td>
    <td align="left"><?php echo build_combo("item_frequency_day", $dow_list, $arr_data_available[2])?></td>
</tr>
<?php } else { 

?>
	<tr id="dow_row" style="display: none">
		<td align="right">Execution Day</td>
		<td align="left"><?php echo build_combo("item_frequency_day", $dow_list, $config['item_frequency_day'])?></td>
	</tr>
<?php } ?>

<?php if($freq_in_db == "monthly"){ ?>
	<tr id="dom_row">
		<td align="right">Execution Dates</td>
		<td align="left"><?php echo build_combo("item_frequency_date", $dom_list, $arr_data_available[2])?></td>
	</tr>
<?php } else { ?>
	<tr id="dom_row" style="display: none">
		<td align="right">Execution Dates</td>
		<td align="left"><?php echo build_combo("item_frequency_date", $dom_list, $config['item_frequency_day'])?></td>
	</tr>
<?php } ?>



<?php if($freq_in_db == "yearly"){ 

$d = explode("-", $arr_data_available[2]);
$day_from_db = $d[0];
$month_from_db = $d[1];

?>
	
	<tr id="month_row">
		<td align="right">Execution Date and Month</td>
		<td align="left">
			<?php echo build_combo("item_frequency_date_yearly", $dom_list, $day_from_db)?>
			<?php echo build_combo("item_frequency_month_yearly", $months, $month_from_db)?>
		</td>
	</tr>
<?php } else { ?>
	
	<tr id="month_row" style="display: none">
		<td align="right">Execution Date and Month</td>
		<td align="left">
			<?php echo build_combo("item_frequency_date_yearly", $dom_list, $config['item_frequency_day'])?>
			<?php echo build_combo("item_frequency_month_yearly", $months, $config['item_frequency_day'])?>
		</td>
	</tr>
<?php } ?>
<!--===========-->
<?php
		
	if($freq_in_db == 'daily'){ $clocks=$arr_data_available[2]; } else { $clocks=$arr_data_available[3]; }
	$time = explode(":", $clocks);
	$time_hour = $time[0];
	$time_minute = $time[1];
	
?>
<!--===========-->
<tr id="ex_hour">
    <td align="right">Execution Hour</td>
    <td align="left">
	
		<select name='hour'> <?php
		
			for ($i = 0; $i <= 23; $i++) {
				$sel = ($i == $time_hour) ? ' selected="selected"' : '';
				echo "<option value=\"$i\"$sel>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>";
			} ?>
		</select>
		
		<select name='minute'> <?php
			for ($i = 0; $i <= 59; $i++) {
				$sel = ($i == $time_minute) ? ' selected="selected"' : '';
				echo "<option value=\"$i\"$sel>".str_pad($i, 2, '0', STR_PAD_LEFT)."</option>";
			} ?>
		</select>
		
	</td>
</tr>
</table>
</fieldset>
<fieldset class="footer">
<input type="hidden" name="long_term_notification_date" id="long_term_notification_date">
<input type='submit' name="save" value="Save" class='button'>
</fieldset>
</div>
</form>

<?php


if($_POST['save']){

	//echo $_POST['id_group']." - ".$_POST['item_frequency_type']." - ".$_POST['item_frequency_day']." - ".$_POST['item_frequency_date_yearly']." - ".$_POST['item_frequency_month_yearly']." - " . $_POST['item_frequency_date'];
	
	$frequency = $_POST['item_frequency_type'];
	$status_active = $_POST['notification_email'];
	
	$execution_hour = str_pad($_POST['hour'], 2, '0', STR_PAD_LEFT).":".str_pad($_POST['minute'], 2, '0', STR_PAD_LEFT);
	
	if($frequency == 'daily'){ 
		$frequency_modif = $frequency."|".$execution_hour;
	}
	
	if($frequency == 'weekly'){ 
		$frequency_modif = $frequency."|".$_POST['item_frequency_day']."|".$execution_hour;
	}
	
	if($frequency == 'monthly'){ 
		$frequency_modif = $frequency. "|". $_POST['item_frequency_date'] . "|".$execution_hour;
	}
	
	if($frequency == 'yearly'){ 
		$frequency_modif = $frequency."|". $_POST['item_frequency_date_yearly'] .'-'.$_POST['item_frequency_month_yearly']."|".$execution_hour ;
	}
	
	//echo $frequency_modif;
	$a = add_frequency($id_group, $id_department, $frequency_modif, $status_active);
	echo "<script>alert('".$a."');location.href='./?mod=item&act=setting_stocktake'</script>";
	//echo $a;
} else {

	echo "";
	
}

?>
<script>
$('select[name=item_frequency_type]').change(function(){
	var type = $(this).find('option:selected').val();
	$('#dow_row').hide();
	$('#dom_row').hide();
	$('#month_row').hide();
	$('#daily').hide();
	if (type=='monthly') $('#dom_row').show();
	else if (type=='weekly') $('#dow_row').show();
	else if (type=='yearly') $('#month_row').show();
	else if (type=='daily') $('#daily').show();

});

$('select[name=report_frequency_type]').trigger('change');
</script>

<?php
function add_frequency($id_group, $id_department, $frequency, $status_active){
	$check = "SELECT * FROM notification_frequency WHERE id_group = $id_group AND id_department = $id_department";
	$check_query = mysql_query($check);
	$check_query_exec = mysql_fetch_array($check_query);
	
	if($check_query_exec > 0){
		
		$query_update = "UPDATE notification_frequency SET frequency = '$frequency', status='$status_active' WHERE id_group = $id_group AND id_department = $id_department";
		$mysql_update = mysql_query($query_update);
		if($mysql_update){ return 'Update Success.'; } else { return 'Update Failed.'; }
		
	} else {
	
		$query = "INSERT INTO notification_frequency (id_group, id_department, frequency, status) VALUES($id_group, '$id_department', '$frequency', $status_active)";
		$mysql_query = mysql_query($query);
		
		if($mysql_query){ return 'Add Success.'; } else { return 'Update Failed.'; }
		
	}

}

function check_group_in_notification_frequency_table($id_group, $id_department){
	$query = "SELECT * FROM notification_frequency WHERE id_group = ".$id_group." AND id_department = ".$id_department;
	$mysql_query = mysql_query($query);
	$mysql_row = mysql_fetch_array($mysql_query);

	if($mysql_row['id_group'] > 0){
		return $mysql_row['status']."|".$mysql_row['frequency'];
	} else {
		return 0;
	}

}
?>