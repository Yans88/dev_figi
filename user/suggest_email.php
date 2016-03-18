<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$group = !empty($_POST['group']) ? $_POST['group'] : 0;

if ($text != null){
    $query = "SELECT id_user, full_name, user_email   
                FROM user  
                WHERE (full_name like '%$text%' OR user_email like '%$text%') ";
    $query .= ' AND id_group IN (' . GRPADM . ', ' . GRPHOD . ') ';
    $query .= " ORDER BY user_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){            
            $text = "$rec[1] ($rec[2])";
            $value = "$rec[1]|$rec[2]";
            echo '<li onclick="fill(\''. $inputId . '\',\''.$value.'\', 1)">' . $text . '</li>';
        }
        echo '</ul>';
    }
}
?>