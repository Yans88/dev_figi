<?php

include '../util.php';
include '../common.php';

$dept = !empty($_POST['queryString']) ? $_POST['queryString'] : 0;
$type = !empty($_POST['type']) ? $_POST['type'] : 0;

$type = (strtolower($type) == 'service') ? 'SERVICE' : 'EQUIPMENT';
    
if ($dept != null){
    $query = "SELECT dc.id_category, category_name 
                FROM department_category dc 
                LEFT JOIN category c ON c.id_category = dc.id_category 
                WHERE category_type = '$type' AND dc.id_department = $dept 
                ORDER BY category_name ASC ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        while ($rec = mysql_fetch_row($rs)){
            echo '<option value="'. $rec[0] . '">' . $rec[1] . '</option>';
        }
    } 
    //else echo '<option value=0>-- none --</option>';
}
?>