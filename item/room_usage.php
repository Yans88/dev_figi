<?php

if (!defined('FIGIPASS')) exit;


if(USERDEPT == 0){

?>


<h2>Room Usage</h2>
<div style="text-align:right">
	<a class='button' href='./?mod=item&sub=student_add'>Add Student</a><a class='button' href='./?mod=item&sub=student_add_class'>Add Class</a>
</div>





<?php

} else {

	echo "<div class='error'>You dont have allowed to access.</div>";

}

// ================= FUNCTIONS =================

function get_students($orderby = 'full_name', $sort = 'asc', $start = 0, $limit = 10){

	$query = "SELECT *, student_classes.year, student_classes.class FROM students LEFT JOIN student_classes ON student_classes.id_student_class = students.id_class ";
	
	if($orderby)
		$query .= " ORDER BY $orderby $sort LIMIT $start, $limit ";
		
	$mysql = mysql_query($query);
	return $mysql;

}

function count_student(){


}


?>