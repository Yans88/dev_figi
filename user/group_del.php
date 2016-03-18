<?php
if (!defined('FIGIPASS')) exit;
$_id = !empty($_GET['id']) ? $_GET['id'] : 0;

if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}
if (GRPADM == $_id){
    echo '<p class="error" style="margin-top: 100px">You can not delete group "Administrator"!</p>';
    return;
}

$_msg = null;
$_name = null;

$query = 'SELECT group_name FROM `group` WHERE id_group='. $_id;
$rs = mysql_query($query);
$rec = mysql_fetch_row($rs);
$_name = $rec[0];

// delete privileges for the group
$query = "DELETE FROM access WHERE id_group=$_id";
mysql_query($query);
// delete the group
$query = "DELETE FROM `group` WHERE id_group=$_id";
mysql_query($query);
if (mysql_affected_rows() > 0) {
    echo '<br><div class="error">Group "'.$_name.'" is deleted!</div><br>';
    user_log(LOG_DELETE, 'Delete group '. $_name. '(ID:'. $_id.')');
}

?>
<a href="./?mod=user&sub=group"> Back to Group List</a> 