<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$searchBy = !empty($_POST['searchBy']) ? $_POST['searchBy'] : 'item';
$status = AVAILABLE_FOR_LOAN;
$dept = defined('USERDEPT') ? USERDEPT : 0;

if ($text != null){
    if ($searchBy == 'isbn' || $searchBy == 'title'){
        $query = "SELECT $searchBy 
                    FROM deskcopy_title 
                    WHERE $searchBy like '$text%'  ";
        if ($dept > 0)
            $query .= " AND id_department = $dept ";
        $query .= " ORDER BY $searchBy ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs))
                echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
            echo '</ul>';
        }
        /*
    } else
    if ($searchBy == 'title'){
        $query = "SELECT title 
                    FROM deskcopy_title 
                    WHERE title like '$text%' ";
        if ($dept > 0)
            $query .= " AND id_department = $dept ";
        $query .= " ORDER BY title ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs)){
                echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
            }
            echo '</ul>';
        }
        */
    } else {
        $query = null;
        if ($searchBy == 'author_name')
            $query = "SELECT author_name 
                        FROM deskcopy_author 
                        WHERE author_name like '$text%' 
                        ORDER BY author_name ASC LIMIT 10 ";            
        else if ($searchBy == 'publisher_name')
            $query = "SELECT publisher_name 
                        FROM deskcopy_publisher  
                        WHERE publisher_name like '$text%' 
                        ORDER BY publisher_name ASC LIMIT 10 ";
        else if ($searchBy == 'status')
            $query = "SELECT DISTINCT(status) 
                        FROM deskcopy_item 
                        WHERE status like '$text%' 
                        ORDER BY status ASC LIMIT 10 ";
        else if ($searchBy == 'serial_no')
            $query = "SELECT serial_no 
                        FROM deskcopy_item 
                        WHERE serial_no like '$text%' 
                        ORDER BY serial_no ASC LIMIT 10 ";
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
}
?>