<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$dept = !empty($_POST['dept']) ? $_POST['dept'] : 0;
$ctype = !empty($_POST['ctype']) ? $_POST['ctype'] : 'EQUIPMENT';
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;

if ($text != null){
    $query = "SELECT category_name 
                FROM category c 
                LEFT JOIN department_category dc ON dc.id_category = c.id_category 
                WHERE category_type = '$ctype' AND category_name like '%$text%' AND dc.id_department != '$dept'  
                ORDER BY category_name ASC 
                LIMIT 10 ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs))
            echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
        echo '</ul>';
    }
}
?>