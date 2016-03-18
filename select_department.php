<?php
require 'common.php';
require 'user/user_util.php';
require 'authcheck.php';

if (!defined('FIGIPASS')) exit;
$is_admin_dept = USERGROUP == GRPADM || USERGROUP == GRPASSETOWNER && !SUPERADMIN;
if (!$is_admin_dept) {
	include 'unauthorized.php';
	exit;
}

require 'header.php';

$a = switch_department(USERDEPT, USERGROUP);

?>
<!--
<div class="mod_wrap">
	<div class="mod_title"><h3>Select Department</h3></div>
	<div class="mod_links"></div>
</div>

<div class="clear"></div>-->
<br/><br/>
<div style="width: 800px; margin: auto auto;">
<!--
<h2>Select department to be managed</h2>
-->
<form method="POST">
<button class="btn_dept" name="target" value="<?php echo USERDEPT?>"> <?php echo get_name_department(USERDEPT); ?> </button>
<?php

$query = $a;
$mysql_query = mysql_query($query);
while($row = mysql_fetch_array($mysql_query)){

if($row['id_department'] == USERDEPT){}else{
?>
<button class="btn_dept" name="target" value="<?php echo $row['id_department']?>"> <?php echo $row['department_name']; ?> </button>
<?php
}
}
?>
</form>
<?php
$_target = isset($_POST['target']) ? $_POST['target'] : 0;
if ($_target>0){
	$_SESSION['figi_department'] = $_target;
	$get_name = get_name_department($_target);
	if (!empty($get_name))
		$_SESSION['figi_department_name'] = $get_name;
		
	redirect('./?mod=item'); //update by hansen 14.01.16
	//redirect('./');// back to home
	
}

?>
<style>
.btn_dept {
	width: 260px;
	height: 100px;
	font-size: 16pt;
	font-weight: bold;
}
.btn_dept:hover { cursor: pointer; background: #ccc}
</style>

<?php
require 'footer.php';


?>