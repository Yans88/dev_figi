<?php

function count_consumable_item($searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$result = 0;
	$query  = "SELECT count(dci.id_item)  
                FROM consumable_item dci 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   ";           
    if (!empty($searchtext))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " cat.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_consumable_items($orderby = 'item_code', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$query  = "SELECT dci.*, department_name, category_name 
                FROM consumable_item dci 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   ";           
    if (!empty($searchtext) && !empty($searchby))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " cat.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
    //echo mysql_error().$query;
    
	return $rs;
}

function get_consumable($id = 0)
{
    $result = array();
	$query  = "SELECT dci.*, department_name, category_name 
                FROM consumable_item dci 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE dci.id_item = $id ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_consumable_stock($id = 0)
{
    $result = 0;
	$rec = get_consumable($id);
    if (isset($rec['item_stock']))
        $result = $rec['item_stock'];
    return $result;
}

function get_purchase_item($id = 0)
{
    $result = array();
	$query  = "SELECT cii.*, dci.*, department_name, category_name, vendor_name  
                FROM consumable_item_in cii 
                LEFT JOIN consumable_item dci ON cii.id_item = dci.id_item 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                LEFT JOIN vendor v ON v.id_vendor = cii.id_vendor 
                WHERE cii.id_trx = $id ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_usage_item($id = 0)
{
    $result = null;
    $query = "SELECT *, date_format(trx_time, '%e-%b-%Y %H:%i') trx_time  
                FROM consumable_item_out cio 
                LEFT JOIN consumable_item_out_list ciol ON ciol.id_trx = cio.id_trx 
                LEFT JOIN consumable_item ci ON ci.id_item= ciol.id_item 
                LEFT JOIN category cat ON cat.id_category = ci.id_category 
                WHERE ciol.id_trx = $id 
                ORDER BY item_name DESC ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = $rs;
    return $result;
}

function get_consumable_item_by_code($item_code)
{
    $result = array();
	$query  = "SELECT dci.*, department_name, category_name 
                FROM consumable_item dci 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE dci.item_code = '$item_code' ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_consumer_signature($id)
{
    $result = null;
	$query  = "SELECT signature FROM consumable_user_signature WHERE id_trx = '$id' ";
    $rs = mysql_query($query);    
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_assoc($rs);
        $result = $rec['signature'];
    }
    return $result;
}



function save_consumable_item($id, $data)
{
    // get old data for existing item
    $olddata = get_consumable($id);
    $query = "REPLACE INTO consumable_item(id_item, item_code, item_name, item_stock, id_category)
              VALUES($id, '$data[item_code]', '$data[item_name]', '$data[item_stock]', '$data[id_category]')";
    mysql_query($query);
    //echo mysql_error().$query;
    $affected  = mysql_affected_rows();
    if (($affected > 0) && ($id == 0))
        $id = mysql_insert_id();

    return $id;
}

function purchase_consumable_item($id, $data)
{
    // get old data for existing item
    $olddata = get_consumable($id);
    // record item_in
    $query = "INSERT INTO consumable_item_in(id_item, trx_time, quantity, price, do_no, id_vendor)
              VALUES($id, now(), '$data[quantity]', '$data[price]', '$data[do_no]', '$data[id_vendor]')";
    mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_affected_rows() > 0){
        // update stock
        $query = "UPDATE consumable_item SET item_stock = item_stock + $data[quantity] 
                    WHERE id_item = $id ";
        mysql_query($query);
    }
    return $id;
}

function update_purchased_item($id, $data)
{
    // get old data for existing item
    $olddata = get_consumable($id);
    // record item_in
    $query = "UPDATE consumable_item_in 
                SET price = '$data[price]', do_no = '$data[do_no]', id_vendor = '$data[id_vendor]'
                WHERE id_trx = '$id' ";
    mysql_query($query);
    //echo mysql_error().$query;
    return $id;
}

// id_item,item_code,item_name,stock,category,department
function export_csv_consumable_item($path, $dept = 0)
{
    $dtf = '%d-%b-%Y %H:%i:%s';    
    $query  = "SELECT id_item, item_code, item_name, item_stock, category_name, department_name     
               FROM consumable_item ci 
               LEFT JOIN category cat ON cat.id_category = ci.id_category 
               LEFT JOIN department d ON d.id_department = cat.id_department 
               WHERE ci.id_item > 0 ";
    if ($dept > 0)
        $query .= " AND cat.id_department = $dept ";

    $fp = fopen($path, 'w');
    $header  = 'ItemID,ItemCode,ItemName,Stock,Category,Department';
    fputs($fp, $header."\r\n");
    $i = 0;  
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_num_rows($rs)) {   
        while ($rec = mysql_fetch_row($rs)){
            fputs($fp, implode(',', $rec) . "\r\n");
        }
    }
    fclose($fp);
}

function import_csv_consumable_item($path, $dept = 0)
{
    $row = 0;
    $result = 0; // upload failed
    if (!empty($path) && file_exists($path)) {
        if (($fp = fopen($path, 'r')) !== FALSE){
            $cols = fgetcsv($fp, 512, ',');
// id_item,item_code,item_name,stock,category,department
            if (count($cols) >= 6){ // 
                $departments = get_department_list();                
                $categories = get_category_list('CONSUMABLE',$dept,true,true);
                $my_dept = strtolower($departments[$dept]);
                $item_code_map = array();
                while ($cols = fgetcsv($fp, 512, ',')){
                    $row++;
                    $deptname = strtolower($cols[5]); // department
                    if ($my_dept != $deptname) continue;
                    $catname = strtolower($cols[4]);
                    $cid = isset($categories[$catname]) ? $categories[$catname] : 0;
                    if (!empty($cols[1]) && ($cid > 0)){
                        $query  = 'INSERT INTO consumable_item (item_code, item_name, item_stock, id_category) ';
                        $query .= "VALUES ('$cols[1]', '$cols[2]', '$cols[3]', '$cid')";
                        mysql_query($query);
                        if (mysql_affected_rows() == 1){
                            $id_item = mysql_insert_id();
                            $result++;
                        }
                    }
                }
                if ($result == 0)
                    $result = ($row > 0) ? -4 : -3;
            } else // colums is mismatch
                $result = -1;
            fclose($fp);
        } else
            $result = -2; // system error, can't open the file      
    }
    return $result;
}

function add_consumable_item($item_code, $item, $description, $author_id, $publisher_id, $dept)
{
    $result = 0;
    $query = "INSERT INTO consumable_item(item_code, item, description, id_author, id_publisher, id_department)
              VALUES('$item_code', '$item', '$description', $author_id, $publisher_id, $dept)";
    mysql_query($query);
    if (mysql_affected_rows() > 0){
        $result = mysql_insert_id();
        // prepare stock
        mysql_query('INSERT INTO consumable_stock(id_item, stock)  VALUES (' . $result . ', 0)');
    }
    return $result;
}



?>
