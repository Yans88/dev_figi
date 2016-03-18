<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$dept = !empty($_POST['deptId']) ? $_POST['deptId'] : 0;

if ($text != null){
    $query = "SELECT purpose, count(*) freq 
                FROM facility_book fb 
                LEFT JOIN facility f ON f.id_facility = fb.id_facility 
                WHERE purpose like '%$text%' ";
    if ($dept > 0) $query  .= " AND f.id_department = $dept ";
    $query .= "GROUP BY purpose ORDER BY freq DESC, purpose ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
            echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\', 1)">' . $rec[0] . '</li>';
        }
        echo '</ul>';
    }
}
?>