<?php
if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

if ($_act == null)	$_act = 'option';

$_msg = null;
$_dept = USERDEPT;
$_tab = (!empty($_GET['tab'])) ?  $_GET['tab'] : null;
if ($_tab == null)
	$_tab = (!empty($_POST['tab'])) ?  $_POST['tab'] : 'email';


//<h4>Setting for Loan Module </h4>
?>
<p class="center">
<a href="./?mod=loan&sub=setting&act=option">Option</a> | 
<a href="./?mod=loan&sub=setting&act=email&tab=email">Email</a> | 
<a href="./?mod=loan&sub=setting&act=email&tab=mobile">Mobile</a> | 
<a href="./?mod=loan&sub=setting&act=message">Messages</a> | 
<a href="./?mod=loan&sub=setting&act=checklist">Loan-Out Checklist</a> 
</p>
<?php
	if (!empty($_act)){
		include 'loan/setting_' .  $_act . '.php';
	}
?>

