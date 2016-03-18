<?php
if (!defined('FIGIPASS')) exit;
require '../common.php';
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_username = null;
if ($_act == null) 
	$_act = 'list';


include 'user/user_' . $_sub . '_' . $_act . '.php';
?>