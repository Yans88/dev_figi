<?php
	ob_clean();
	$data = get_attachment_service($_GET['id']);
	download_attachment($data['filename'], base64_decode($data['data']));	
	ob_end_flush();
	exit;
?>
