<?php

include '../util.php';
include '../common.php';
//include 'loan_util.php';

$rs = 0;
$_asset = !empty($_POST['asset']) ? $_POST['asset']:0;
$id_loan = !empty($_POST['loan']) ? $_POST['loan']:0;
if(!empty($_asset)){
	$my_asset = explode(',', $_asset);
	foreach($my_asset as $myAsset){
		$_ass[] = "'".$myAsset."'";
	}
	$asset = implode(',', $_ass);
	$query = "delete from loan_item where id_loan ='$id_loan' and id_item in ($asset)";
	
	$rs = mysql_query($query);	
}

return $rs;

