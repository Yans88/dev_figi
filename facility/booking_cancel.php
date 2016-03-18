<?php
//if (!defined('FIGIPASS')) exit;
session_start();
//ob_start();

$_id = (isset($_POST['id'])&& !empty($_POST['id'])) ? $_POST['id'] : 0;
$_submitcode = (isset($_POST['submitcode'])&& !empty($_POST['submitcode'])) ? $_POST['submitcode'] : null;
$_remark = (isset($_POST['remark'])&& !empty($_POST['remark'])) ? $_POST['remark'] : null;
$_referer = preg_match('/facility/', $_SERVER['HTTP_REFERER']);
$_msg = 'ERR';

if (($_id > 0) && ($_submitcode == 'cancel') && !empty($_remark) && $_referer){
    $_msg = 'OK';
    print_r($_SESSIONS);
    //cancel_book($_id, USERID, $_remark);
}
echo $_msg;
?>