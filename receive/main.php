<?php
if (!defined('FIGIPASS')) exit;

require 'receive_util.php';
if (empty($_sub)) $_sub = 'receive';

$mod_url = './?mod=receive';
$submod_url = $mod_url;
$current_url = $mod_url;

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

$_path = 'receive/' . $_sub . '.php';
if (!file_exists($_path)) $_sub = 'receive';
$_path = 'receive/' . $_sub . '.php';

if (!$_can_view) 
	require 'unauthorized.php';
else {

?>

<div class="mod_wrap">
  <div class="mod_title"><h3>Receive Management</h3></div>
  
</div>
<div class="clear"></div>

<?php
	require($_path);
}


