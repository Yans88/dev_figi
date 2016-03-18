<style>
.leftlink{
   margin-left:150px;
}
</style>

<?php
if (!defined('FIGIPASS')) exit;


$_page = isset($_GET['page']) ? $_GET['page'] : 1;


$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = count_facility_fixed();
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;
$data = get_facility_fixed($_start, $_limit);
?>
<br/>
<div id="submodhead" >
<div align="left" valign="middle" class="leftlink" >
<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<h2 style="text-align:center;">Fixed item List</h2>
<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th>Facility/oom</th>
  <th>Class</th>
  <th>Status</th>
  <th>Start Date</th>
  <th>End Date</th>
  <th width=50>Action</th>
</tr>

<?php
$counter = $_start+1;
foreach($data as $rec){
	//$edit_link = null;
	//$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	$startDate = date('d-M-Y H:i', strtotime($rec['start_date']));
	if($rec['status'] == 1){
		$stts = 'In use';
		$end = 'Not Available';
	}else{
		$stts = 'End used/Released';
		$end = date('d-m-Y H:i', strtotime($rec['end_date']));
	}
	echo <<<DATA
	<tr $_class>
	<td align="right">$counter</td>
	<td>$rec[location_name]</td>
	<td>$rec[id_class]</td>
	<td>$stts</td>	
	<td>$startDate</td>
	<td >$end</td>	
	<td align="center" nowrap>
	<a href="?mod=facility&act=fixedview&id=$rec[id_trans]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	
	</td>
	</tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=11 class="pagination">';
echo make_paging($_page, $total_page, './?mod=facility&sub=facility&act=fixList&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>

