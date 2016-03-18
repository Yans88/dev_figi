<?php 
include '../util.php';
include '../common.php';
include '../user/user_util.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_msg = null;

$result = 0;
if ($_id > 0) {
	$query  = "UPDATE vendor SET vendor_name = '$_name' WHERE id_vendor = $_id";
	mysql_query($query);
	$result = mysql_affected_rows();
	user_log(LOG_UPDATE, 'Update vendor '. $_name. '(ID:'. $_id.')');
}
echo $result;
?>