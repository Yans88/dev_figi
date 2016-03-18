<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_facility_list();
}

$start = 0;
$limit = RECORD_PER_PAGE;
if ($_page > 0) $start = ($_page-1) * $limit;

$total_item = count_facilities();
$total_page = ceil($total_item/$limit);
$data = get_facilities($start, $limit);

?>
<br/>
<?php
if ($i_can_create && !SUPERADMIN) {
?>
<div align="left" valign="middle" style="width: 800px">
<a class="button" href="./?mod=facility&sub=facility&act=edit">Add Facility</a>
</div>
<?php
} // admin non-superadmin
if ($total_item > 0){
?>
<table width=800 cellpadding=2 cellspacing=1 class="facility_table" >
<tr>
  <th >Facility No</th>
  <!--
  <th width=80>Duration / Period</th>
  <th width=80>Max. Number of Period</th>
  <th width=80>Lead Time </th>
  <th width=90>Time Usage</th>
  -->
  <th width=80>Notification</th>
  <th width=80>Email</th>
  <th width=80>Handphone</th>
  <th width=80>Action</th>
</tr>
<?php
$row = 0;
foreach ($data as $rec){
	$row++;
	$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
	$link  = '<a href="./?mod=facility&act=view&id='.$rec['id_facility'].'" alt="view"><img class="icon" src="images/loupe.png"></a> ';
	if ($i_can_update)
		$link .= '<a href="./?mod=facility&act=edit&id='.$rec['id_facility'].'" alt="edit"><img class="icon" src="images/edit.png"></a> ';
	if ($i_can_delete)
		$link .= '<a href="./?mod=facility&act=del&id='.$rec['id_facility'].'" alt="delete"
            onclick="return confirm(\'Are you sure delete the facility \\\''.$rec['facility_no'].'\\\'?\')"><img class="icon" src="images/delete.png"></a>';
	//$link .= ' | <a href="./?mod=facility&sub=schedule&act=view&id='.$rec['id_facility'].'">schedule</a>';
	if($rec[status_notification]==1) $status_notif = "Enable"; else $status_notif = "Disable";
	echo <<<ROW
<tr $class>
	<td align="left">$rec[facility_no]</td>
	<!--
	<td align="center">$rec[period_duration]</td>
	<td align="center">$rec[max_period]</td>
	<td align="center">$rec[lead_time]</td>
	<td align="center">$rec[time_start] - $rec[time_end]</td>
	-->
	<td align="center">$status_notif</td>
	<td align="center">$rec[email]</td>
	<td align="center">$rec[handphone]</td>
	<td align="center">$link</td>
</tr>

ROW;
	}

echo '<tr ><td colspan=6 class="pagination">';
echo make_paging($_page, $total_page, './?mod=facility&sub=facility&act=list&page=');
echo  '<div class="exportdiv"><a href="./?mod=facility&sub=facility&act=list&do=export" class="button">Export Data</a></div></td></tr></table>';
        
} else
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
?>

<br/>
