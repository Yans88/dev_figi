<?php
error_log('LOG:'.serialize($_POST));
include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$location = !empty($_POST['loc_id']) ? $_POST['loc_id'] : 0;
$key = !empty($_POST['key']) ? $_POST['key'] : null;
//$cat = !empty($_POST['catId']) ? $_POST['catId'] : 0;
$status_list = implode(', ', array(AVAILABLE_FOR_LOAN, STORAGE));

if ($text != null && $key == null){
    $query = "SELECT asset_no, serial_no, category_name, brand_name, model_no, id_item, item.id_category   
                FROM item 
                LEFT JOIN category ON category.id_category = item.id_category 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE item.id_status IN ($status_list) and id_location= $location AND (asset_no like '%$text%' OR serial_no like '%$text%') ";   
    $query .= " ORDER BY asset_no, serial_no ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
            //$what = (preg_match('/'.$text.'/i', $rec[0])) ? $rec[0] : $rec[1];        
			// serial,asset,category_name, brand_name, model_no
            $what = "$rec[1], $rec[0], $rec[2], $rec[3], $rec[4]";
            $fillarg= "$rec[1]|$rec[0]|$rec[2]|$rec[3]|$rec[4]|$rec[5]";
            echo '<li onclick="fill(\''. $inputId . '\',\''.$fillarg.'\', 1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}

if ($text != null && $key == 'report'){
    $query = "SELECT asset_no, serial_no, category_name, brand_name, model_no, id_item, item.id_category   
                FROM item 
                LEFT JOIN category ON category.id_category = item.id_category 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE asset_no like '%$text%' OR serial_no like '%$text%' ";   
    $query .= " ORDER BY asset_no, serial_no ASC LIMIT 10 ";
    $rs = mysql_query($query);
    error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
            $what = "$rec[1], $rec[0], $rec[2], $rec[3], $rec[4]";
            $fillarg= "$rec[1],$rec[0],$rec[2],$rec[3],$rec[4]";
            echo '<li onclick="fill(\''. $inputId . '\',\''.$fillarg.'\', 1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}

?>
