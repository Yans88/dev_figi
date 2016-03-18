<?php

include '../util.php';
include '../common.php';

$nric = !empty($_POST['nric']) ? $_POST['nric'] : null;
if ($nric != null){
    $query = "SELECT  u.*, d.department_name department  
                FROM user u  
				LEFT JOIN department d ON d.id_department = u.id_department 
                WHERE nric = '$nric' ";
    $rs = mysql_query($query);
    
    if ($rs && (mysql_num_rows($rs) > 0)){
        $user = mysql_fetch_assoc($rs);
		echo json_encode($user);
    }
}
echo '';
