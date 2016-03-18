<?php

include '../util.php';
include '../common.php';

$serial_no = !empty($_POST['serial_no']) ? $_POST['serial_no'] : null;
$asset_no = !empty($_POST['asset_no']) ? $_POST['asset_no'] : null;
$status_list = implode(', ', array(AVAILABLE_FOR_LOAN, STORAGE));

if ( $asset_no != null ){
	$asset_no = mysql_real_escape_string($asset_no);
    $query = "SELECT asset_no, serial_no, id_item, category_name, brand_name, model_no, loan_period, item.id_category  
                FROM item 
                LEFT JOIN category ON category.id_category = item.id_category 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE  asset_no = '$asset_no'OR serial_no = '$asset_no' ";
	if ($serial_no!=null)
		$query .= " AND serial_no = '$serial_no'";
	if (!empty($status_list))
		$query .= " AND id_status IN ($status_list) "; 
    //$query .= " ORDER BY asset_no, serial_no ASC LIMIT 10 ";
    $rs = mysql_query($query);
    //error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs) > 0)){
        $rec = mysql_fetch_row($rs);
        $row = "$rec[0]|$rec[1]|$rec[2]|0|$rec[3]|$rec[4]|$rec[5]|$rec[6]|$rec[7]";
        $id_category = $rec[7];

        // find if the item is a mobile cart member
        $query = "SELECT id_cart FROM mobile_cart_item WHERE id_item = $rec[2]";
        $rs = mysql_query($query);
        if ($rs &&  (mysql_num_rows($rs) > 0)){
            $rec = mysql_fetch_row($rs);
            $id_cart = $rec[0];
            // get the all member of the cart
            $query = "SELECT asset_no, serial_no, mci.id_item, id_cart, category_name, brand_name, model_no, loan_period, item.id_category     
                        FROM mobile_cart_item mci 
                        LEFT JOIN item ON item.id_item = mci.id_item 
						LEFT JOIN category ON category.id_category = item.id_category 
						LEFT JOIN brand ON brand.id_brand = item.id_brand 
                        WHERE id_cart = '$id_cart' AND item.id_status IN ($status_list)";
            $rs = mysql_query($query);
            //echo mysql_error().$query;
            $row = null;
            while ($rec = mysql_fetch_row($rs)){
                if (!empty($row)) $row .= ',';
        		$row .= "$rec[0]|$rec[1]|$rec[2]|$rec[3]|$rec[4]|$rec[5]|$rec[6]|$rec[7]|$rec[8]";
            }
        }
		$cats = array();
		$query = "SELECT id_accessory, accessory_name FROM accessories WHERE id_category='$id_category' ORDER by order_no ASC ";
		$rs = mysql_query($query);
		if ($rs)
			while($rec = mysql_fetch_row($rs))
			$cats[]	= "$rec[0]-$rec[1]";
		
		echo $row.'|'.implode('|', $cats);
    }
}

