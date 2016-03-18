<div style="color:#fff;">
<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null)	$_sub = 'condemned';

global $transaction_prefix, $config; 
$transaction_prefix = TRX_PREFIX_CONDEMNED;
$config = $configuration['condemned'];

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request
if (SUPERADMIN) {
	$i_can_delete = false;
	$i_can_update = false;	
}
if (!$i_can_view)
	if ($i_can_create)
		$_act = 'submit';
	else
		return;

$_path = 'condemned/' . $_sub . '.php';

if (!file_exists($_path)) 
	return;
include_once 'item/item_util.php';
include_once 'condemned/condemned_util.php';

if (($i_can_view || $i_can_create) && (USERGROUP != GRPTEA)) {
?>
<div align="center" id="fum">
<table width="800" border=0>
<tr>
  <td align="left" width="40%"><h3>Condemned Item Management</h3></td>
  <td align="right">
  <?php
    if ($i_can_create) {
        echo '<a class="button" href="?mod=condemned&sub=condemned&act=issue">Create Condemned Process</a> ';
    }
	if ($i_can_view)
        echo '<a class="button" href="?mod=condemned&sub=condemned&act=list">Condemnation List</a> '; 
    if (!SUPERADMIN && $i_can_update)
        echo '<a class="button" href="?mod=condemned&sub=setting">Setting</a>'; 
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