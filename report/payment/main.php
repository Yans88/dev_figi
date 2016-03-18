<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) 	$_sub = 'payment';

$_path = 'payment/' . $_sub . '.php';


include 'item/item_util.php';
if (!file_exists($_path)) 
	return;
    
global $transaction_prefix;
$transaction_prefix = TRX_PREFIX_INVOICE;

 
$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));
 
?>
<div id="payment_management">
<table width="800" border=0>
<tr>
  <td align="left" width="40%"><h3>Payment Management</h3></td>
  <td align="right">
	<a class="button" href="?mod=payment&sub=payment">Schedules</a> 
<?php
	if (!SUPERADMIN && $i_can_create && (USERDEPT>0)){
		echo '<a class="button" href="?mod=payment&sub=payment&act=generate">Create Schedule</a>';
        echo '<a class="button" href="?mod=payment&sub=setting">Setting</a>'; 
    }
?>		
  </td>
</tr>
</table>
<?php
  include($_path);
?>
</div>
