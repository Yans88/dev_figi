<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Key_loan_template.csv");
echo "Serial No, Status, Description\n";
echo "SN812345677,Available for loan,Ex:description\n";
ob_end_flush();
exit;
?>