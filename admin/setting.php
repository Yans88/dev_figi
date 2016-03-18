<?php
if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}

if ($_act == null)	$_act = 'option';

$_msg = null;
$_dept = USERDEPT;
$_tab = (!empty($_GET['tab'])) ?  $_GET['tab'] : null;
if ($_tab == null)
	$_tab = (!empty($_POST['tab'])) ?  $_POST['tab'] : 'email';

echo '<div id="adminpage">';
if (!empty($_act))
	include 'setting_' .  $_act . '.php';
echo '</div>';	
?>