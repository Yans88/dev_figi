<?php
if (!defined('FIGIPASS')) exit;

//include 'title/title_util.php';

if ($_act == null)	$_act = 'list';

$_path = 'consumable/item_' . $_act . '.php';
  
if (!file_exists($_path)) 
	$_path = 'consumable/item_list.php';


require_once('item/item_util.php');
include($_path);
?>
</div>
