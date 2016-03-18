<?php 
include './util.php';
include './common.php';
include './user/user_util.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_act = isset($_POST['act']) ? $_POST['act'] : null;
$_msg = null;

$result = 0;
if ($_act == 'delete') {
	$query  = "DELETE FROM location WHERE id_location = '$_id'";
	mysql_query($query);
	$result = mysql_affected_rows();
	user_log(LOG_UPDATE, 'Delete location '. $_name. '(ID:'. $_id.')');
} else {
	$query  = "REPLACE INTO location (id_location, location_name, location_desc) value( '$_id', '$_name', '$_name')";
	mysql_query($query);
	$result = mysql_affected_rows();
	user_log(LOG_UPDATE, 'Update location '. $_name. '(ID:'. $_id.')');
}
echo $result;
?>