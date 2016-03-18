<?php
if (!defined('FIGIPASS')) exit;

//include 'title/title_util.php';

if ($_act == null) 
	$_act = 'list';

$_path = 'deskcopy/title_' . $_act . '.php';
  
if (!file_exists($_path)) 
	$_path = 'deskcopy/title_list.php';


  include($_path);
?>
</div>
