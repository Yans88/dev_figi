<?php
//if (!defined('FIGIPASS')) exit;
include '../util.php';
include '../common.php';
require '../authcheck.php';
include '../calendar/calendar_util.php';


if (preg_match('/(\d+)-(\d+)/', $_GET['id'], $matches)){
    $id = $matches[1];
    $dt = $matches[2];
//print_r($_GET);
    
    if ($_GET['opt']=='only-me') $part = 1;
    else if ($_GET['opt']=='me-follow') $part = 2;
    else $part = 3;
    
    delete_event($id, date('Y-m-d', $dt), USERID, $_GET['reason'], $part);
}

ob_clean();
header('Location: ../?mod=calendar');
ob_end_flush();

?>