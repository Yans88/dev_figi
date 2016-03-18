<?php

include '../util.php';
include '../common.php';
include '../authcheck.php';

$serial_no = !empty($_POST['serial_no']) ? $_POST['serial_no'] : null;
$asset_no = !empty($_POST['asset_no']) ? $_POST['asset_no'] : null;
$status_list = implode(', ', array(AVAILABLE_FOR_LOAN, STORAGE));
$dept = USERDEPT;

if (($serial_no != null) && ($asset_no != null)){
    $query = "SELECT asset_no, serial_no, id_item, brand_name, model_no, 
                date_format(date_of_purchase, '%d-%b-%Y') date_of_purchase  
                FROM item 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE  asset_no = '$asset_no' AND serial_no = '$serial_no' 
                AND id_status IN ($status_list)  AND id_owner = $dept";
    //$query .= " ORDER BY asset_no, serial_no ASC LIMIT 10 ";
    //            LEFT JOIN category c ON c.id_category = item.id_category 
    //echo ;
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    
    if ($rs && (mysql_num_rows($rs) > 0)){
        $rec = mysql_fetch_row($rs);
        echo "$rec[0]|$rec[1]|$rec[2]|$rec[5]|$rec[3]|$rec[4]";
        
    }
}
?>