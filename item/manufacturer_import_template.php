<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Manufacture_template.csv");
echo "Manufacture Name\n";
echo "PT.OPS\n";
ob_end_flush();
exit;
?>