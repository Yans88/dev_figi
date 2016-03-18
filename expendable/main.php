<?php
if (!defined('FIGIPASS')) exit;
if(!defined('EXPENDABLE_ITEM') || !EXPENDABLE_ITEM) {
	echo 'Unknown Modules';
	exit;
	}
if ($_sub == null)	$_sub = 'item';
if($_sub != 'loan'){
	$_path = 'expendable/' . $_sub . '.php';
	
	}

else {
		if(!isset($_act)) $_act = 'list';
		
		$_path = 'expendable/' . $_sub . '_'.$_act.'.php';
	}
	

include 'expendable/util.php';
if (!file_exists($_path)) return;

$config = $configuration['consumable'];

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));
 
?>
<div align="center" id="item_management">
<table width="800" border=0>
<tr>
  <td align="left" width="45%"><h3>Expendable Item Management</h3></td>
  <td align="right">
    <a class="button" href="?mod=expendable&sub=item">Item List</a>
    <a class="button" href="?mod=expendable&sub=loan&act=list">Loan Request</a>
    <a class="button" href="?mod=expendable&sub=category">Category</a>
<?php
    if ((USERDEPT>0) && (USERGROUP==GRPADM) && !SUPERADMIN)
        echo '<a class="button" href="?mod=expendable&sub=setting">Setting</a>';
?>
  </td>
</tr>
<tr>
  <td align="right" colspan=2>
<?php
if ($i_can_create && !SUPERADMIN){
    echo '<a class="button" href="./?mod=expendable&act=loan"><img width=16 src="images/up.png" height=16 border=0 > Loan Item</a> ';
    echo '<a class="button" href="./?mod=expendable&act=return"><img width=16 src="images/down.png" height=16 border=0 > Return Item</a> ';
    echo '<a class="button" href="./?mod=expendable&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Item</a> ';
    echo '<a class="button" href="./?mod=expendable&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Item(s)</a> ';
    echo '<a class="button" href="./?mod=expendable&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Item</a>';
}
?>
  </td>
</tr>

</table>
<?php
  include($_path);
?>
</div>