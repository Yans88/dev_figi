<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$searchBy = !empty($_POST['searchBy']) ? $_POST['searchBy'] : 'item';
$dept = !empty($_POST['dept']) ? $_POST['dept'] : 0;
$status = AVAILABLE_FOR_LOAN;
//print_r($_POST);
if ($text != null){
    if (($searchBy == 'serial_no') || ($searchBy == 'asset_no') || ($searchBy == 'model_no')){
        $query = "SELECT serial_no, asset_no, model_no  
                    FROM item 
                    LEFT JOIN category cat ON cat.id_category = item.id_category 
                    WHERE category_type = 'EQUIPMENT' AND $searchBy like '%$text%'  ";
        if ($dept > 0)
            $query .= " AND cat.id_department = $dept ";
        $query .= " ORDER BY serial_no ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs)){
                //$what = (preg_match('/'.$text.'/i', $rec[0])) ? $rec[0] : $rec[1];
                $what = ($searchBy == 'serial_no') ? $rec[0] : ($searchBy == 'asset_no') ? $rec[1] : $rec[2];
                echo '<li onclick="fill(\''. $inputId . '\',\''.$what.'\')">' . $what . '</li>';
            }
            echo '</ul>';
        }
    } else
    if ($searchBy == 'category_name'){
        $query = "SELECT category_name 
                    FROM category
                    WHERE category_type = 'EQUIPMENT' AND category_name like '%$text%' ";
        if ($dept > 0)
            $query .= " AND category.id_department = $dept ";
        $query .= " ORDER BY category_name ASC LIMIT 10 ";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs && (mysql_num_rows($rs) > 0)){
            echo '<ul>';
            while ($rec = mysql_fetch_row($rs)){
                echo '<li onclick="fill(\''. $inputId . '\',\''.$rec[0].'\')">' . $rec[0] . '</li>';
            }
            echo '</ul>';
        }
    } else {
        $query = null;
        if ($searchBy == 'vendor_name')
            $query = "SELECT vendor_name 
                        FROM vendor
                        WHERE vendor_name like '%$text%' 
                        ORDER BY vendor_name ASC LIMIT 10 ";            
        else if ($searchBy == 'manufacturer_name')
            $query = "SELECT manufacturer_name 
                        FROM manufacturer
                        WHERE manufacturer_name like '%$text%' 
                        ORDER BY manufacturer_name ASC LIMIT 10 ";
        else if ($searchBy == 'brand_name')
            $query = "SELECT brand_name 
                        FROM brand
                        WHERE brand_name like '%$text%' 
                        ORDER BY brand_name ASC LIMIT 10 ";
        else if ($searchBy == 'status_name')
            $query = "SELECT status_name 
                        FROM status
                        WHERE status_name like '%$text%' 
                        ORDER BY status_name ASC LIMIT 10 ";

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
