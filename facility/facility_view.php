<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

$data_item = get_facility($_id);
$timesheet_data = get_timesheets($_id);
$item_count = count($timesheet_data);
/*
$equipments = get_equipments($_id);
$equipment_list = null;
if (count($equipments) > 0){
	$no = 1;
	$equipment_list = '<table width="100%" id="equipment_table">';
	$equipment_list .= '<tr><th width="30">No</th><th>Equipment</th><th width=80>Quantity</th></tr>';
	foreach ($equipments as $rec){
		$edit_link = '<a href="?mod=facility&sub=equipment&act=edit&id='.$_id.'&id_equipment='.$rec['id_equipment'].'" class="edit_link">edit</a>';
		$dele_link = '<a href="javascript:remove_equipment('.$_id.','.$rec['id_equipment'].')" class="edit_link">x</a>';
		$cn = ($no % 2 == 1) ? 'alt' : 'normal';
		$equipment_list .= '<tr class="el '.$cn.'"><td class="right">'.($no++).' '.$dele_link.'</td><td>'.$rec['name'].'</td><td class="center">'.$rec['quantity'].'<div style="float: right">'.$edit_link.'</div></td></tr>';	
	}
	$equipment_list .= '</table>';
} else {
	$equipment_list = '- NA -';
}
*/	
$period_term_list = '';
$terms = facility_period_term_rows($_id);
foreach($terms as $rec){
	$period_term_list .= '<a href="./?mod=facility&sub=period_timesheet&id='.$rec['id_term'].'">'.$rec['term'].'</a><br>';
}
	
?>
<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />

<style>
.edit_link { font-size: smaller; }
.cell_space_wider { padding: 2px 10px; }
#equipment_table {border: 1px solid #aaa; margin-bottom:5px;}
</style>
<br/>
<br/>
<form method="POST">
   <table style="min-width: 550px" class="facility_table" cellpadding=2 cellspacing=1>
    <tr><th colspan=2>View Facility in Detail</th></tr>
    <tr valign="top">
      <td width=130>Facility No</td>
      <td><?php echo $data_item['facility_no']?></td>
     </tr>
    <tr valign="top" class="alt">
      <td>Description</td>
      <td><?php echo $data_item['description']?></td>
     </tr>
	<!--
    <tr valign="top" class="alt">
      <td>Duration per Periode (days)</td>
      <td><?php echo $data_item['period_duration']?></td>
     </tr>
    <tr valign="top">
      <td>Max. Number of Periode can be taken</td>
      <td><?php echo $data_item['max_period']?></td>
     </tr>
    <tr valign="top" class="alt">
      <td>Lead time before booking (days)</td>
      <td><?php echo $data_item['lead_time']?></td>
     </tr>
    <tr valign="top">
      <td>Time Usage / Day</td>
      <td><?php echo $data_item['time_start'] . ' &nbsp; to &nbsp; '. $data_item['time_end']?></td>
     </tr>
	 -->
	 <?php
	 /*
     <tr valign="top" class="">
      <td>Equipments </td>
     </tr>
     <tr valign="top" class="">
      <td colspan=2 class="cell_space_wider"><?php echo $equipment_list; ?>
	  <button type="button" id="add_equipment" class="fancybox iframe">Add New Equipment</button>
	  <!--button type="button" id="edit_equipment">Edit Equipment List</button-->
	  </td>
     </tr>
	 */
	 ?>
     <tr valign="top" >
      <td>Period Terms</td>
      <td><?php echo $period_term_list?></td>
     </tr>
	 <tr valign="top" >
      <td>Status Notification</td>
      <td><?php 
	  
			if($data_item['status_notification'] == 1){
				echo "Enable";
			} else {
				echo "Disable";
			}
	  
	  ?></td>
     </tr>
	 <tr valign="top" >
      <td>Email</td>
      <td>
	  <?php 
		$email = str_replace(',','<br />', $data_item['email']);
		$email = str_replace('|', ' | ', $email);
		echo $email;
	  ?></td>
     </tr>
	  <tr valign="top" >
      <td>Handphone</td>
      <td>
	  <?php 
		$hp = str_replace(',','<br />', $data_item['handphone']);
		$hp = str_replace('|', ' | ', $hp);
		echo $hp;
	  ?></td>
     </tr>
     </table>
	 <div class="center middle" style="width:600px; padding-top: 10px">
    <a class="button" href="./?mod=facility&sub=facility&act=list">Back to Facility List</a> &nbsp;
<?php 
    if ($i_can_update && (USERDEPT>0)) 
        echo '<a class="button" href="./?mod=facility&sub=facility&act=edit&id='.$_id.'">Edit</a> &nbsp;';
    if ($i_can_delete && (USERDEPT>0)) 
        echo '<a class="button" href="./?mod=facility&sub=facility&act=del&id='.$_id.'"
    onclick="return confirm(\'Are you sure delete this facility?\')">Delete</a> &nbsp; ';
?>
    
	<!--
    <a class="button" href="./?mod=facility&sub=period&act=term_list_for_facility&id=<?php echo $_id?>">Period Terms</a> &nbsp;
    <a class="button" href="./?mod=facility&sub=timesheet&act=edit&id=<?php echo $_id?>">Add a Period</a> &nbsp;
    <a class="button" href="./?mod=facility&sub=period&act=timesheet_list&id=<?php echo $_id?>">Timesheet</a> &nbsp;
	-->
</div>
<br/>
<?php
if ($_id > 0) {
echo <<<TEXT
<!--
<input type="submit" name="delete" value=" Delete " 
	onclick="return confirm('Are you sure you want to delete $data_item[facility_no]?')">
	-->
TEXT;
}
?>

</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
<form id="remove_equipment_form" method="post" action="?mod=facility&sub=equipment&act=edit">
<input type="hidden" name="delete" value=1>
<input type="hidden" name="id_equipment" value=0>
<input type="hidden" name="id_facility" value="<?php echo $_id?>">
</form>

<script>
$('#add_equipment').click(function(){
	var url = "?mod=facility&sub=equipment&act=add&id=<?php echo $_id?>";
	/*
	$.fancybox.open({
		href: url, type: 'iframe'});
		*/
	location.href = url;
});

$('#edit_equipment').click(function(){
	//location.href = "?mod=facility&sub=equipment&act=edit&id=<?php echo $_id?>";
});

function remove_equipment(fid, eid)
{
	if (confirm('Do you sure un-assign the equipment?')) {
		var f = $('#remove_equipment_form').get(0);
		f.id_facility.value = fid;
		f.id_equipment.value = eid;
		f.submit()
	}
}
</script>
