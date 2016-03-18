<?php

if (!defined('FIGIPASS')) exit;
require_once('item/item_util.php');

$default_term =  'tracking';
$default_by =  'category';

$_term = !empty($_GET['term']) ? $_GET['term'] : $default_term;
$_by = !empty($_GET['by']) ? $_GET['by'] : $default_by;
$_act = !empty($_GET['act']) ? $_GET['act'] : 'view';

if (!empty($_act) && ($_act != 'view'))
	$path = 'report/'.$_sub.'_' . $_term . '_' . $_act . '.php';
else
	$path = 'report/'.$_sub.'_' . $_term . '_by_' . $_by . '.php';
	
//echo $path;

if (!file_exists($path))
	$path = 'report/'.$_sub.'_' . $default_term . '_by_' . $default_by . '.php';

include $path;
?>