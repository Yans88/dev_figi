<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $query  = "SELECT * FROM manufacturer WHERE id_manufacturer = $_id";
  $rs = mysql_query($query);
  if (mysql_num_rows($rs) > 0) {
	  $data = mysql_fetch_array($rs);
	  $query = "DELETE FROM manufacturer WHERE id_manufacturer = $_id";
	  $rs = mysql_query($query);
      $_msg = "Manufacturer '$data[manufacturer_name]' deleted!";
	   user_log(LOG_DELETE, 'Delete manufacturer '. $data['manufacturer_name']. '(ID:'. $_id.')');
  } else
	$_msg = "Manufacturer is not found!";
} else 
	$_msg = "Manufacturer's ID is not specified!";
	  

// logging
/*
$last_time = date("Y-m-d g:i:s");
$viewlog="INSERT INTO log_table VALUES(null,'".$_id."','".$data_item['asset_no']."','','','".$last_time."','','".USERNAME."','".$last_status."')";
mysql_query($viewlog);
*/

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
