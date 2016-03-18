<div style="color:#fff;"><?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null)	$_sub = 'service';

$id_page = get_page_id_by_name($_sub);

global $transaction_prefix;
$transaction_prefix = TRX_PREFIX_SERVICE;


$page_access = get_page_privileges(USERGROUP, $id_page);
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

if (SUPERADMIN){
	$i_can_delete = false;
	$i_can_update = false;
}

if (!$i_can_view)
	if ($i_can_create)
		$_act = 'submit';
	else
		return;

$_path = 'service/' . $_sub . '.php';

if (!file_exists($_path)) 
	return;
include_once 'item/item_util.php';
include_once 'service/service_util.php';

if (($i_can_view || $i_can_create) && (USERGROUP != GRPTEA)) {
?>
<div id="service_management" >
<table width="100%" border=0>
<tr>
  <td align="left" width="35%"><h3>Service Management</h3></td>
  <td align="right">
  <?php
    if ($i_can_create) {
        echo '<a class="button" href="?mod=portal&portal=service">Submit a Request</a> ';
        echo '<a class="button" href="?mod=service&sub=service&act=extraform">Extra Form</a> ';
    }  
    if ($i_can_view){
        echo '<a class="button" href="?mod=service&sub=service&act=list">Service Request</a> ';
        echo '<a class="button" href="?mod=service&sub=category&act=list">Category</a> '; 
    }
    if ($i_can_update)
        echo '<a class="button" href="?mod=service&sub=setting">Setting</a> '; 
?>
	</td>
</tr>
</table>
<?php
}
  include($_path);
?>
</div>
</div>