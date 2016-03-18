<?php
/*
backup & restore tool

- can do backup now
- can do schedulled backup (periodically)
- can do restore data, will replace all existing data
- existing backup can be downloaded


*/

if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}

if ($_act == null)	$_act = 'backup';

$_msg = null;
$_dept = USERDEPT;
$_tab = (!empty($_GET['tab'])) ?  $_GET['tab'] : null;
if ($_tab == null)
	$_tab = (!empty($_POST['tab'])) ?  $_POST['tab'] : 'email';


function get_current_schedule($task_name)
{
    $result = array();
    $query = "SELECT * FROM schedule_task WHERE task_name = '$task_name'";
    $rs = mysql_query($query);
    if (mysql_num_rows($rs) > 0){
        $result = mysql_fetch_assoc($rs);
    }
    return $result;
}

?>
<div id="backuprestore" style="width: 600px;">
<!--
<a href="./?mod=admin&sub=backuprestore&act=backup">Backup</a> | 
<a href="./?mod=admin&sub=backuprestore&act=restore">Restore</a>
-->
<?php
	if (!empty($_act)){
		include 'backuprestore_' .  $_act . '.php';
	}
?>
</div>
<br/>&nbsp; 

