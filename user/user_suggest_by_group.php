<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$group = !empty($_POST['group']) ? $_POST['group'] : 0;
$email = !empty($_POST['email']) ? $_POST['email'] : 0;

if ($text != null){
    $query = "SELECT user_name, full_name, user_email, id_user   
                FROM user  
                WHERE id_group = $group AND (user_name like '$text%' OR full_name like '$text%') ";
    $query .= " ORDER BY user_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
            $what = $rec[1];
            if ($email > 0)
                $what .= ' &lt;'.$rec[2].'&gt;';
                    
            echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\',\''.$rec[3].'\')">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>