<?php

function update_register_no($_year, $_class)
{
	$nos = 0;
	$query = "SELECT sc.id_student 
				FROM student_classes sc 
				LEFT JOIN students s ON s.id_student = sc.id_student 
				WHERE sc.year = $_year AND sc.class = '$_class' 
				ORDER BY full_name ASC ";
	$rs = mysql_query($query);
	if ($rs){
		$nos = mysql_num_rows($rs);
		$regno = 1;
		while($rec = mysql_fetch_assoc($rs)){
			mysql_query("UPDATE student_classes SET register_no = $regno WHERE id_student = $rec[id_student] ");			
			$regno++;
		}
	}
	return $nos;
}

function update_student_count($_year, $_class, $_nos)
{
	mysql_query("UPDATE class SET student_count = $_nos WHERE year = $_year AND class = '$_class' ");
}

function get_students($filters = array(), $start = 0, $limit = 10){

	$query = "SELECT *  FROM students ";
	if (!empty($filters['class']))
		$query .= " WHERE class='$filters[class]'";
	$query .= " ORDER BY register_number ASC LIMIT $start, $limit";	
	$mysql = mysql_query($query);
	return $mysql;

}

function count_student($filters = array()){

	$query = "SELECT count(*) as total FROM students ";
	if (!empty($filters['class']))
		$query .= " WHERE class='$filters[class]'";
	$mysql = mysql_query($query);
	$fetch = mysql_fetch_array($mysql);
	
	return $fetch['total'];
	
}

function get_student($id_student)
{
	$rec = false;
	$rs = get_student_by_id($id_student);
	if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_assoc($rs);
	}
	return $rec;
}

function get_student_by_id($id_student){

	$query = "SELECT *  FROM students WHERE id_student = '$id_student'";
		
	$mysql = mysql_query($query);
	return $mysql;

}

function get_student_info_by_id($id_student){

	$query = "SELECT *  FROM student_info WHERE id_student = '$id_student'";
		
	$mysql = mysql_query($query);
	if ($mysql && mysql_num_rows($mysql)>0){
		$rec = mysql_fetch_assoc($mysql);
	}
	return $rec;

}


function del_student($id_student)
{
	$query = "DELETE FROM students WHERE id_student = '$id_student'";
	$query_info = "DELETE FROM student_info WHERE id_student = '$id_student'";
	$mysql = mysql_query($query);
	$mysql_info = mysql_query($query_info);
	//error_log(mysql_error().$query);
	return $mysql;
}

function get_class_list()
{
	$result = array();
	$rs = mysql_query("SELECT DISTINCT(class) FROM students ORDER BY class");
	if ($rs && mysql_num_rows($rs)>0){
		while($rec = mysql_fetch_assoc($rs))
			$result[$rec['class']] = $rec['class'];
	}
	return $result;
}

function check_parentInfo($id_student){

	$check_available = "SELECT * FROM student_info WHERE id_student = $id_student";
	$mysql = mysql_query($check_available);
	$rs = mysql_fetch_array($mysql);

	if($rs['id_student'] > 0)
	return 1;
	else
	return 0;

}

function getParentInfo_byId($id_student){

	$check_available = "SELECT * FROM student_info WHERE id_student = $id_student";
	$mysql = mysql_query($check_available);
	//$rs = mysql_fetch_array($mysql);
	
	return $mysql;

}
