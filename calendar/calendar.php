<?php
//if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) $_sub = 'calendar';
if ($_act == null) $_act = 'view_month';
/*
$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request
*/
if (SUPERADMIN) {
	$i_can_delete = false;
	$i_can_update = false;	
}
if (!$i_can_view)
	if ($i_can_create)
		$_act = 'submit';
	else
		return;

$from_portal = false;
$_path = 'calendar/' . $_sub . '_' . $_act . '.php';

if (!file_exists($_path)) return;

include($_path);
?>
