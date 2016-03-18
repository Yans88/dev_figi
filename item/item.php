<?php
if (!defined('FIGIPASS')) exit;
  
if ($_act == null) $_act = 'list';

$_path = 'item/item_' . $_act . '.php';
  
if (!file_exists($_path)) 
	$_path = 'item/item_list.php';

  include($_path);
?>
