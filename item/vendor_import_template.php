<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Vendor_template.csv");
echo "Vendor Name,Contact no 1, Contact email 1, Contact no 2, Contact email 2\n";
echo "HQ USER,0812345677,test@mail.com,08766666,test2@mail.com\n";
ob_end_flush();
exit;
?>