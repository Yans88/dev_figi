<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$searchBy = !empty($_POST['searchBy']) ? $_POST['searchBy'] : 'item';
$dept = !empty($_POST['dept']) ? $_POST['dept'] : 0;
$status = AVAILABLE_FOR_LOAN;

if ($text != null){
    if ($searchBy == 'invoice') {
        $query = "SELECT DISTINCT invoice 
                    FROM item 
                    LEFT JOIN category cat ON cat.id_category = item.id_category 
                    WHERE category_type = 'EQUIPMENT' AND $searchBy like '$text%'  ";
        if ($dept > 0)
            $query .= " AND (item.id_department = $dept AND item.id_owner = $dept)  ";
        $query .= " ORDER BY serial_no ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs)){
                echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
            }
            echo '</ul>';
        }
    }
}
?>
