<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=TemporaryItemImportTemplate.csv");
echo "Asset No, Serial No, Category, Brand\n";

ob_end_flush();
exit;
?>