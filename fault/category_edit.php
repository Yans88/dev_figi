<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_type= !empty($_GET['type']) ? $_GET['type'] : 'equipment';
$_msg = null;


if (isset($_POST['save'])) {
	// check if duplicate name
	$query  = "SELECT count(*) 
				FROM category 
				WHERE (category_name = '$_POST[category_name]') and (id_category != $_id)";
	$rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	if ($rec[0] == 0) {
		$query = "REPLACE INTO category (id_category, category_name, category_type, id_department) 
				  VALUES ($_id, '$_POST[category_name]', '$_POST[category_type]', '$_POST[id_department]')";
		$rs = mysql_query($query);
		if (mysql_affected_rows()>0){
			if ($_id == 0){
				$_id = mysql_insert_id();
				user_log(LOG_CREATE, 'Create category '. $_POST['category_name']. '(ID:'. $_id.')');
			} else
				user_log(LOG_UPDATE, 'Update category '. $_POST['category_name']. '(ID:'. $_id.')');
		}
		$_msg = "Category's name updated!";
	} else 
		$_msg = "Error : duplicated category's name!";
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=item&sub=category&act=del&type='.$_type.'&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($_id > 0) {
  $query  = "SELECT * FROM category WHERE id_category = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
  
} else {

    $data_item['id_category'] = '--';
    $data_item['category_name'] = '';
    if ($_type == 'service')
        $data_item['category_type'] = 'SERVICE';
    else
        $data_item['category_type'] = 'EQUIPMENT';
	$data_item['id_department'] = USERDEPT;
}
   
$department_list = get_department_list();
$department_name = (!empty($department_list[$data_item['id_department']])) ? $department_list[$data_item['id_department']] : null;

?>
<script type="text/javascript" src="./js/datepicker.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo STYLE_PATH?>datepicker.css" media="screen" />

<script>
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
</script>

<form method="POST">
<input type="hidden" name="id_department" value="<?php echo $data_item['id_department']?>">
<table width=350 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2>Edit Category</th></tr>
<tr>
  <td width=100>Department </td>
  <td><?php echo $department_name?></td>
</tr>
<tr class="alt" valign="top">
  <td>Category Type</td>
  <td>
    <input type="radio" name="category_type" value="EQUIPMENT" <?php echo ($data_item['category_type']=='EQUIPMENT')?'checked':''?> > EQUIPMENT <br/>
    <input type="radio" name="category_type" value="SERVICE" <?php echo ($data_item['category_type']=='SERVICE')?'checked':''?>> SERVICE    
  </td>
</tr>
<tr>
  <td>Category Name </td>
  <td><input type="text" size=30 name="category_name" value="<?php echo $data_item['category_name']?>"></td>
</tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save">Save</button> 
<button type="button" name="cancel" onclick="location.href='./?mod=item&sub=category&type=<?php echo $_type?>'">Cancel</button> 
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[category_name]?')">Delete</button> 
TEXT;
}
?>
</form>
<br/>
<?php
if (USERDEPT > 0){
	//echo '<script>var d = document.getElementById("id_department"); d.disabled = true; </script>';
}
if ($_msg != null)
	echo '<script>alert("' . $_msg . '"); location.href = "./?mod=item&sub=category&act=list";</script>';
?>
