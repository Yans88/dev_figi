<?php

if (!defined('FIGIPASS')) exit;

$default_term =  'tracking';
$default_by =  'category';

$_term = !empty($_GET['term']) ? $_GET['term'] : $default_term;
$_by = !empty($_GET['by']) ? $_GET['by'] : $default_by;

$path = 'loan/report_' . $_term . '_by_' . $_by . '.php';
if (!file_exists($path))
	$path = 'loan/report_' . $default_term . '_by_' . $default_by . '.php';

include $path;
?>