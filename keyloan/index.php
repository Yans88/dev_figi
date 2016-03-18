<?php
/*
index
*/
//session_start();
//ob_start();
$figi_dir = '../';

include $figi_dir . 'util.php';
include $figi_dir . 'common.php';
//include $figi_dir . 'authcheck.php';

$_mod = (!empty($_GET['mod'])) ? $_GET['mod'] : 'loan';
$_sub = (!empty($_GET['sub'])) ? $_GET['sub'] : null;
$_act = (!empty($_GET['act'])) ? $_GET['act'] : null;

$toggle = ($_mod == 'return') ? 'loan' : 'return';

include 'header.php';
$path = $_mod . '.php';

if (!file_exists($path))
    $path = 'loan.php';
//echo $path;
include $path;
include 'footer.php';

ob_end_flush();
?>