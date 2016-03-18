<?php

function get_email_tobe_notified($dept, $mod){
	$result = array();
    $grplist = GRPADM . ',' . GRPHOD;
	$query = "SELECT user_email FROM user 
                WHERE id_department = '$dept' AND id_group IN ($grplist) 
                ORDER BY user_email";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){
		while ($rec = mysql_fetch_row($rs))
			$result[] = $rec[0];
	}
	return $result;
}
/*
function get_item_from_serial_no($serialstr = null){
    $result = array();
    $asset_numbers = array();
    $serial_numbers = array();
    if ($serialstr != null) {
        $serials = explode(',', $serialstr);
        foreach ($serials as $no){
            $cols = explode('|', $no);
            if (count($cols) == 2){
                $asset_no = trim($cols[0]);
                $serial_no = trim($cols[1]);
                if (!empty($serial_no))
                    $serial_numbers[] = "'$serial_no'";
                else if (!empty($asset_no))
                    $asset_numbers[] = "'$asset_no'";
            }
        }
        
        $result = array();
        if (count($serial_numbers)>0)
            $result[] = ' serial_no IN (' . implode(',', $serial_numbers) . ')';
        if (count($asset_numbers)>0)
            $result[] = ' asset_no IN (' . implode(',', $asset_numbers) . ')';
            
        if (count($result)>0){            
            $query = "SELECT DISTINCT(id_item) FROM item WHERE " . implode(' OR ', $result);
            $rs = mysql_query($query);
            $result = array();     
            if ($rs && mysql_num_rows($rs)>0)
                while ($rec = mysql_fetch_assoc($rs))
                    $result[] = $rec['id_item'];
        }
    }
    return $result;
}
*/

function get_item_from_machine_id($id = 0){
    $result = array();
    $query = "SELECT *, date_format(date_of_purchase, '%d-%b-%Y') date_of_purchase, 
				date_format(defect_date, '%d-%b-%Y') defect_date, l.location_name 
                FROM machine_info mi 
                LEFT JOIN item i ON i.id_item = mi.id_item 
                LEFT JOIN category c ON c.id_category = i.id_category 
                LEFT JOIN brand b ON b.id_brand = i.id_brand 
                LEFT JOIN vendor v ON v.id_vendor = i.id_vendor 
				LEFT JOIN location l ON l.id_location = i.id_location 
                WHERE id_machine = $id AND i.id_item = mi.id_item ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0)
        $result = mysql_fetch_assoc($rs);

    return $result;
}



function machrec_count_item($searchby = null, $searchtext = null, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) 
                FROM machine_info mi 
                LEFT JOIN item ON item.id_item = mi.id_item  
			   LEFT JOIN category ON item.id_category=category.id_category 
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   WHERE category_type = 'EQUIPMENT' AND item.id_item IS NOT NULL ";           
    if (!empty($searchby) && !empty($searchtext))
        $query .= " AND $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $query .= " AND id_owner = $dept ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function machrec_get_items($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
	$query  = "SELECT mi.*, item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
                FROM machine_info mi 
                LEFT JOIN item ON item.id_item = mi.id_item  
			   LEFT JOIN category ON item.id_category=category.id_category 
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   LEFT JOIN department dept ON dept.id_department=category.id_department 
			   WHERE category_type = 'EQUIPMENT' AND item.id_item IS NOT NULL  ";
    if (!empty($searchby) && !empty($searchtext))
        $query .= " AND $searchby like '%$searchtext%'  ";
	if ($dept > 0)
		$query .= " AND id_owner = $dept ";
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
	return $rs;
}
/*
function get_item($id = 0)
{
    $result = null;
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
               FROM item 
			   LEFT JOIN category ON item.id_category=category.id_category 
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   LEFT JOIN department dept ON dept.id_department=category.id_department 
			   WHERE id_item= $id ";
	$rs = mysql_query($query);
    if ( $rs && mysql_num_rows($rs) > 0)
        $result = mysql_fetch_assoc($rs);
    
	return $result;
}
*/

