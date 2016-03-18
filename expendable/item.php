<?php
if (!defined('FIGIPASS')) exit;

//include 'title/title_util.php';

if ($_act == null)	$_act = 'list';
if($_sub == null) $_sub = 'item';
$_path = 'expendable/'.$_sub.'_' . $_act . '.php';
  
if (!file_exists($_path)) 
	$_path = 'expendable/item_list.php';


// require_once('item/item_util.php');

include($_path);
?>
</div>
