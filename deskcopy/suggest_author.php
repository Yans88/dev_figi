<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
//$dept = defined('USERDEPT') ? USERDEPT : 0;

if ($text != null){
    $query = "SELECT author_name 
                FROM deskcopy_author 
                WHERE author_name like '$text%' ";

    $query .= " ORDER BY author_name ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
            $what = (preg_match('/'.$text.'/i', $rec[0])) ? $rec[0] : $rec[1];
            echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\')">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>