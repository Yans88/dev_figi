<?php
include '../util.php';
include '../common.php';
include 'user_util.php';


$query = "SELECT * FROM user WHERE id_user > 1";
$rs = mysql_query($query);
$updates = array();
while ($rec =  mysql_fetch_assoc($rs)){
    $updates[$rec['id_user']]['user_name'] = $encryption->encode($rec['user_name']);
    $updates[$rec['id_user']]['user_email'] = $encryption->encode($rec['user_email']);
    $updates[$rec['id_user']]['contact_no'] = $encryption->encode($rec['contact_no']);
}
foreach($updates as $id => $rec){
    $query = "UPDATE user SET user_name = '$rec[user_name]', user_email = '$rec[user_email]', 
                contact_no = '$rec[contact_no]' WHERE id_user = $id ";
    mysql_query($query);
}
?>