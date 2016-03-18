<?php

$id = isset($_GET['id']) ? $_GET['id'] : null;
print_r($id);

	if($id > 0){
		$query = "delete from sms_management where id_sms_school=$id";		
	}
	$ok_save = mysql_query($query);
	
	if($ok_save){
		redirect('./?mod=sms');
	}else{
		echo '<span class="error">Failed to delete data</span>';
	}	

?>
