<?php

include '../util.php';
include '../common.php';

$id_loan = !empty($_POST['id_loan']) ? $_POST['id_loan'] : 0;
$id_item = !empty($_POST['id_item']) ? $_POST['id_item'] : 0;
$query = "delete from loan_item where id_loan ='$id_loan' and id_item = '$id_item'";
echo $query;
echo mysql_error().$query;
$rs = mysql_query($query);
return $rs;
