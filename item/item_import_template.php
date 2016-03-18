<?php
if (!defined('FIGIPASS')) exit;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=item_import_template.csv");
echo "Serial No, Model No, Issued To, Issued Date, Category, Vendor, Manufacturer, Department,Brand, Location, Host Name, Brief, Cost, Store Type,Invoice No, Date of Purchase, Warranty Period, Warranty End Date, Status, Status Update, Update Defect, Vendor Contact No 1, Vendor Contact Email 1, Vendor Contact No 2, Vendor Contact Email 2\n";
echo "5439076666,Aspire 4788,Administrator,4/21/2015  17:00:00 AM,Notebook,NCS,Acer,ICT,Acer,Computer Lab 1,Host,Acer Zend,1999,Non-Expendables,INV-0981111,1/10/2012  15:00:00 AM,24,1/10/2014 10:00,Onloan,4/21/2015 10:39,released from student usage,9876,hnsen.mail.com,562444,hnases.mail2.com2\n";
ob_end_flush();
exit;
?>