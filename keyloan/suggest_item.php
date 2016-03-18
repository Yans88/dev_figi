<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$searchBy = !empty($_POST['searchBy']) ? $_POST['searchBy'] : 'item';
$status = AVAILABLE_FOR_LOAN;
$dept = defined('USERDEPT') ? USERDEPT : 0;

if ($text != null){
     $query = "SELECT $searchBy 
                        FROM key_item 
                        WHERE $searchBy like '$text%' 
                        ORDER BY $searchBy ASC LIMIT 10 ";
        if ($query != null){
            $rs = mysql_query($query);
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