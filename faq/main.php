<?php
if (!defined('FIGIPASS')) exit;

include_once 'faq/faq_util.php';
//include_once 'loan/loan_util.php';
  
if ($_sub == null)	$_sub = 'faq';
if ($_act == null)	$_act = 'list';

$_path = 'faq/' . $_sub . '.php';

//require 'keyloan/util.php';

if (!file_exists($_path)) 
	return;

 
?>


<?php
  include($_path);
?>

