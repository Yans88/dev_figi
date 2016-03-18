<?php


if (!defined('FIGIPASS')) exit;


if(USERDEPT == 0){

	$id_student_class = $_GET['id_student_class'];
	$id_student = $_GET['id_student'];
	$query = "DELETE FROM student_classes WHERE id_student_class = $id_student_class";
	$execute = mysql_query($query);
	if (mysql_affected_rows()>0){
		$insert = "UPDATE students set class_now = null where id_student= ".$id_student;
			$rs = mysql_query($insert);
        //error_log(mysql_error().$insert);
	}	 
	if($execute){
		echo "<script>alert('Deleting Successfully.');location.href='./?mod=item&sub=student_add_class'</script>";
	} else {
		echo "<script>alert('Deleting Failed.');location.href='./?mod=item&sub=student_add_class'</script>";
	}

} else {

	echo "<div class='error'>Sorry! You not allowed to access this page.</div>";
}

?>