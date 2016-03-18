<?php

include 'maintenance_util.php';
include '../common.php';
//include 'loan_util.php';

$rs = 0;
$ok = '';
$_data = !empty($_POST['data']) ? $_POST['data']:0;

if(!empty($_data)){
	$data1 = explode(';', $_data);
	$count_data = count($data1);
	for($i=0; $i<$count_data;$i++){
		$dt = explode(',',$data1[$i]);
		$id_term = 0;
		$id_location = $dt[0];
		$id_create = $dt[1];
		$create_on = $dt[2];
		$id_asset = $dt[3];
		$id_modify = $dt[4];
		$modify_on = $dt[5];
		$result = $dt[6];
		$remark = $dt[7];
		$query = "INSERT INTO checklist_checking(id_location, id_term, created_by, created_on, modified_by, modified_on) ";
		$query .= "VALUE($id_location, $id_term, $id_create, '$create_on', $id_modify, '$modify_on')";
		$rs = mysql_query($query); 
		if ($rs) $id_check = mysql_insert_id();
		$query =  "INSERT INTO checklist_checking_result(id_check, id_item, result, remark, checked_by, checked_on) VALUE ";
		$query .= "($id_check, $id_asset,'$result','$remark', $id_modify,'$modify_on')";
		$rs = mysql_query($query); 
		if($rs){
			$ok = 'ok';
		}else{
			$ok = 'wrong';
		}
	}
}

echo $ok;

