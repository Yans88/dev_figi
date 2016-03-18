<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $data = get_consumable($_id);
  if (count($data) > 0) {
        // delete item
		$query = "DELETE FROM consumable_item  WHERE id_item = $_id";
		mysql_query($query);        
        
		$_msg = "Consumable item '$data[item_name]' was deleted!";
        
        user_log(LOG_DELETE, 'Delete consumable item '. $data['item_name']. '(ID:'. $_id.')');
  } else
	$_msg = "Item is not found!";
} else 
	$_msg = "Item's ID is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/><br/>
<a href="./?mod=consumable&act=list"> Back to Title List</a> 