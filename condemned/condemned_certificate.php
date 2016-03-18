<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_sign = isset($_GET['sign']) ? $_GET['sign'] : 0;
$_dl = isset($_GET['dl']) ? $_GET['dl'] : false;
$_ref = isset($_GET['ref']) ? $_GET['ref'] : 0;

$certificate_type = 'condemned/condemned_certificate-default.php';
if (!empty($config['template'])){
    $path = 'condemned/condemned_certificate-' . $config['template']. '.php';
    if (file_exists($path)) $certificate_type = $path;
}

$output = ($_dl) ? 'D' : 'I'; // I-nline, D-ownload
$file_name = 'Certificate_of_Condemnation.pdf';

require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');

$pdf_header_logo = "logo_print.png";
$pdf_header_logo_width = 60;
$pdf_header_title = 'Certificate of Condemnation';
$pdf_header_string = "FiGi - Productivity Tools\n".FIGI_URL;
$generate_date = date('d F Y');

//Close and output PDF document
ob_clean();
//echo $certificate_type;
require_once ($certificate_type);
ob_end_flush();
//============================================================+
// END OF FILE                                                
//============================================================+
?>