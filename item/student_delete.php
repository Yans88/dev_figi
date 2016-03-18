<?php


if (!defined('FIGIPASS')) exit;


if(USERDEPT == 0){

	$id_student = $_GET['id_student'];
	$query = "DELETE FROM students WHERE id_student = $id_student";
	$execute = mysql_query($query);
	
	if($execute){
		echo "<script>alert('Deleting Successfully.');location.href='./?mod=item&sub=room_usage'</script>";
	} else {
		echo "<script>alert('Deleting Failed.');location.href='./?mod=item&sub=room_usage'</script>";
	}

} else {

	echo "<div class='error'>Sorry! You not allowed to access this page.</div>";
}

?>