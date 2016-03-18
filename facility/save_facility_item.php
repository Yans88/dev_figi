<?php

include '../util.php';
include '../common.php';

$id_facility = !empty($_POST['idFacility']) ? $_POST['idFacility'] : null;
$id = !empty($_POST['id']) ? $_POST['id'] : null;
$id_item = !empty($_POST['idItem']) ? $_POST['idItem'] : null;
$register_number = !empty($_POST['register_number']) ? $_POST['register_number'] : null;
$id_student = !empty($_POST['id_student']) ? $_POST['id_student'] : null;
$old_student = !empty($_POST['old_student']) ? $_POST['old_student'] : null;
$class = !empty($_POST['Myclass']) ? $_POST['Myclass'] : null;
$name_student = !empty($_POST['name']) ? $_POST['name'] : null;
//$id_asset = !empty($_POST['id_asset']) ? $_POST['id_asset'] : null;
$id_students = !empty($_POST['id_student']) ? $_POST['id_student'] : 0;
$status = !empty($_POST['status']) ? $_POST['status'] : 0;
$key = !empty($_POST['key']) ? $_POST['key'] : null;
$_item = !empty($_POST['all_id']) ? $_POST['all_id'] : null;
$present = !empty($_POST['present']) ? $_POST['present'] : null;
$user = !empty($_POST['user']) ? $_POST['user'] : null;
$_to = !empty($_POST['to']) ? $_POST['to'] : null;

if ($id_item != null && $key == 'save'){
    $query = "INSERT into facility_fixed_item (id_facility,id_item, register_number) values ($id_facility, $id_item, $register_number) ";
    $rs = mysql_query($query);
    //error_log(mysql_error().$query);
}

if ($id_facility !=null && $id_item != null && $key == 'edit'){
    $query = "update facility_fixed_item set id_item = ".$id_item." where id_facility= ".$id_facility." and register_number = ".$register_number;
    $rs = mysql_query($query);
    //error_log(mysql_error().$query);
}

if ($id_facility !=null && $key == 'del'){	
    $query = "delete from facilty_fixed_item where id_facility= ".$id_facility." and register_number = ".$register_number;
    $rs = mysql_query($query);    
}

if ($id_facility !=null && $key == 'del_update'){	
    $query = "update facility_fixed_item set id_item='' where id_facility= ".$id_facility." and register_number = ".$register_number;
    $rs = mysql_query($query);   
}

if ($id_student !=null && $key == 'upd_student'){
	$upd = "update students set register_number = 0 where id_student = '".$old_student."' AND class_now = '".$class."'";
    $rs = mysql_query($upd);
	//error_log(mysql_error().$upd);
	$query = "update students set register_number = ".$register_number." where id_student = '".$id_student."' AND class_now = '".$class."'";
	$rs = mysql_query($query);
	//error_log(mysql_error().$query);    
}

if ($id_facility !=null && $key == 'trans'){
	$start_time = date('Y-m-d H:i:s');
	$end_time = 0;
	$end_user = null;
    $query = "INSERT into students_trans (id_class,id_location, status, start_date, end_date, user_start, user_end) values ('$class', '$id_facility', '$status', '$start_time', '$end_time', '$user', '$end_user')";
    $rs = mysql_query($query);	    
	$id = mysql_insert_id();
	error_log(mysql_error().$query);	
	if($id){
		echo "ok_".$id;
	}else{
		echo "false";
	}
}

if ($id !=null && $register_number !=null && $key == 'save_all'){
    $query = "INSERT into students_trans_detail (id_trans,id_student, id_item, reg_number, absent_present) values ('$id', '$id_students', '$id_item', '$register_number','$present')";
    $rs = mysql_query($query);	
    //error_log(mysql_error().$query);	
	if (mysql_affected_rows()>0){		
		$insert = "UPDATE item set id_status = 11 where id_item= ".$id_item;
		$rs = mysql_query($insert);
        //error_log(mysql_error().$insert);		
	}	
}

if ($id !=null && $key == 'end_use'){
	$end_time = date('Y-m-d H:i:s');
    $query = "UPDATE students_trans set status='0',end_date = '".$end_time."', user_end = '".$user."' where id_trans= ".$id;
    $rs = mysql_query($query);	
    //error_log(mysql_error().$query);
	if($rs){
		echo 'ok';
	}else{
		echo 'failed';
	}
	
}


if ($id_facility !=null && $key == 'swap_item'){	
	$query = "select id_item from facilty_fixed_item where id_facility= '$id_facility' and register_number = '$_to'";
	$rs_id = mysql_query($query);
	$id = mysql_fetch_assoc($rs_id);
	//error_log(mysql_error().$query);
        $iditem = 0;
        if (!empty($id['id_item'])) $iditem = $id['id_item'];
	$query = "update facilty_fixed_item set id_item= '$iditem' where id_facility= '$id_facility' and register_number = '$register_number'";
    $rs = mysql_query($query); 
	error_log(mysql_error().$query);
	if (mysql_affected_rows()>0){
		$query = "update facilty_fixed_item set id_item='$id_item' where id_facility= '$id_facility' and register_number = '$_to'";
		$rs = mysql_query($query); 
//error_log(mysql_error().$query);		
		if($rs){
		echo 'ok';
			}else{
		echo 'failed';
		}	
	}	
}

?>