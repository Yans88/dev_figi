<?php
if (!defined('FIGIPASS')) exit;

//include 'title/title_util.php';
require 'keyloan/util.php';
if ($_act == null) 
	$_act = 'list';

$_path = 'keyloan/key_' . $_act . '.php';

if (!file_exists($_path)) 
	$_path = 'keyloan/key_list.php';


  include($_path);
?>
</div>
