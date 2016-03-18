<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;

$search_by = !empty($_POST['searchBy']) ? $_POST['searchBy'] : null;

$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$usernameonly = !empty($_POST['usernameonly']) ? $_POST['usernameonly'] : 0;

if ($text != null && $search_by !=null){
    $query = "SELECT id_student, full_name, class FROM students where $search_by like '%$text%' ORDER BY full_name ASC";             
    $rs = mysql_query($query);
	error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){   
		if($search_by == 'class'){
			$what = "$rec[2]";
		}else{
			$what = "$rec[1]";
		}            
            $fillarg= "$rec[1]|$rec[0]";
            echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\', 1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>