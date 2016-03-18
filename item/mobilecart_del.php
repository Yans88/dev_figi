<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $query  = "SELECT * FROM mobile_cart WHERE id_cart = $_id";
  $rs = mysql_query($query);
  if (mysql_num_rows($rs) > 0) {
	  $data = mysql_fetch_array($rs);
	  mysql_query("DELETE FROM mobile_cart WHERE id_cart = $_id");
	  mysql_query("DELETE FROM mobile_cart_item WHERE id_cart = $_id");
      $_msg = "Mobile Cart '$data[cart_name]' deleted!";
	   user_log(LOG_DELETE, 'Delete cart '. $data['cart_name']. '(ID:'. $_id.')');
  } else
	$_msg = "Mobile Cart is not found!";
} else 
	$_msg = "Mobile Cart's ID is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/>
<div>
<a href="./?mod=item&sub=mobilecart&act=list">Back to Mobile Cart List</a>
</div>
