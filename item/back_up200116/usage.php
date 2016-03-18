<?php
if (!defined('FIGIPASS')) exit;

if ($_act == '') $_act = 'use';


$_path = 'item/usage_' . $_act . '.php';
//echo $_path;
if (file_exists($_path))
  include $_path;
else 
  echo 'Unknown module or action!';



