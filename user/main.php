

<?php
if (!defined('FIGIPASS')) exit;


$page_quickUM = get_page_privileges(USERGROUP, get_pages_id_by_name('User Management'));
$i_can_view_quickUM = (isset($page_quickUM[CAN_VIEW]) && ($page_quickUM[CAN_VIEW] == 1)); 
$hide = null;
if(!$i_can_view_quickUM){
	$hide = 'style="display:none;"';
}

if ($_sub == null) 
	$_sub = 'user';
if ($_act == null)
	$_act = 'list';

define('IDMOD', get_module_id_by_name($_mod));

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));

$_path = 'user/' . $_sub . '_' . $_act . '.php';

if (!file_exists($_path)) 
	return;


?>
<div align="center" id="usercontent">
<?php
    if ($_act != 'account' && SUPERADMIN) {
?>
<table width="100%" border=0>

<tr>
  <td align="left" width="40%"><h3>User Management</h3></td>
  <td align="right">
    <a class="button" <?php echo $hide;?> href="?mod=user&sub=department">Department</a>
    <a class="button" <?php echo $hide;?> href="?mod=user&act=list" >User</a> 
	<?php  if (SUPERADMIN) {
		$page_quickSL = get_page_privileges(USERGROUP, get_pages_id_by_name('Student List'));
		$i_can_view_quickSL = (isset($page_quickSL[CAN_VIEW] ) && ($page_quickSL[CAN_VIEW] == 1)); 
		if($i_can_view_quickSL){
		?>
			<a class="button" href="?mod=student">Student List</a> 
		<?php }}?>
    <a class="button" <?php echo $hide;?> href="?mod=user&sub=group">Group</a>
  </td>
</tr>
</table>
<?php
    } // if !account
  
 
include($_path); 
?>
</div>
