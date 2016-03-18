<?php

include '../util.php';
include '../common.php';
include 'condemned_util.php';

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_status = isset($_GET['status']) ? $_GET['status'] : 0;

$data = get_condemned_signature($_id, 'issue');
list($info, $img) = explode(',', $data);
/*
$im = imagecreatefrompng($img);
//print_r($im);           
ob_clean();
imagepng($im);
imagedestroy($im);
ob_end_flush();
*/
header('Content-type: image/png');
echo base64_decode($img);
?>