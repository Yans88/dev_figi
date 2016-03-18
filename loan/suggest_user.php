<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;

if ($text != null){
    $query = "SELECT full_name, nric, id_department, id_user 
                FROM user  
                WHERE full_name like '$text%' ";
    $query .= " ORDER BY full_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
			if (empty($rec[1])) $rec[1] = '-';
            $what = "$rec[0] (NRIC: $rec[1])";
            $data = $rec[3];
            echo '<li onclick="fill_user(\''. $inputId . '\',\''.$data.'\',1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>
