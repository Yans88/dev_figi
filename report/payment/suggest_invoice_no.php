<?php

include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;

if ($text != null){
    $query = "SELECT COUNT(DISTINCT invoice) 
                FROM item
                WHERE invoice like '$text%' ";
    $rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	$total = $rec[0];
    $query = "SELECT DISTINCT invoice 
                FROM item
                WHERE invoice like '$text%' 
                ORDER BY invoice ASC 
                LIMIT 10 ";
    $rs = mysql_query($query);
	$num = mysql_num_rows($rs);
    if ($rs && ($num > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs))
            echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
		/*
		if ($total > $num)
			echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
		*/
        echo '</ul>';
    }
}
?>