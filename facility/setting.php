<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

if ($_act == null)	$_act = 'option';

$_msg = null;
$_dept = USERDEPT;
$_tab = (!empty($_GET['tab'])) ?  $_GET['tab'] : null;
if ($_tab == null)
	$_tab = (!empty($_POST['tab'])) ?  $_POST['tab'] : 'email';


?>
<div class="submod_wrap">
	<div class="submod_title"><h4>Setting for Facility Module </h4></div>
	<div class="submod_links">
		<a href="./?mod=facility&sub=setting&act=option">Option</a> | 
		<a href="./?mod=facility&sub=setting&act=email&tab=email">Email</a> | 
		<a href="./?mod=facility&sub=setting&act=email&tab=mobile">Mobile</a>
	</div>
</div>
<div class="clear"></div>
<?php
	if (!empty($_act)){
		include 'facility/setting_' .  $_act . '.php';
	}
?>

