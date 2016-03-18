<?php
if (!defined('FIGIPASS')) exit;
require 'item/comparison_util.php';

if ($_act == null) $_act = 'list';
$_path = 'item/comparison_asset_' . $_act . '.php';


 
if (!file_exists($_path)) 
	$_path = 'item/comparison_asset_list.php';


include($_path);
?>
