<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null)	$_sub = 'title';
//if ($_act == null)	$_act = 'list';

$_path = 'deskcopy/' . $_sub . '.php';

include 'deskcopy/util.php';
//include './util.php';

//include 'item/item_util.php';
if (!file_exists($_path)) 
	return;
 
$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));
 
?>
<div align="center" id="fum">
<table width="800" border=0>
<tr>
  <td align="left" width="35%"><h3>Deskcopy Item Management</h3></td>
  <td align="right">
    <a class="button" href="?mod=deskcopy&sub=title"><img width=16 height=16 border=0 src="images/table.png"> Title List</a>

<?php
if ($i_can_create && !SUPERADMIN){
    echo '<a class="button" href="./?mod=deskcopy&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Item</a> ';
    echo '<a class="button" href="./?mod=deskcopy&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Item(s)</a> ';
    echo '<a class="button" href="./?mod=deskcopy&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Item</a>';
}
?>
    <br/>
    <a class="button" href="?mod=deskcopy&sub=setting"><img width=1 height=16 border=0 src="images/space.gif">Setting</a>

  <!--
	<a class="button" href="?mod=deskcopy&sub=item">Deskcopy Item</a>
	<a class="button" href="?mod=item&sub=item">Item</a>
	<a class="button" href="?mod=item&sub=category">Category</a>
	<a class="button" href="?mod=item&sub=specification">Specification</a>
	<a class="button" href="?mod=item&sub=vendor">Vendor</a>
	<a class="button" href="?mod=item&sub=manufacturer">Manufacturer</a>
	<a class="button" href="?mod=item&sub=brand">Brand</a>
    -->
  </td>
</tr>
</table>
<?php
  include($_path);
?>
</div>
