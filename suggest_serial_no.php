<?php

include 'common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$status = AVAILABLE_FOR_LOAN;

if ($text != null){
    $query = "SELECT serial_no 
                FROM item
                WHERE id_status = $status AND serial_no like '$text%' 
                ORDER BY serial_no ASC 
                LIMIT 10 ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs))
            echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
        echo '</ul>';
    }
}
?>