<div style="color:#fff;"><?php

if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) 	$_sub = 'fault';

global $transaction_prefix;
$transaction_prefix = TRX_PREFIX_FAULT;

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

if (SUPERADMIN){
	$i_can_delete = false;
	$i_can_update = false;
}

if (!$i_can_view){

	if ($i_can_create)
		$_act = 'submit';
	else
		return;
}
$_path = 'fault/' . $_sub . '.php';

if (!file_exists($_path)) 
	return;
//include_once 'item/item_util.php';
include_once 'fault/fault_util.php';

if (($i_can_view || $i_can_create) && (USERGROUP != GRPTEA)) {
?>
<div class="mod_wrap">
    <div class="mod_title"><h3>Fault Report Management</h3></div>
    <div class="mod_links">
        <a class="button" href="?mod=portal&sub=portal&portal=fault">Submit a Report</a>  
  <?php
    if ($i_can_view)
        echo '<a class="button" href="?mod=fault&sub=fault&act=list">Fault Report</a> '; 
    echo '<a class="button" href="?mod=fault&sub=category">Category</a> ';
    if (USERGROUP==GRPADM && USERDEPT>0)
        echo '<a class="button" href="?mod=fault&sub=setting">Setting</a> '; 
?>
    
    </div>
</div>
<div class="clear"></div>

<?php
}
  include($_path);
?>
</div>