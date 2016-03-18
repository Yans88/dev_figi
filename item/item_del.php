<?php 

if (!defined('FIGIPASS')) exit;
if (!$i_can_delete) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $query  = "SELECT * FROM item WHERE id_item = $_id";
  $rs = mysql_query($query);
  if (mysql_num_rows($rs) > 0) {
		$data = mysql_fetch_array($rs);
        // delete spec
        $query = "DELETE FROM item_specification WHERE id_item = $_id";
		$rs = mysql_query($query);
        // delete item
		$query = "DELETE FROM item WHERE id_item = $_id";
		$rs = mysql_query($query);
        
		$_msg = "Item with '$data[asset_no]' deleted!";
        
        user_log(LOG_DELETE, 'Delete item with asset no '. $data['asset_no']. '(ID:'. $_id.')');
  } else
	$_msg = "Item is not found!";
} else 
	$_msg = "Item's ID is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/><br/>
<a href="./?mod=item&sub=item&act=list"> Back to Item  List</a> 