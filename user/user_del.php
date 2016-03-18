<?php
if (!defined('FIGIPASS')) exit;
$_id = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;

if (1 == $_id){ // super admin
    echo '<p class="error" style="margin-top: 100px">You can not delete account "Administrator"!</p>';
    return;
}
if (USERID == $_id){ // own account
    echo '<p class="error" style="margin-top: 100px">You can not delete your own account!</p>';
    return;
}

$_msg = null;

$query = 'SELECT full_name FROM user WHERE id_user =' . $_id;
$rs = mysql_query($query);
$rec = mysql_fetch_array($rs);
$name = $rec['full_name'];
  
$query = "DELETE FROM user WHERE id_user=" . $_id;
mysql_query($query);
if (mysql_affected_rows() > 0) {
    user_log(LOG_DELETE, 'Delete user '. $name. '(ID:'. $_id.')');
    echo '<br/><div class="error">Delete user "'.$name.'" successfull!<br></div>';
} else 
    echo '<br/><div class="error">User "'.$name.'" fail to delete!<br></div>';

?>
<br/>
<a href="./?mod=user"> Back to User List</a> 