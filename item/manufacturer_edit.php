<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;


if (isset($_POST['save'])) {
	// check if duplicate name
	$query  = "SELECT count(*) 
				FROM manufacturer 
				WHERE (manufacturer_name = '$_POST[manufacturer_name]') and (id_manufacturer != $_id)";
	$rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	if ($rec[0] == 0) {
		$query = "REPLACE INTO manufacturer (id_manufacturer, manufacturer_name) 
				  VALUES ($_id, '$_POST[manufacturer_name]')";
		$rs = mysql_query($query);
		if (mysql_affected_rows()>0){
			if ($_id == 0){
				$_id = mysql_insert_id();
				user_log(LOG_CREATE, 'Create manufacturer '. $_POST['manufacturer_name']. '(ID:'. $_id.')');
			} else
				user_log(LOG_UPDATE, 'Update manufacturer '. $_POST['manufacturer_name']. '(ID:'. $_id.')');
		}
		$_msg = "Manufacturer's name updated!";
	} else 
		$_msg = "Error : duplicated manufacturer's name!";
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=item&sub=manufacturer&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($_id > 0) {
  $query  = "SELECT * FROM manufacturer WHERE id_manufacturer = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
} else {

  $data_item['id_manufacturer'] = '0';
  $data_item['manufacturer_name'] = '';
}
   

// logging
/*
$last_time = date("Y-m-d g:i:s");
$viewlog="INSERT INTO log_table VALUES(null,'".$_id."','".$data_item['asset_no']."','','','".$last_time."','','".USERNAME."','".$last_status."')";
mysql_query($viewlog);
*/

?>
<script>
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
</script>

<form method="POST">

<table width=300 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2>Edit Manufacturer</th></tr>
<tr>
  <td width=120>Manufacturer ID </td>
  <td><?php echo $data_item['id_manufacturer']?></td>
</tr>
<tr class="alt">
  <td>*Manufacturer Name </td>
  <td><input type="text" name="manufacturer_name" value="<?php echo $data_item['manufacturer_name']?>"></td>
</tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save" >Save</button>
<button type="button" name="cancel" onclick="location.href='./?mod=item&sub=manufacturer'">Cancel</button>
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[manufacturer_name]?')">Delete</button>
TEXT;
}
?>
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
