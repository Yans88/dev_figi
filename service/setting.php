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
<h4>Setting for Service Module </h4>
<a href="./?mod=service&sub=setting&act=option">Option</a> | 
<a href="./?mod=service&sub=setting&act=email&tab=email">Email</a> | 
<a href="./?mod=service&sub=setting&act=email&tab=mobile">Mobile</a> | 
<a href="./?mod=service&sub=setting&act=message">Messages</a> 
<br/>
<?php
	if (!empty($_act)){
		include 'service/setting_' .  $_act . '.php';
	}
?>

