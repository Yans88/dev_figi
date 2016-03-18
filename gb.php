<?php
ob_start();
$code = isset($_GET['barcode']) ? $_GET['barcode'] : null;

include 'barcode_util.php';

$im = create_barcode($code);
ob_clean();
header('Content-type: image/png');
imagepng($im);
imagedestroy($im);
ob_end_flush();
?>