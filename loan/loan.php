<?php
if (!defined('FIGIPASS')) exit;
$item_id = isset($_GET['item']) ? $_GET['item'] : 0;
$_msg = null;
if ($_act == null)   $_act = 'list';
if ($_sub == null)   $_sub = 'request';


$_path = 'loan/loan_' . $_act . '.php';

if (file_exists($_path)){
   echo '<script type="text/javascript" src="./js/signature_pad.js"></script>';
   include $_path;
} else 
  echo 'Unknown module or action!';

?>


