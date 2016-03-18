<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Maintenance_template.csv");
echo "location,nric_create_by,create_on,nric_modify_by,modify_on,asset_no,result,remark\n";
echo "comp lab 2,345445,6/23/2015 11:31,345445,6/23/2015 10:31,12333,ok,test1\n";
ob_end_flush();
exit;
?>