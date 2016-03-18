<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$dept = USERDEPT ;
if ($_id > 0) {
  $query  = "SELECT * FROM category WHERE id_category = $_id";
  $rs = mysql_query($query);
  if (mysql_num_rows($rs) > 0) {
	  $data = mysql_fetch_array($rs);
	  $query = "DELETE FROM department_category WHERE id_category = $_id AND id_department = '$dept'";
	  $rs = mysql_query($query);
      $_msg = "Category '$data[category_name]' has been deleted!";
	  user_log(LOG_DELETE, 'Delete category '. $data['category_name']. '(ID:'. $_id.')');
  } else
	$_msg = "Category is not found!";
} else 
	$_msg = "Category's ID is not specified!";
	  

?>
<script>
alert("<?php echo $_msg ?>");
location.href="./?mod=item&sub=category";
</script>