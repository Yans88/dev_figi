<?php
if (!defined('FIGIPASS')) exit;

$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$start = 0;
$limit = RECORD_PER_PAGE;
if ($_page > 0) $start = ($_page-1) * $limit;

if (!empty($_POST['remove'])){
	$query = 'DELETE FROM bookable_facility WHERE id_facility ='.$_POST['id_facility'];
	mysql_query($query);
	if (mysql_affected_rows()>0)
		$msg = 'Selected facility has been deleted!';
	else
		$msg = 'Selected facility failed to be deleted!';
	redirect($current_url, $msg);
}

$total_item = bookable_facility_count();
$total_page = ceil($total_item/$limit);
$data = bookable_facility_rows($start, $limit);

?>
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		
<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />

<div class="submod_wrap">
	<div class="submod_title"><h4>List of Facility for Booking</h4></div>
	<div class="submod_links"> 
	<a class="button" href="<?php echo $submod_url?>&act=edit"> Add Facility</a>
	</div>
</div>
<div class="clear"></div>
<table  class="itemlist bookable-facility" >
<tr>
  <th width=40>No</th>
  <th >Facility Name</th>
  <th >Description</th>
  <th >Period Term</th>
  <th width=80>Term Status</th>
  <th width=80>Equipment</th>
  <th width=80>Action</th>
</tr>
<?php
if ($total_item > 0) {
$row = 0;
foreach ($data as $rec){
	$row++;
	$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
	$link  = null;//'<a href="'.$submod_url.'&act=view&id='.$rec['id_facility'].'" alt="view"><img class="icon" src="images/loupe.png"></a> ';
	//$link = '<a class="fancybox" href="javascript:equipment('.$rec['id_facility'].')">equipment</a>';
	//if ($i_can_update)
		$link .= '<a href="'.$submod_url.'&act=edit&id='.$rec['id_facility'].'" alt="edit"><img class="icon" src="images/edit.png"></a> ';
	if ($i_can_delete)
		$link .= '<a href="javascript:dele('.$rec['id_facility'].')" alt="delete"><img class="icon" src="images/delete.png"></a>';
	//$link .= ' | <a href="./?mod=facility&sub=schedule&act=view&id='.$rec['id_facility'].'">schedule</a>';
	$term_link = $mod_url.'&sub=period_timesheet&act=list&id='.$rec['id_term'];
	$term_status = ($rec['active']) ? 'Active' : 'Not Active';
	echo <<<ROW
<tr $class>
	<td class="right">$row </td>
	<td class="left">$rec[facility_name]</td>
	<td class="left">$rec[description]</td>
	<td class="left"><a href="$term_link">$rec[term_name]</a></td>
	<td class="center">$term_status</td>
	<td class="center"><a href="javascript:equipment($rec[id_facility])">equipment</a></td>
	<td class="center">$link</td>
</tr>

ROW;
	}

echo '<tr ><td colspan=7 class="pagination">';
echo make_paging($_page, $total_page, $submod_url.'&act=list&page=');
echo  '</td></tr>';
        
} else
	echo '<tr ><td colspan=7 class="center" style="margin-top: 10px">Data is not available!.</td></tr>';
?>
</table>
<br/>
<form method="post" id="frm">
<input type="hidden" name="id_facility">
<input type="hidden" name="remove">
</form>
<script>
function dele(id){
	
	if (confirm('Do you sure delete the facility?')){
		var f = $('#frm').get(0);
		f.id_facility.value = id;
		f.remove.value = id;
		f.submit();
	}
}
function equipment(id){
	url = './?mod=booking&sub=equipment&id='+id;
	$.fancybox.open({
		href: url, type: 'iframe', padding: 5, width: 600});

}
</script>
