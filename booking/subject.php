<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_POST) || !empty($_GET)){
	$result = null;
	if (!empty($_POST['create'])){
		$ok = create_subject($_POST);
		$result = ($ok>0) ? 'CREATE:OK' : 'CREATE:ERROR';
	} else
	if (!empty($_POST['update'])){
		$ok = update_subject($_POST);
		$result = ($ok>0) ? 'UPDATE:OK' : 'UPDATE:ERROR';
	} else
	if (!empty($_POST['dele'])){
		$ok = dele_subject($_POST);
		$result = ($ok>0) ? 'DELETE:OK' : 'DELETE:ERROR';
	} else
	if (!empty($_GET['get'])){
		$data = get_subject($_GET['get']);
		$result = json_encode($data);
	} else {

		require 'subject_list.php';
		exit;
	}
	ob_clean();
	echo $result;
	ob_end_flush();
	exit;
} else {

}	

function create_subject($data)
{
	$subject_name = mysql_real_escape_string($data['subject_name']);
	$enabled = !empty($data['enabled']) ? 1 : 0;
	$id_department = USERDEPT;
	$created_by = USERID;
	$query = "INSERT INTO booking_subject(subject_name) VALUE('$subject_name')";
	$rs = mysql_query($query);
	//error_log(mysql_error().$query);
	return mysql_affected_rows();
}

function update_subject($data)
{
	$subject_name = mysql_real_escape_string($data['subject_name']);
	$query = "UPDATE booking_subject SET subject_name = '$subject_name' WHERE id_subject='$data[id_subject]'";
	mysql_query($query);
	//error_log(mysql_error().$query);
	return mysql_affected_rows();
}

function dele_subject($data)
{
	$query = "DELETE FROM booking_subject WHERE id_subject='$data[id_subject]'";
	mysql_query($query);
	//error_log(mysql_error().$query);
	return mysql_affected_rows();
}


