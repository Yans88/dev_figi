<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
if (empty($_GET['status'])){
        $_status = 'notified';
} else
    $_status = $_GET['status'];

echo <<<LINK3
<p class="center">
	<a href="./?mod=fault&sub=fault&status=notified">Notified Fault Report</a> | 
	<a href="./?mod=fault&sub=fault&status=progress">Fault Report Under Rectification</a> | 
	<a href="./?mod=fault&sub=fault&status=completed">Fault Report already Completed</a> 
</p>
LINK3;

include 'fault/fault_list_'. $_status . '.php';
?>
<br/>&nbsp;
