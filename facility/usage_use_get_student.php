
<?php
require "../common.php";
require '../student/student_util.php';

$nric = !empty($_POST['nric']) ? $_POST['nric'] : 0;




$student = get_student_by_nric($nric);

echo $student;



function get_student_by_nric($nric){
	$query = "SELECT * FROM students WHERE nric = '".$nric."'";	
	$mysql = mysql_query($query);
	$rec = mysql_fetch_array($mysql);
	$row = mysql_num_rows($mysql);
	
	$check = check_student_in_another_class($rec['id_student']);
	
	if($check > 0 ) {
		return -2;
	} else {
		if($row > 0){
			return $rec['full_name'];
		} else {
			return 0;
		}
	}
}

function check_student_in_another_class($id_student){
$query = "SELECT * FROM students_trans_detail 
LEFT JOIN students_trans ON students_trans.id_trans = students_trans_detail.id_trans
WHERE students_trans.status='0' AND students_trans_detail.id_student = '".$id_student."'";

$mysql = mysql_query($query);
$row = mysql_num_rows($mysql);

	if($row > 0){
		return 1;
	} else {
		return 0;
	}

}

?>