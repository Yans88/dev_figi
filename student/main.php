<?php
if (!defined('FIGIPASS')) exit;

require 'student_util.php';
if (empty($_sub)) $_sub = 'student';

$mod_url = './?mod=student';
$submod_url = $mod_url;
$current_url = $mod_url;

$page_access = get_page_privileges(USERGROUP, get_pages_id_by_name('Student List'));
$_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

$page_quickUM = get_page_privileges(USERGROUP, get_pages_id_by_name('User Management'));
$i_can_view_quickUM = (isset($page_quickUM[CAN_VIEW]) && ($page_quickUM[CAN_VIEW] == 1)); 
$hide = null;
if(!$i_can_view_quickUM){
	$hide = 'style="display:none;"';
}


$_path = 'student/' . $_sub . '.php';
if (!file_exists($_path)) $_sub = 'student';
$_path = 'student/' . $_sub . '.php';

if (!$_can_view) 
	require 'unauthorized.php';
else {

?>

<div class="mod_wrap">
  <div class="mod_title"><h3>Student Management</h3></div>
  <div class="mod_links">
	<!--<a class='button' href='./?mod=student&sub=student'>Manage Student</a>
	a class='button' href='./?mod=student&sub=class'>Manage Class</a-->
	<a class="button" <?php echo $hide;?> href="?mod=user&sub=department">Department</a>
    <a class="button" <?php echo $hide;?> href="?mod=user&act=list">User</a> 
	<?php  if (SUPERADMIN) {
		$page_quickSL = get_page_privileges(USERGROUP, get_pages_id_by_name('Student List'));
		$i_can_view_quickSL = (isset($page_quickSL[CAN_VIEW] ) && ($page_quickSL[CAN_VIEW] == 1)); 
		if($i_can_view_quickSL){
		?>
			<a class="button" href="?mod=student">Student List</a> 
		<?php }}?>
    <a class="button" href="?mod=user&sub=group" <?php echo $hide;?>>Group</a>
  </div>
</div>
<div class="clear"></div>

<?php
	require($_path);
}