function machrec_get_item($id = 0)
{
    $result = null;
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
                FROM machine_info mi 
                LEFT JOIN item ON item.id_item = mi.id_item  
			   LEFT JOIN category ON item.id_category=category.id_category 
			   LEFT JOIN status ON item.id_status=status.id_status 
			   LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
			   LEFT JOIN brand ON item.id_brand=brand.id_brand 
			   LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
			   LEFT JOIN department dept ON dept.id_department=category.id_department 
			   WHERE id_machine = $id ";
	$rs = mysql_query($query);
    if ( $rs && mysql_num_rows($rs) > 0)
        $result = mysql_fetch_assoc($rs);
    
	return $result;
}

function get_machrec_by_item($id = 0)
{
    $result = null;
	$query  = "SELECT mi.* 
                FROM machine_info mi 
                WHERE id_item = $id ";
	$rs = mysql_query($query);
    if ( $rs && mysql_num_rows($rs) > 0)
        $result = mysql_fetch_assoc($rs);
    
	return $result;
}

function machrec_count_history($id = 0, $searchby = null, $searchtext = null, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) 
                FROM machine_history mh 
                WHERE id_machine = $id ";
    /*
    if (!empty($searchby) && !empty($searchtext))
        $query .= " AND $searchby like '$searchtext%' ";
    if ($dept > 0)
        $query .= " AND category.id_department = $dept ";
    */
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function machrec_get_histories($id = 0)
{
    $result = null;
	$query  = "SELECT *, date_format(period_from, '%d-%b-%Y') period_from, 
                date_format(period_to, '%d-%b-%Y') period_to, mh.vendor_name mh_vendor_name 
                FROM machine_history mh 
                LEFT JOIN machine_info mi ON mh.id_machine = mi.id_machine  
                LEFT JOIN item ON item.id_item = mi.id_item  
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                LEFT JOIN department dept ON dept.id_department=category.id_department 
                WHERE mh.id_machine = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0)
        $result = $rs;
    
	return $result;
}

function machrec_get_history($id = 0)
{
    $result = null;
	$query  = "SELECT *, date_format(period_from, '%d-%b-%Y') period_from, 
                date_format(period_to, '%d-%b-%Y') period_to, mh.vendor_name service_vendor_name  
                FROM machine_history mh 
                LEFT JOIN machine_info mi ON mh.id_machine = mi.id_machine  
                LEFT JOIN machine_issued_out mio ON mh.id_history = mio.id_history 
                LEFT JOIN item ON item.id_item = mi.id_item  
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                LEFT JOIN department dept ON dept.id_department=category.id_department 
                WHERE mh.id_history = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){
        $result = mysql_fetch_assoc($rs);
    }
	return $result;
}

function machrec_get_history_out($id = 0)
{
    $result = null;
	$query  = "SELECT *, date_format(issued_date, '%d-%b-%Y') issued_date 
                FROM machine_issued_out 
                WHERE id_history = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){
        $result = mysql_fetch_assoc($rs);
    }
	return $result;
}


function machrec_get_history_in($id = 0)
{
    $result = null;
	$query  = "SELECT *, date_format(received_date, '%d-%b-%Y') received_date 
                FROM machine_issued_in 
                WHERE id_history = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){
        $result = mysql_fetch_assoc($rs);
    }
	return $result;
}

function machrec_get_issue_signature($id = 0)
{
    $result = null;
	$query  = "SELECT * 
                FROM machine_issued_out_signature mios 
                WHERE mios.id_history = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){
        $rec = mysql_fetch_assoc($rs);
        $result = $rec['vendor_contact_signature'];
    }
	return $result;
}

function machrec_get_return_signature($id = 0)
{
    $result = null;
	$query  = "SELECT * 
                FROM machine_issued_out_signature mios 
                WHERE mios.id_history = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){
        $rec = mysql_fetch_assoc($rs);
        $result = $rec['vendor_contact_signature'];
    }
	return $result;
}


?>
