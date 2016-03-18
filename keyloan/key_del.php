<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $data = get_key($_id);
  print_r($data);
  if (count($data) > 0) {
        // delete stock
        $query = "DELETE FROM key_item WHERE id_item = $_id";
		mysql_query($query);
        // delete item
		
        
		$_msg = "Key Item '$data[serial_no]' was deleted!";
        
        user_log(LOG_DELETE, 'Delete key '. $data['serial_no']. '(ID:'. $_id.')');
  } else
	$_msg = "Key item is not found!";
} else 
	$_msg = "Key's is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/><br/>
<a href="./?mod=keyloan&act=list"> Back to Title List</a> 