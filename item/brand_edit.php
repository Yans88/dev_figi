<?php 
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;


if (isset($_POST['save'])) {
	// check if duplicate name
	$query  = "SELECT count(*) 
				FROM brand 
				WHERE (brand_name = '$_POST[brand_name]') AND (id_manufacturer = '$_POST[id_manufacturer]')";
	$rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	if ($rec[0] == 0) {
		$query = "REPLACE INTO brand (id_brand, brand_name, id_manufacturer) 
				  VALUES ($_id, '$_POST[brand_name]', '$_POST[id_manufacturer]')";
		$rs = mysql_query($query);
		if (mysql_affected_rows()>0){
			if ($_id == 0){
				$_id = mysql_insert_id();
				user_log(LOG_CREATE, 'Create brand '. $_POST['brand_name']. '(ID:'. $_id.')');
			} else
				user_log(LOG_UPDATE, 'Update brand '. $_POST['brand_name']. '(ID:'. $_id.')');
		}
		$_msg = "Brand's name updated!";
	} else 
		$_msg = "Error : duplicated brand's name!";
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=item&sub=brand&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($_id > 0) {
  $query  = "SELECT * FROM brand WHERE id_brand = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
} else {

  $data_item['id_brand'] = '0';
  $data_item['brand_name'] = '';
  $data_item['id_manufacturer'] = 0;
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
<tr><th colspan=2>Edit Brand</th></tr>
<tr>
  <td width=120>Manufacturer</td>
  <td><?php echo build_manufacturer_combo($data_item['id_manufacturer'])?></td>
</tr>
<tr class="alt">
  <td>Brand Name </td>
  <td><input type="text" name="brand_name" value="<?php echo $data_item['brand_name']?>"></td>
</tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save" >Save</button>
<button type="button" onclick="location.href='./?mod=item&sub=brand';">Cancel</button>
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[brand_name]?')">Delete</button>
TEXT;
}
?>
</form>
<br/>
<?php
if ($_msg != null)
	echo '<script>alert("' . $_msg . '"); location.href = "./?mod=item&sub=brand&act=list";</script>';
?>
