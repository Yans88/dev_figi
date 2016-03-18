<?php
	
	include('./qrcode_util.php');
	get_qrcode($_GET['qrcode'],$_GET['selected_field']);
?>