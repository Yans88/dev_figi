<?php
if (!defined('FIGIPASS')) exit;

require 'sms_util.php';
if (empty($_sub)) $_sub = 'sms_list';

$mod_url = './?mod=sms';
$submod_url = $mod_url;
$current_url = $mod_url;


$_path = 'sms/' . $_sub . '.php';
if (!file_exists($_path)) $_sub = 'sms_list';
$_path = 'sms/' . $_sub . '.php';
//echo $_path;
if (!SUPERADMIN) 
	require 'unauthorized.php';
else {

?>

<div class="mod_wrap">
  <div class="mod_title"><h3>SMS Management</h3></div>
  
</div>
<div class="clear"></div>

<?php

	require($_path);
}


