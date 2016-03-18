<?php
if (!defined('FIGIPASS')) exit;

if ($_act == null) $_act = 'use';
$submod_url = $mod_url . '&sub=usage';
$current_url = $submod_url . '&act'.$_act;

$_path = 'facility/usage_' . $_act . '.php';

if (file_exists($_path))
  include $_path;
else 
  echo 'Unknown module or action!';



