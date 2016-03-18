<?php 
$_id = isset($_GET['id']) ? $_GET['id'] : null;

ob_clean();
if (!empty($_id))
	$_SESSION['CURRENT_FAULT_NO'] = $_id;
header('Location: ./?mod=machrec&sub=machine&act=create');
ob_end_flush();
exit;                
?>
