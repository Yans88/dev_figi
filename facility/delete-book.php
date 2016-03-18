<?php
//if (!defined('FIGIPASS')) exit;
include '../util.php';
include '../common.php';
require '../authcheck.php';
include '../facility/facility_util.php';


if (preg_match('/(\d+)-(\d+)/', $_GET['id'], $matches)){
    $id = $matches[1];
    $dt = $matches[2];
    
    if ($_GET['opt']=='only-me') $part = 1;
    else if ($_GET['opt']=='me-follow') $part = 2;
    else $part = 3;
    
    delete_book($id, date('Y-m-d', $dt), USERID, $_GET['reason'], $part);
}

$id_facility = $_GET['id_facility'];
ob_clean();
if (strstr($_SERVER['HTTP_REFERER'], 'portal'))
    header('Location: ../?mod=portal&portal=facility&act=view_month&sub=history&id_facility='.$id_facility);
else
    header('Location: ../?mod=facility&sub=booking&id_facility='.$id_facility);
ob_end_flush();

?>
