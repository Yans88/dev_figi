<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$class = !empty($_POST['Myclass']) ? $_POST['Myclass'] : null;
//$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$key = !empty($_POST['key']) ? $_POST['key'] : null;

$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$usernameonly = !empty($_POST['usernameonly']) ? $_POST['usernameonly'] : 0;

if ($text != null && $key == null){
    $query = "SELECT id_student, full_name FROM students where active=1 and class='$class' AND full_name like '%$text%' ORDER BY full_name ASC";             
    $rs = mysql_query($query);
    error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){            
            $what = "$rec[1]";
            $fillarg= "$rec[1]|$rec[0]";
            echo '<li onclick="fill(\''. $class . '\',\''.$fillarg.'\', 1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}

if ($text != null && $key == 'report'){
    $query = "SELECT full_name, id_student 
                FROM students  
                WHERE full_name like '$text%' ";
    if ($usernameonly == 1) 
        $query .= " OR full_name like '$text%'";
    $query .= " ORDER BY full_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
            if ($usernameonly == 1)
                $what = $rec[0];
            else
                $what = (preg_match('/'.$text.'/i', $rec[0])) ? $rec[0] : $rec[1];
            echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\')">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>