<?php

include '../util.php';
include '../common.php';

$_item_chk = !empty($_POST['item_ttl']) ? $_POST['item_ttl']:null;
$_id_check = !empty($_POST['id_chk']) ? $_POST['id_chk']:null;
$_key = !empty($_POST['key']) ? $_POST['key']:null;
$_id_check_item = !empty($_POST['id_chk_item']) ? $_POST['id_chk_item']:null;

if ($_item_chk != null && $_id_check !== '' && $_key == 'save'){
    $query = "INSERT into loan_out_checklist_item (id_check,title_item) values ($_id_check, '$_item_chk')";
    $rs = mysql_query($query);
	if($rs){
		echo 'ok';
	}else{
		echo 'failed';
	}   
}

if ($_item_chk != null && $_id_check_item !== '' && $_key == 'edit'){
    $query = "UPDATE loan_out_checklist_item set title_item = '$_item_chk' where id_check_item = '$_id_check_item'";
    $rs = mysql_query($query);
	if($rs){
		echo 'ok';
	}else{
		echo 'failed';
	}
   
}

if ($_id_check_item !== '' && $_key == 'delete'){
    $query = "delete from loan_out_checklist_item where id_check_item = '$_id_check_item'";
    $rs = mysql_query($query);
	if($rs){
		echo 'ok';
	}else{
		echo 'failed';
	}
    error_log(mysql_error().$query);
}

?>