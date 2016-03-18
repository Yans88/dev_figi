<?php 
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_cat = isset($_GET['cat']) ? $_GET['cat'] : 0;
if (!empty($_POST['id_category'])) $_cat = $_POST['id_category'];
$_msg = null;
$dept = USERDEPT ;


if (isset($_POST['save'])) {
	$id_category = $_POST['id_category'];
	$spec_name = mysql_real_escape_string($_POST['spec_name']);
	/*
	// check if duplicate name
	$query  = "SELECT count(*) 
				FROM specification 
				WHERE spec_name = '$_POST[spec_name]' AND id_category='$id_category'";
	$rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	if ($rec[0] == 0) 
	*/
	{
		$order_no = 0;
		$query = "SELECT COUNT(*) FROM specification WHERE id_category = '$id_category'";
		$rs = mysql_query($query);
		if ($rs && mysql_num_rows($rs)>0){
			$rec = mysql_fetch_row($rs);
			$order_no = $rec[0];
		}
		$query = "REPLACE INTO specification (spec_id, spec_name, id_category, order_no) 
				  VALUES ($_id, '$spec_name', '$id_category', $order_no)";
		$rs = mysql_query($query);
		if (mysql_affected_rows()>0){
			if ($_id == 0){
				$_id = mysql_insert_id();
				user_log(LOG_CREATE, 'Create Specification '. $_POST['spec_name']. '(ID:'. $_id.')');
			} else
				user_log(LOG_UPDATE, 'Update Specification '. $_POST['spec_name']. '(ID:'. $_id.')');
		}
		$_msg = "Specification's name updated!";
	} 
	/*
	else 
		$_msg = "Error : Duplicated Specification's name!";
	*/
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=item&sub=specification&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($_id > 0) {
  $query  = "SELECT * FROM specification WHERE spec_id = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
  $_cat = $data_item['id_category']; 
} else {

  $data_item['spec_id'] = '0';
  $data_item['spec_name'] = '';
}
   
$caption = ($_id>0) ? 'Edit Specification' : 'Create New Specification';     
?>
<script>
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
</script>
<div style="width: 450px" class="itemlist middle center">
<br/>
<h4 class="center"><?php echo $caption?></h4>
<br/>

<form method="POST">

<table width="99%" class="itemlist" cellpadding=4 cellspacing=0>
<tr>
  <td width=120>Category</td>
  <td><?php echo build_combo('id_category', get_category_list('equipment', $dept), $_cat)?></td>
</tr>
<tr class="alt">
  <td>Specification Name </td>
  <td><input type="text" name="spec_name" value="<?php echo $data_item['spec_name']?>" style="width:300px"></td>
</tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save" >Save</button>
<button type="button" onclick="location.href='./?mod=item&sub=specification';">Cancel</button>
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[spec_name]?')">Delete</button>
TEXT;
}
?>
</form>
<br/>
<?php
if ($_msg != null)
	echo '<script>alert("' . $_msg . '"); location.href = "./?mod=item&sub=specification&act=list&cat='.$_cat.'";</script>';
?>
</div>
