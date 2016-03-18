<?php
//if (!defined('FIGIPASS')) exit;
include '../util.php';
include '../common.php';
include '../calendar/calendar_util.php';

$_id = (isset($_POST['id'])&& !empty($_POST['id'])) ? $_POST['id'] : 0;
$_opt = (isset($_POST['opt'])&& !empty($_POST['id'])) ? $_POST['opt'] : null;
$_userid = (isset($_POST['userid'])&& !empty($_POST['id'])) ? $_POST['userid'] : 0;
//if ($_id == 0 && isset($_GET['id'])) $_id = $_GET['id'];
$_submitcode = (isset($_POST['submitcode'])&& !empty($_POST['submitcode'])) ? $_POST['submitcode'] : null;
$_remark = (isset($_POST['remark'])&& !empty($_POST['remark'])) ? $_POST['remark'] : null;
if (empty($_remark))
    $_remark = (isset($_POST['reason'])&& !empty($_POST['reason'])) ? $_POST['reason'] : null;
$_referer = preg_match('/calendar/', $_SERVER['HTTP_REFERER']);
$_msg = 'ERR';

    
$cur_date = time();
if (strchr($_id, '-'))
    list($_id, $cur_date) = explode('-', $_id);

//echo "$_id $cur_date $_opt $_userid $_submitcode $_referer $_remark";
if (($_id > 0) && ($_submitcode == 'remove') && !empty($_remark) && $_referer>0){
    $_msg = 'OK';
    $seldate = date('Y-m-d', $cur_date);
    $part = @$delete_commands[$_opt];
    
    delete_event($_id, $seldate, $_userid, $_remark, $part);
}
echo $_msg;
?>