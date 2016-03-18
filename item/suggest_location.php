<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;

if ($text != null){
    $query = "SELECT DISTINCT location  
                FROM item 
                WHERE location like '$text%' 
                ORDER BY location ASC 
                LIMIT 10 ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs))
            echo '<li onclick="fill_loc(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
        echo '</ul>';
    }
}
?>