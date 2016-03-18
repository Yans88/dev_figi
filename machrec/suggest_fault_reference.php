<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;

if ($text != null){
    $query = "SELECT DISTINCT(id_fault), fault_date  
                FROM fault_report 
                WHERE id_fault like '$text%' ";
    $query .= " ORDER BY fault_date DESC LIMIT 10 ";
    $rs = mysql_query($query);
    
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($row = mysql_fetch_row($rs)){
            echo '<li onclick="fillServiceAgent(\''. $inputId . '\',\''.$row[0].'\', 1)">' . $row[0] . ': ' . substr($row[1],0, 10) . '</li>';
        }
        echo '</ul>';
    }
}
?>