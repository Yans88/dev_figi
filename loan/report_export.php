<?php

if (!defined('FIGIPASS')) exit;

$default_by =  'category';
$_by = !empty($_GET['by']) ? $_GET['by'] : $default_by;

$path = 'loan/report_tracking_by_' . $_by . '.php';
if (!file_exists($path))
	$path = 'loan/report_tracking_by_' . $default_by . '.php';

/*
	echo"<a href='export_summary_trackin_by_category.php' style='font-size:12px !important; font-family:Arial, Helvetica, sans-serif !important;'>Export to Excel 2003</a>";
	echo'<h3>In Terms Of Category<h3>';
	include 'summary_by_category.php';
	echo'<br><h3>In Terms Of Department<h3><br>';
	echo"<a href='export_summary_trackin_by_department.php' style='font-size:12px !important; font-family:Arial, Helvetica, sans-serif !important;'>Export to Excel 2003</a>";
	include 'summary_by_department.php';
*/
	
//echo '<h2>Inventory Tracking Report</h2>';
//include $path;
?>
<br/>
<br/>
<br/>
<h2>Currently in Develpment Status!</h2>