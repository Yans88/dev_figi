<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$usernameonly = !empty($_POST['usernameonly']) ? $_POST['usernameonly'] : 0;

if ($text != null){
    $query = "SELECT full_name, nric,user_name, id_user
                FROM user  
                WHERE full_name like '$text%' ";
    if ($usernameonly == 1) 
        $query .= " OR full_name like '$text%'";
    $query .= " ORDER BY full_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){           
				$nric = $rec[1];
                $what = $rec[0];
            echo '<li onclick="fill(\''. $nric . '\',\''.$what.'\')">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>