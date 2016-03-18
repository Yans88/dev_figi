<?php
if (!defined('FIGIPASS')) exit;

$_path = 'maintenance/' . $_sub . '.php';

if (!file_exists($_path)) $_sub = 'checking';
$_path = 'maintenance/' . $_sub . '.php';

$mod_url = './?mod=maintenance';
$submod_url = $mod_url.'&sub='.$_sub;
$mod_url = './?mod=maintenance';

/*
global $transaction_prefix;
$transaction_prefif = TRX_PREFIX_FACILITY;

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name('maintenance'));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

*/

?>

<div class="mod_wrap">
	<div class="mod_title"><h3>Maintenance Management</h3></div>
	<div class="mod_links">
		<!-- <a class="button" href="./?mod=maintenance&sub=checking">Machine Records</a> -->
		<a class="button <?php echo ($_sub=='import')?'active':null?>" href="./?mod=maintenance&sub=checklist&act=import">Import</a>
		<a class="button" id="btn_export" href="#export" disabled>Export</a>
		<a class="button <?php echo ($_mod=='machrec')?'active':null?>" href="?mod=machrec">Machine Record</a>
		<a class="button" href="./?mod=maintenance&sub=checklist">Checklist Setting</a>
	</div>
</div>
<div class="clear"> </div>

<?php

require($_path);
