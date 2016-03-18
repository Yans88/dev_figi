<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$facility_data = get_facility($_id);
$schedule_data = get_schedule($_id);
$item_count = count($schedule_data);


?>
<br/>
<?php
if ($i_can_create) {
?>
<div align="left" valign="middle" style="width: 800px">
<a href="./?mod=facility&sub=facility&act=edit">
	<img width=16 height=16 border=0 src="images/add.png"> Add New Schedule</a>
</div>
<?php
}
?>
<table width="400" cellpadding=2 cellspacing=1 class="itemlist" >
<tr>
  <td width=150>Facility ID </td><td><?php echo $_id?></td>
</tr>
<tr valign="top" class="alt">
  <td>Facility No</td><td><?php echo $facility_data['facility_no']?></td>
 </tr>
<tr valign="top">
  <td>Duration per Periode</td><td><?php echo $facility_data['duration_per_periode']?></td>
 </tr>
<tr valign="top" class="alt">
  <td>Maximum Periode</td><td><?php echo $facility_data['maximum_periode']?></td>
 </tr>
<tr valign="top">
  <td>Lead Time </td><td><?php echo $facility_data['lead_time']?></td>
 </tr>
<tr valign="top" class="alt">
  <td>Location</td><td><?php echo $facility_data['location']?></td>
 </tr>
</table>

<table width="400" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30 valign="top">
  <th width=160>Schedule No</th>
  <th width=80>Periode</th>
  <th width=80>Status</th>
  <th width=150>Action</th>
</tr>
<?php
if ($item_count > 0){
	$row = 0;
	foreach ($schedule_data as $rec){
		$row++;
		$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
		$link  = '<a href="./?mod=facility&act=edit&id='.$rec['id_facility'].'">edit</a> | ';
		$link .= '<a href="./?mod=facility&act=del&id='.$rec['id_facility'].'">delete</a> | ';
		$link .= '<a href="./?mod=facility&sub=schedule&act=view&id='.$rec['id_facility'].'">schedule</a>';
		echo <<<ROW
<tr $class>
	<td>$rec[id_time]</td>
	<td align="center">$rec[start_time] - $rec[end_time]</td>
	<td align="center">$rec[status]</td>
	<td align="center">$link</td>
</tr>

ROW;
	}
} else 
	echo '<tr><td colspan=6 align="center" class="error">Data is not available!</td></tr>';
?>
</table>