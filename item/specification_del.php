<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $query  = "SELECT * FROM specification WHERE spec_id = $_id";
  $rs = mysql_query($query);
  if (mysql_num_rows($rs) > 0) {
	  $data = mysql_fetch_array($rs);
	  $query = "DELETE FROM specification WHERE spec_id = $_id";
	  $rs = mysql_query($query);
      $_msg = "Specification '$data[spec_name]' deleted!";
	  user_log(LOG_DELETE, 'Delete spec '. $data['spec_name']. '(ID:'. $_id.')');
  } else
	$_msg = "Specification is not found!";
} else 
	$_msg = "Specification's ID is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/><br/>
<a href="./?mod=item&sub=specification&act=list">Back to Specification List</a> 