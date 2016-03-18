<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $data = get_deskcopy($_id);
  if (count($data) > 0) {
        // delete stock
        $query = "DELETE FROM deskcopy_stock WHERE id_title = $_id";
		mysql_query($query);
        // delete item
		$query = "DELETE FROM deskcopy_item  WHERE id_title = $_id";
		mysql_query($query);        
        // delete title
		$query = "DELETE FROM deskcopy_title WHERE id_title = $_id";
		mysql_query($query);
        
		$_msg = "Deskcopy title '$data[title]' was deleted!";
        
        user_log(LOG_DELETE, 'Delete title '. $data['title']. '(ID:'. $_id.')');
  } else
	$_msg = "Title is not found!";
} else 
	$_msg = "Title's ID is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/><br/>
<a href="./?mod=deskcopy&act=list"> Back to Title List</a> 