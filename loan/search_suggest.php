<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$searchBy = !empty($_POST['searchBy']) ? $_POST['searchBy'] : 'item';
//$dept = !empty($_POST['dept']) ? $_POST['dept'] : 0;


if ($searchBy == 'issued_to') $searchBy = 'full_name';

if ($text != null){
    if (($searchBy == 'serial_no') || ($searchBy == 'asset_no')){
        $query = "SELECT i.$searchBy, i.id_item  FROM item i left join loan_item li on li.id_item = i.id_item
                    LEFT JOIN category cat ON cat.id_category = i.id_category ";
                   
        $query .= " WHERE cat.category_type = 'EQUIPMENT' AND i.$searchBy like '%$text%'";                    
        if ($dept > 0)
            $query .= " AND (i.id_department = $dept OR i.id_owner = $dept) ";
        $query .= " ORDER BY i.$searchBy ASC LIMIT 10 ";
        $rs = mysql_query($query);
       // error_log(mysql_error().$query);
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs)){                
                $what = $rec[0];
				$id = $rec[1];
                echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\',\''.$id.'\')">' . $what . '</li>';
            }
            echo '</ul>';
        }
    } 
    
	if ($searchBy == 'full_name'){
        $query = "SELECT $searchBy, nric  FROM user where $searchBy like '%$text%'";      
        $query .= " ORDER BY $searchBy ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs)){               
                $what = $rec[0];
				$id = $rec[1];
                echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\',\''.$id.'\')">' . $what . '</li>';
            }
            echo '</ul>';
        }
    } 
	
}
?>