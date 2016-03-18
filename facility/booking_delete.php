<?php
include '../common.php';
include 'facility_util.php';

$_id = (isset($_POST['id'])&& !empty($_POST['id'])) ? $_POST['id'] : 0;
$_submitcode = (isset($_POST['submitcode'])&& !empty($_POST['submitcode'])) ? $_POST['submitcode'] : null;
$_remark = (isset($_POST['remark'])&& !empty($_POST['remark'])) ? $_POST['remark'] : null;
$_option = (isset($_POST['option'])&& !empty($_POST['option'])) ? $_POST['option'] : null;
$_referer = preg_match('/facility/', $_SERVER['HTTP_REFERER']);
$_msg = 'ERR';

if (($_id > 0) && ($_submitcode == 'remove') && !empty($_remark) && $_referer){
    $_msg = 'OK';
    delete_book($_id, $_SESSION['figi_userid'], $_remark, $_option);
}
echo $_msg;
?>