<?php
if (!defined('FIGIPASS')) exit;

if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$facility_data = get_facility($_id);
$schedule_data = get_schedules($_id);
$item_count = count($schedule_data);
?>
<br/><h4>Time sheet for facility "<?php echo $facility_data['facility_no']?>"</h4>
<!--
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
<br/>
-->
<?php
if ($i_can_create) {
?>
<div align="left" valign="middle" style="width: 200px">
<a href="./?mod=facility&sub=schedule&act=edit&id=<?php echo $_id?>" title="Add new periode">
	<img width=16 height=16 border=0 src="images/add.png"></a>
    &nbsp; 
<a href="./?mod=facility&sub=schedule&act=gen&id=<?php echo $_id?>" 
        onclick="return confirm('Generate time sheet will remove existing time sheet. Continue?')" title="Generate Time sheet">
	<img width=16 height=16 border=0 src="images/script.png"></a>
    &nbsp; 
<a href="./?mod=facility&sub=schedule&act=copy&id=<?php echo $_id?>"  title="Copy time sheet from other facility">
	<img width=16 height=16 border=0 src="images/copy.png"></a>
</div>
<?php
}
?>
<table width="200" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=20 valign="top">
  <th width=100>Periode</th>
  <th width=100>Action</th>
</tr>
<?php
if ($item_count > 0){
	$row = 0;
	foreach ($schedule_data as $rec){
		$row++;
        $period = "$rec[time_start] - $rec[time_end]";
		$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
		$link  = '<a href="./?mod=facility&sub=schedule&act=edit&id='.$rec['id_facility'].'&sid='.$rec['id_time'].'">edit</a> | ';
		$link .= '<a href="./?mod=facility&sub=schedule&act=del&id='.$rec['id_time'].'"';
        $link .= ' onclick="return confirm(\'Are you sure delete period '.$period.' ?\')">delete</a> ';
		//$link .= '<a href="./?mod=facility&sub=schedule&act=view&id='.$rec['id_facility'].'">schedule</a>';
		echo <<<ROW
<tr $class>
	<td align="center">$period</td>
	<td align="center">$link</td>
</tr>

ROW;
	}
} else 
	echo '<tr><td colspan=6 align="center" class="error">Data is not available!</td></tr>';
?>
