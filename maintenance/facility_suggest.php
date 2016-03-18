<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;


if ($text != null && $key == null){
    $query = "SELECT id_location, location_name FROM location where location_name like '%$text%' ORDER BY location_name ASC";                       
    $rs = mysql_query($query);
    
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){            
            $what = "$rec[1]";
            $fillarg= "$rec[1]|$rec[0]";
            echo '<li onclick="fill(\'searchtext\',\''.$what.'\', 1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}


?>