<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=StudentCsvTemplate.csv");
echo "Register Number, Full Name, NRIC, Email, Class, Year, Father Name, Father Email, Father Mobile Number, Mother Name, Mother Email, Mother Mobile Number\n";


ob_end_flush();
exit;
?>