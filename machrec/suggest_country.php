<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;

if ($text != null){
    $query = "SELECT printable_name 
                FROM country 
                WHERE printable_name like '$text%' ";
    $query .= " ORDER BY printable_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($row = mysql_fetch_row($rs)){
            echo "<li onclick='fillCountry(\"$inputId\",\"$row[0]\", 1)'>$row[0]</li>";
        }
        echo '</ul>';
    }
}
?>