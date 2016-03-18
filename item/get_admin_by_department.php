<?php

include '../util.php';
include '../common.php';

$dept = !empty($_POST['queryString']) ? $_POST['queryString'] : 0;
    
if ($dept != null){
    $admins = get_admins($dept);
    if (!empty($admins)){
        foreach ($admins as $rec){
            echo '<option value="'. $rec['id_user'] . '">' . $rec['full_name'] . '</option>';
        }
    }else
        echo '<option value=0>-- none --</option>';
}
?>