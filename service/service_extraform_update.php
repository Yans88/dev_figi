<?php 
include '../util.php';
include '../common.php';
include '../user/user_util.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_type = isset($_POST['type']) ? $_POST['type'] : null;
$_page = isset($_POST['page']) ? $_POST['page'] : null;
$_cat = isset($_POST['cat']) ? $_POST['cat'] : null;
$_desc = isset($_POST['desc']) ? $_POST['desc'] : null;
$_msg = null;

$result = 0;
if ($_id > 0) {
	$result = save_extra_field($_id, $_name, $_type, $_desc, $_cat, $_page);
	user_log(LOG_UPDATE, 'Update extra_form '. $_name. '(ID:'. $_id.')');
}
echo $result;
?>