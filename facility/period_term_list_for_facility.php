<?php
if (!defined('FIGIPASS')) exit;


$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$id_facility = isset($_GET['id']) ? $_GET['id'] : 0;
$facility = get_facility($id_facility);

$_do = 'nothing';
if ($_do == 'export'){ }
$edit['term'] = null;
$edit['id_term'] = 0;
$edit['active'] = 0;
$edit['valid_from'] = null;
$edit['valid_from_display'] = null;
$edit['valid_to'] = null;
$edit['valid_to_display'] = null;
if (!empty($_POST['edit'])){
	$edit = period_term_get($_POST['edit']);
} 
if (!empty($_POST['save'])){
	
	$term = mysql_real_escape_string($_POST['term']);
	$valid_from  = convert_date($_POST['valid_from']);
	$valid_to  = convert_date($_POST['valid_to']);
	$active = $_POST['active'];
	$id_term = isset($_POST['id_term']) ? $_POST['id_term']:0;
	$modified_by = USERID;
	if ($id_term==0)
		$query = "INSERT INTO facility_period_term(id_term, term, valid_from, valid_to, active, modified_by, modified_on) VALUE 
					($id_term, '$term', '$valid_from', '$valid_to', $active, $modified_by, now())";
	else
		$query = "REPLACE INTO facility_period_term(id_term, term, valid_from, valid_to, active, modified_by, modified_on) VALUE 
					($id_term, '$term', '$valid_from', '$valid_to', $active, $modified_by, now())";
	if (mysql_query($query)){
		if ($id_term == 0)
			$id_term = mysql_insert_id();
		if ($id_term > 0)
			$_msg = 'Period term information has been saved sucessfully';
		else
			$_msg  = 'Failed to save period term information!';
	} else
		$_msg  = 'Failed to save period term information!';

	//error_log(mysql_error().$query);
	$url = '?mod=facility&sub=period&act=term_list';
	redirect($url, $_msg);
	
} else 
if (!empty($_POST['dele'])){
	$query = "DELETE FROM facility_period_map WHERE id_facility = $id_facility AND  id_term = $_POST[id_term]";
	if (mysql_query($query)){
		$ok = mysql_affected_rows();
		if ($ok) $_msg = 'Selected period term has been un-assignedsucessfully!';
		else $_msg = 'Error occurred when un-assign the period term!';
			
		$url = '?mod=facility&sub=period&act=term_list_for_facility';
		redirect($url, $_msg);
	}
}

$data = facility_period_term_rows($id_facility);
$total_item = count($data);
?>
<div style="text-align: left">
<h3>View Period Term for Facility "<?php echo $facility['facility_no']?>"</h3>

<form method="post" id="frm_period_term">
<input type="hidden" name="id_term" value="<?php echo $edit['id_term']?>">
</form>
<!-- 
action="./?mod=facility&sub=period&act=term_list"
-->
<form method="post" id="frm_update_period_term" >
<input type="hidden" name="id_term" value=0>
</form>

<script>
function dele(id_term, title){
	var f = $('#frm_update_period_term').get(0);
	if (confirm('Do you sure un-assign the period term "'+title+'" from the facility?')){
		f.id_term.value = id_term;
		$(f).append('<input type="hidden" name="dele" value=1>');
		f.submit();
	}
}

</script>
</div>
<?php
 
if ($total_item > 0){
?>
<table width="100%" cellpadding=2 cellspacing=1 class="facility_table tbl_period_term_list" >
<tr>
  <th >Term</th>
  <th width=80>Valid From</th>
  <th width=80>Valid To</th>
  <th >Modified By </th>
  <th width=130>Modified On</th>
  <th width=80>Action</th>
</tr>
<?php
$row = 0;
foreach ($data as $rec){
	$row++;
	$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
	$link  = '<a href="./?mod=facility&sub=period&act=timesheet_list_for_facility&id='.$rec['id_term'].'&id_facility='.$id_facility.'" alt="view period timesheet"><img class="icon" src="images/script.png"></a> ';
	if ($i_can_update)
	if ($i_can_delete)
		$link .= '<a href="javascript:dele('.$rec['id_term'].',\''.$rec['term'].'\')" alt="delete" ><img class="icon" src="images/delete.png"></a>';
	echo <<<ROW
<tr $class>
	<td >$rec[term]</td>
	<td align="center">$rec[valid_from_display]</td>
	<td align="center">$rec[valid_to_display]</td>
	<td >$rec[modified_by_name]</td>
	<td align="center">$rec[modified_on_display]</td>
	<td align="center">$link</td>
</tr>

ROW;
	}

echo '</table>';
        
} else
    echo '<p class="error" style="margin-top: 10px">No period term assigned to the facility. Assign a period term <a href="./?mod=facility&sub=period&act=term_assign&id='.$id_facility.'">here</a>.</p>';
?>

<br/>
