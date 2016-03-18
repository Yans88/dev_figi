<?php

function count_mobile_cart()
{
	$result = 0;
	$query  = "SELECT count(*) FROM mobile_cart ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_mobile_carts($sort = 'asc', $start = 0, $limit = 10)
{
	$query  = "SELECT * 
				FROM mobile_cart mc 
				ORDER BY cart_name $sort 
				LIMIT $start,$limit ";
	return mysql_query($query);
}

function get_mobile_cart_list($swap = false, $lowercase = false)
{
	$data = array();
    $query  = "SELECT id_cart, cart_name FROM mobile_cart ORDER BY cart_name ";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[1] = strtolower($rec[1]);
            $data[$rec[1]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[1];
    return $data;
}

/*
function count_mobile_cart_item($searchby = null, $searchtext = null, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) FROM item            
			   LEFT JOIN category ON item.id_category=category.id_category 
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   WHERE category_type = 'EQUIPMENT' ";           
    if (!empty($searchby) && !empty($searchtext))
        $query .= " AND $searchby like '$searchtext%' ";
    if ($dept > 0)
        $query .= " AND category.id_department = $dept ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_mobile_cart_items($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
               FROM item 
			   LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department 
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   WHERE category_type = 'EQUIPMENT' ";
    if (!empty($searchby) && !empty($searchtext))
        $query .= " AND $searchby like '$searchtext%'  ";
	if ($dept > 0)
		$query .= " AND category.id_department = $dept ";
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
    
	return $rs;
}
*/

function get_mobile_cart_items($id = 0){
    $result = array();
    $query = "SELECT mci.id_item, i.asset_no, i.serial_no, i.issued_to, i.id_status, status_name, category_name, brand_name, u.full_name 
                FROM mobile_cart_item mci 
                LEFT JOIN item i ON mci.id_item = i.id_item 
                LEFT JOIN brand b ON b.id_brand = i.id_brand 
                LEFT JOIN user u ON u.id_user = i.issued_to 
                LEFT JOIN status s ON s.id_status = i.id_status 
                LEFT JOIN category c ON c.id_category = i.id_category 
                WHERE id_cart = $id";
	
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}

function get_id_loan($id_item){
	$query = "select id_loan, id_item from loan_item where id_item in($id_item) group by id_loan order by id_loan ASC ";
	//print_r($query);
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[$rec['id_item']] = $rec['id_loan'];
    return $result;
}

function get_data_loan(){
	$query = "select id_loan, loan_date, return_date, name from loan_out ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs)){
		    $result[$rec['id_loan']]['loan_date'] = $rec['loan_date'];
            $result[$rec['id_loan']]['return_date'] = $rec['return_date'];		
            $result[$rec['id_loan']]['isued_to'] = $rec['name'];		
		}    
		
    return $result;
}


function cnt_loan($id_loan = 0, $id_item){
	$query = "select count(*) as cnt, id_loan from loan_item where id_loan = '$id_loan' and id_item in($id_item) group by id_loan";
	
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[$rec['id_loan']] = $rec['cnt'];
    return $result;
}

function save_mobile_cart($id, $data)
{

    if ($id > 0) {
        $query = "REPLACE INTO mobile_cart (id_cart, cart_name, cart_status, number_of_item, id_department) 
                  VALUES ($id, '$data[cart_name]', '$data[cart_status]', '$data[number_of_item]', '$data[id_department]')";
    } else {
        $query = "INSERT INTO mobile_cart (cart_name, cart_status, number_of_item, id_department) 
                  VALUES ('$data[cart_name]', '$data[cart_status]', '$data[number_of_item]', '$data[id_department]')";
    }
    mysql_query($query);
    if (mysql_affected_rows()>0){
        if ($id == 0) 
            $id = mysql_insert_id();
    }
    return $id;
}

function get_machine_records($items = null)
{
    $result = array();
    if ($items != null && count($items)>0){
        $query = 'SELECT id_item, id_machine 
                    FROM machine_info mi 
                    WHERE id_item IN ('.implode(',', $items).')';
        $rs = mysql_query($query);
        if ($rs && mysql_num_rows($rs)>0)
            while ($rec = mysql_fetch_row($rs))
                $result[$rec[0]] = $rec[1];
    }
    return $result;
}


?>
