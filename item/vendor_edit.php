<?php 
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;


if (isset($_POST['save'])) {

	$query = "REPLACE INTO vendor (id_vendor, vendor_name, contact_no_1, contact_email_1, contact_no_2, contact_email_2) 
			  VALUES ($_id, '$_POST[vendor_name]', '$_POST[contact_no_1]', '$_POST[contact_email_1]', '$_POST[contact_no_2]', 
			  '$_POST[contact_email_2]')";
	$rs = mysql_query($query);
	if (mysql_affected_rows()>0){
		if ($_id == 0){
			$_id = mysql_insert_id();
			user_log(LOG_CREATE, 'Create vendor '. $_POST['vendor_name']. '(ID:'. $_id.')');
		} else
			user_log(LOG_UPDATE, 'Update vendor '. $_POST['vendor_name']. '(ID:'. $_id.')');
	}
	$_msg = "Vendor's name updated!";

} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=item&sub=vendor&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($_id > 0) {
  $query  = "SELECT * FROM vendor
				WHERE id_vendor = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
} else {

  $data_item['id_vendor'] = 'n/a';
  $data_item['vendor_name'] = '';
  $data_item['contact_no_1'] = '';
  $data_item['contact_email_1'] = '';
  $data_item['contact_no_2'] = '';
  $data_item['contact_email_2'] = '';
}
   

// logging
/*
$last_time = date("Y-m-d g:i:s");
$viewlog="INSERT INTO log_table VALUES(null,'".$_id."','".$data_item['asset_no']."','','','".$last_time."','','".USERNAME."','".$last_status."')";
mysql_query($viewlog);
*/

$caption = ($_id > 0) ? 'Edit Vendor' : 'Add New Vendor';

?>
<script>
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 function cancel_it(){
	location.href = './?mod=item&sub=vendor';
 }
</script>

<form method="POST">

<table width=400 class="itemlist" cellpadding=3 cellspacing=1>
<tr><th colspan=2><?php echo $caption?></th></tr>
<?php
/*
if ($_id > 0){
?>
<tr>
  <td width=160>Vendor ID </td>
  <td><?php echo $data_item['id_vendor']?></td>
</tr>
<?php
}
*/
?>
<tr class="alt">
  <td>*Vendor Name </td>
  <td><input type="text" size=30 name="vendor_name" value="<?php echo $data_item['vendor_name']?>"></td>
</tr>
<tr>
  <td>Vendor Contact No 1 </td>
  <td><input type="text" size=30 name="contact_no_1" value="<?php echo $data_item['contact_no_1']?>"></td>
</tr>
<tr class="alt">
  <td>Vendor Contact Email 1</td>
  <td><input type="text" size=30 name="contact_email_1" value="<?php echo $data_item['contact_email_1']?>"></td>
</tr>
<tr>
  <td>Vendor Contact No 2 </td>
  <td><input type="text" size=30 name="contact_no_2" value="<?php echo $data_item['contact_no_2']?>"></td>
</tr>
<tr class="alt">
  <td>Vendor Contact Email 2 </td>
  <td><input type="text" size=30 name="contact_email_2" value="<?php echo $data_item['contact_email_2']?>"></td>
</tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save" >Save</button> 
<button type="button" onclick="cancel_it()">Cancel</button> 
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[vendor_name]?')">Delete</button> 
TEXT;
}
?>
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
