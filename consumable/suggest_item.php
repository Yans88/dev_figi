<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$searchBy = !empty($_POST['searchBy']) ? $_POST['searchBy'] : 'item';
$dept = !empty($_POST['dept']) ? $_POST['dept'] :  0;
$status = AVAILABLE_FOR_LOAN;

if ($text != null){
    if ($searchBy == 'item_code' || $searchBy == 'item_name'){
        $query = "SELECT $searchBy 
                    FROM consumable_item ci 
                    LEFT JOIN category c ON c.id_category = ci.id_category 
                    WHERE $searchBy like '$text%' AND category_type = 'CONSUMABLE' ";
        if ($dept > 0)
            $query .= " AND id_department = $dept ";
        $query .= " ORDER BY $searchBy ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs))
                echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
            echo '</ul>';
        }

    } else
    if ($searchBy == 'category_name'){
        $query = "SELECT category_name 
                    FROM category c 
                    WHERE category_name like '$text%' AND category_type = 'CONSUMABLE' ";
        if ($dept > 0)
            $query .= " AND id_department = $dept ";
        $query .= " ORDER BY category_name ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs))
                echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
            echo '</ul>';
        }

    } 
}
?>