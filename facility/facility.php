<?php

if (!defined('FIGIPASS')) exit;


if (defined('USE_NEW_BOOKING') && USE_NEW_BOOKING)
	require_once 'booking/booking_util.php';

$_msg = null;
if ($_act == null) $_act = 'list';
//if ($_sub == null) $_sub = 'request';
$submod_url = $mod_url . '&sub=facility';
$current_url = $submod_url . '&act'.$_act;

$_path = 'facility/facility_' . $_act . '.php';

if (file_exists($_path))
  include $_path;
else 
  echo 'Unknown module or action!';


?>



