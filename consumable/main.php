<?php
if (!defined('FIGIPASS')) exit;
if(!defined('CONSUMABLE_ITEM') || !CONSUMABLE_ITEM) {
	echo 'Unknown Modules';
	exit;
	}
if ($_sub == null)	$_sub = 'item';

$_path = 'consumable/' . $_sub . '.php';
include 'consumable/util.php';
if (!file_exists($_path)) return;

$config = $configuration['consumable'];

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));
 
?>
<div align="center" id="item_management">
<table width="100%" border=0>
<tr>
  <td align="left" width="45%"><h3>Consumable Item Management</h3></td>
  <td align="right">
    <a class="button" href="?mod=consumable&sub=item">Item List</a>
    <a class="button" href="?mod=consumable&sub=category">Category</a>
<?php
    if ((USERDEPT>0) && (USERGROUP==GRPADM) && !SUPERADMIN)
        echo '<a class="button" href="?mod=consumable&sub=setting">Setting</a>';
?>
  </td>
</tr>
<tr>
  <td align="right" colspan=2>
<?php
if ($i_can_create && !SUPERADMIN){
    echo '<a class="button" href="./?mod=consumable&act=use"><img width=16 height=16 border=0 src="images/process.png"> Use Item</a> ';
    echo '<a class="button" href="./?mod=consumable&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Item</a> ';
    echo '<a class="button" href="./?mod=consumable&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Item(s)</a> ';
    echo '<a class="button" href="./?mod=consumable&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Item</a>';
}
?>
  </td>
</tr>

</table>
<?php
  include($_path);
?>
</div>