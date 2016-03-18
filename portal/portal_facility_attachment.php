<?php
	ob_clean();
	include_once './facility/facility_util.php';
	include_once './util.php';
	$data = get_attachment_facility($_GET['id_item']);
	
	download_attachment($data['filename'], base64_decode($data['data']));	
	ob_end_flush();
	exit; 
	
?>