<?php

if (!defined('FIGIPASS')) exit;
//require_once('item/item_util.php');
require_once('fault/fault_util.php');

$default_term =  'frequency';
$default_by =  'category';

$_term = !empty($_GET['term']) ? $_GET['term'] : $default_term;
$_by = !empty($_GET['by']) ? $_GET['by'] : $default_by;

$path = 'report/'.$_sub.'_' . $_term . '_by_' . $_by . '.php';
if (!file_exists($path))
	$path = 'report/'.$_sub.'_' . $default_term . '_by_' . $default_by . '.php';

include $path;
?>