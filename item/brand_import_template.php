<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Brand_template.csv");
echo "Brand,Manufacture\n";
echo "Acer,Acer\n";
ob_end_flush();
exit;
?>