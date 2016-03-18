<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_doc = isset($_GET['doc']) ? $_GET['doc'] : null;

$certificate_type = 'condemned/condemned_schedule-default.php';
if (!empty($config['template'])){
    $path = 'condemned/condemned_schedule-' . $config['template']. '.php';
    if (file_exists($path)) $certificate_type = $path;
}

$output = 'I'; // I-nline, D-ownload
$file_name = 'Condemnation_Item.pdf';
$pdf_page_orientation = 'L';// L - landscape, P - Portrait

require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');

ob_clean();
require_once ($certificate_type);
ob_end_flush();

?>