<?php
if (!defined('FIGIPASS')) exit;
$_msg = null;
if ($_act == null) $_act = 'list';
//if ($_sub == null) $_sub = 'request';


$_path = 'machrec/repair_' . $_act . '.php';

if (file_exists($_path))
  include $_path;
else 
  echo 'Unknown module or action!';

?>



