<?php

/*
 utility routines related to item
*/


function build_manufacturer_combo($selected = -1) {
    
    return build_combo('id_manufacturer', get_manufacturer_list(), $selected);
}


function build_vendor_combo($selected = -1) {
    
    return build_combo('id_vendor', get_vendor_list(), $selected);
}

function build_brand_combo($manufacturer = 0, $selected = -1) {
    
    return build_combo('id_brand', get_brand_list(), $selected);
}

// category
function get_category_list($type = null, $department = 0, $swap = false, $lowercase = false) {
    $data = array();
    $wheres = array();
    $query = 'SELECT id_category,category_name FROM category ';
    if ($type != null) 
        $wheres[] = " category_type = '$type' ";
    //if ($department > 0) $wheres[] = " id_department = $department ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
    $query .= ' ORDER BY category_name ASC ';
    
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

function build_category_combo($type = null, $selected = -1, $department = 0, $onchange = null) {
    
    return build_combo('id_category', get_category_list($type, $department), $selected, $onchange);
}

function get_category_type_list() {
  $data = array();
  $query = 'SELECT id_category,category_type FROM category';
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        $data[$rec[0]] =$rec[1];
    return $data;
}

// status
function get_status_list($swap = false, $lowercase = false) {
  $data = array();
  $query = 'SELECT id_status,status_name FROM status';
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
           $data[$rec[1]] = $rec[0];
        } else
            $data[$rec[0]] = $rec[1];
    return $data;
}

function build_status_combo($selected = -1) {
    
    return build_combo('id_status', get_status_list(), $selected);
}

// spec
function get_specification_list($category = 0) {
    $data = array();
    $query = 'SELECT spec_id,spec_name FROM specification WHERE id_category = ' .$category;
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs)
        while ($rec = mysql_fetch_row($rs))
            $data[$rec[0]] =$rec[1];
    return $data;
}

function get_category($id_category = 0)
{
    $result = null;
    $query = 'SELECT * FROM category WHERE id_category = ' .$id_category;
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_assoc($rs);
        $result = $rec;
    }
    return $result;	
}

function get_department($id_department = 0)
{
    $result = null;
    $query = 'SELECT * FROM department WHERE id_department = ' .$id_department;
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_assoc($rs);
        $result = $rec;
    }
    return $result;	
}

function get_category_code($id_category = 0)
{
    $result = null;
    $query = 'SELECT category_code FROM category WHERE id_category = ' .$id_category;
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;	
}

function get_next_id($id_category = 0)
{
    $result = 0;
    $query = 'SELECT MAX(id_item) FROM  item WHERE id_category = ' . $id_category;
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result+1;	
}


function generate_asset_no($id_category = 0, $date_of_purchase = null)
{
    if (empty($date_of_purchase))
        $year = date('Y');
    else
        $year = substr($date_of_purchase, 0, 4);
    $category = get_category($id_category);
    //$catcode = get_category_code($id_category);
    $department = get_department($category['id_department']);
    $next_id = get_next_id($id_category);
    $sn = str_pad($next_id, ASSETNO_SN_LENGTH, '0', STR_PAD_LEFT);
    $templates = array('/{DEPT-ID}/', '/{DEPT-CODE}/', '/{CAT-ID}/', '/{CAT-CODE}/', '/{YEAR}/', '/{SN}/');
    $contents = array($category['id_department'], $department['department_code'], $category['id_category'], $category['category_code'], $year, $sn);
    $assetno = preg_replace($templates, $contents, ASSETNO_FORMAT);
    
    return $assetno;
}

// save item
function save_item($id = 0, $data){
  $saved = 0;
  $issued_date = convert_date($data['issued_date']);
  $date_of_purchase = convert_date($data['date_of_purchase']);
  $warranty_end_date = convert_date($data['warranty_end_date']);
  $org_invoice = null;
  $status_defect =  @mysql_escape_string($data['status_defect']);
  $id_owner = USERDEPT;
  if ($id == 0){
    // generate the asset_no : CATCODE-PURCHASE_YEAR-SEQUENTIAL
    if (AUTO_GENERATED_ASSETNO)
        $asset_no = generate_asset_no($data['id_category'], $date_of_purchase);
    else
        $asset_no = $data['asset_no'];
    $query  = "INSERT INTO item (asset_no, serial_no, issued_to, issued_date, id_category, id_vendor, 
               id_location, model_no, brief, cost, invoice, date_of_purchase, warranty_periode,
               warranty_end_date, id_brand, id_status, status_update, status_defect, id_owner, id_department) ";
    $query .= "VALUES ('$asset_no', '$data[serial_no]', '$data[issued_to]', '$issued_date', 
               '$data[id_category]', '$data[id_vendor]', '$data[id_location]', 
               '$data[model_no]', '$data[brief]', '$data[cost]', '$data[invoice]', '$date_of_purchase', 
               '$data[warranty_periode]', '$warranty_end_date', '$data[id_brand]',
               '$data[id_status]', now(), '$status_defect', $id_owner, $id_owner)";
    mysql_query($query);
    if (mysql_affected_rows() > 0){
        $id = mysql_insert_id();
        user_log(LOG_CREATE, 'Create item with asset no '. $data['asset_no']. '(ID:'. $id.')');
    }
    $saved = $id;
  } 
  else {
    /*
    $query = "SELECT invoice FROM item WHERE id_item = $id ";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $org_invoice = $rec[0];
    */
    //asset_no = '$data[asset_no]', 
    $query = "UPDATE item SET 
               serial_no = '$data[serial_no]', issued_to = '$data[issued_to]',  
               issued_date = '$issued_date',  id_category = '$data[id_category]',  id_vendor = '$data[id_vendor]',  
               id_location = '$data[id_location]',  model_no = '$data[model_no]',  
               brief = '$data[brief]',  cost = '$data[cost]',  invoice = '$data[invoice]',  date_of_purchase = '$date_of_purchase',  
               warranty_periode = '$data[warranty_periode]', id_brand = '$data[id_brand]',
               warranty_end_date = '$warranty_end_date'  
               WHERE id_item = $id";
    mysql_query($query);
    if (mysql_affected_rows()>0){
        user_log(LOG_UPDATE, 'Update item with asset no '. $data['asset_no']. '(ID:'. $id.')');
    }

    
  }
 
  if ($id > 0){ 
    // update item's specification
    $specs = get_specification_list($data['id_category']);
    foreach ($specs as $k => $v){
      $idx = str_replace(' ', '_', $v);
      if (isset($data[$idx])){
          $query = "REPLACE INTO item_specification(id_item, spec_id, spec_value) 
                    VALUES($id, $k, '$data[$idx]')";
          mysql_query($query); 
        }
    }
    /*
    2012-06-20:  enable to update the status to set initial status of an item and in case need to fix unappropriate status

     no more status update on edit item
     since item status changed on the other processes, such as loan, condemned
    */ 
    // get last status & compare to new data, if differnt there's an update
    $query = "SELECT id_status, status_defect, status_update FROM item WHERE id_item=$id";
    $rs = mysql_query($query);
    $rec = mysql_fetch_assoc($rs);
    if (($rec['id_status'] != $data['id_status']) or ($rec['status_defect'] != $data['status_defect'])){      
      // update latest status in item table
      $query = "UPDATE item 
                SET id_status = '$data[id_status]', status_update = NOW(), status_defect = '$data[status_defect]' 
                WHERE  id_item = $id ";
      mysql_query($query);
      // put in item_status as history, from item
      $query = "INSERT INTO item_status(id_item, id_status, last_update, update_defect) 
                VALUES ($id, $rec[id_status], DATE('$rec[status_update]'), '$rec[status_defect]')";
      mysql_query($query);
    }
    
    // manage images 
    // delete first if specified
    if (!empty($data['deleted_images'])){
        $query = 'DELETE FROM item_image WHERE id_image IN (' . $data['deleted_images'] . ')';
        mysql_query($query);
    }
    if (isset($_FILES['fimage']) && count($_FILES['fimage']) > 0){
        for ($i = 0; $i < count($_FILES['fimage']['name']); $i++){
            $filesize = $_FILES['fimage']['size'][$i];
            $filename = $_FILES['fimage']['name'][$i];
            $filetemp = $_FILES['fimage']['tmp_name'][$i];
            $errorcode = $_FILES['fimage']['error'][$i];
            
            if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                $data_img = base64_encode(file_get_contents($filetemp));
                $filethumb = resize($filetemp, THUMB_WIDTH, THUMB_HEIGHT, tempnam('/tmp', 'thumb'));
                $thumbnail = base64_encode(file_get_contents($filethumb)); 
                $query  = "INSERT INTO item_image(id_item,  filename, data, thumbnail) ";
                $query .= "VALUES('$id', '$filename', '$data_img', '$thumbnail')";
                mysql_query($query);
                //echo mysql_error().$query;
            }
        }
    }
    // manage attachment
    if (!empty($data['deleted_attachments'])){
        //$query = 'DELETE FROM item_attachment WHERE id_attach IN (' . $data['deleted_attachments'] . ')';
        $query = 'DELETE FROM invoice_attachment WHERE id_attach IN (' . $data['deleted_attachments'] . ')';
        mysql_query($query);
    }
    
    if (isset($_FILES['fattachment']) && count($_FILES['fattachment']) > 0){
        for ($i = 0; $i < count($_FILES['fattachment']['name']); $i++){
            $filesize = $_FILES['fattachment']['size'][$i];
            $filename = $_FILES['fattachment']['name'][$i];
            $filetemp = $_FILES['fattachment']['tmp_name'][$i];
            $errorcode = $_FILES['fattachment']['error'][$i];
            
            if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                $data_raw = base64_encode(file_get_contents($filetemp));
                //$query  = "INSERT INTO item_attachment(id_item, filename, data) ";
                //$query .= "VALUES('$id', '$filename', '$data')";
                $query  = "INSERT INTO invoice_attachment(invoice_no, filename, data) ";
                $query .= "VALUES('$data[invoice]', '$filename', '$data_raw')";
                mysql_query($query);
                //echo mysql_error().$query;
                //print_r($data); echo $query;
            }
        }
    }
  }
  return $id;
} // save


function import_csv_item($path, $kind = 0)
{
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $row = 0;
    $result['code'] = -2;
    $result['success'] = 0;
    $result['fail'] = 0;
    $result['unknown_user'] = 0;
    $result['unknown_category'] = 0;
    $result['unknown_vendor'] = 0;
    $result['unknown_brand'] = 0;
    $result['unknown_status'] = 0;
    
    if (!empty($path) && file_exists($path)) {
        if (($fp = fopen($path, 'r')) !== FALSE){
            $cols = fgetcsv($fp, 1024, ',');
            if (count($cols) >= 25){ // item:25, spec:29 = 54
                $users = get_user_list(true, false, true);
                $categories = get_category_list(null,$dept,true,true);
                $vendors = get_vendor_list(true,true);
                $brands = get_brand_list(true,true);
                $statuses = get_status_list(true,true);
                $locations = get_location_list(true,true);
                //print_r($statuses);
                while ($cols = fgetcsv($fp, 512, ',')){
                    $row++;
                    $can_continue = true;
                    $username = strtolower($cols[4]); // issued to
                    $catname = strtolower($cols[6]); // category
                    $vendorname = strtolower($cols[7]); // vendor
                    $brandname = strtolower($cols[10]); // brand
                    $location = strtolower($cols[11]); // brand
                    $statusname = strtolower($cols[18]); // status
                    if (isset($users[$username])) $uid = $users[$username];
                    else { $uid = 0; $result['unknown_user']++; $can_continue = false;}
                    if (isset($categories[$catname])) $cid = $categories[$catname];
                    else { $cid = 0; $result['unknown_category']++; $can_continue = false; }
                    if (isset($vendors[$vendorname])) $vid = $vendors[$vendorname];
                    else { $vid = 0; $result['unknown_vendor']++; $can_continue = false; }
                    if (isset($brands[$brandname])) $bid = $brands[$brandname];
                    else { $bid = 0; $result['unknown_brand']++; $can_continue = false; }
                    if (isset($statuses[$statusname])) $sid = $statuses[$statusname];
                    else { $sid = 0; $result['unknown_status']++; $can_continue = false; }
                    if (isset($locations[$location])) $lid = $locations[$location];
                    else { 
                        $lid = 0; 
                        if (!empty($location)){
                            $lid = set_location($cols[11]); // original location text
                            $locations[strtolower($location)] = $lid;
                        }
                        $can_continue = true; 
                    }
                    if (!$can_continue) continue;
                    if (empty($cols[5]))
                        $issued_date = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else
                        $issued_date = convert_uk_date($cols[5], 'Y-m-d H:i:s');
                    if (empty($cols[15]))
                        $date_of_purchase = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else
                        $date_of_purchase = convert_uk_date($cols[15], 'Y-m-d H:i:s');
                    if (empty($cols[17]))
                        $warranty_end_date = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else
                        $warranty_end_date = convert_uk_date($cols[17], 'Y-m-d H:i:s');
                    if (empty($cols[19]))
                        $status_update = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else 
                        $status_update = convert_date($cols[19], 'Y-m-d H:i:s');
                    if (empty($cols[16])) $cols[16] = 0;
                    $asset_no = $cols[1];
                    if (AUTO_GENERATED_ASSETNO)
                        $asset_no = generate_asset_no($cid, $date_of_purchase);

                    $query  = 'INSERT INTO item (asset_no, serial_no, model_no, issued_to, issued_date, id_category, id_vendor, 
                                id_brand, id_location, brief, cost, invoice, date_of_purchase, warranty_periode, 
                                warranty_end_date, id_status, status_update, status_defect, id_owner, id_department) ';
                    $query .= "VALUES ('$asset_no', '$cols[2]', '$cols[3]', '$uid', '$issued_date', '$cid', '$vid', '$bid', 
                                '$lid', '$cols[12]', '$cols[13]', '$cols[14]', '$date_of_purchase', '$cols[16]', '$warranty_end_date', 
                                '$sid', '$status_update', '$cols[20]', $dept, $dept)";
                    mysql_query($query);
                    //echo mysql_error();// . $query;                    
                    if (mysql_affected_rows() == 1){
                        $id_item = mysql_insert_id();
                        /*
                        $vendor_name = $cols[7];
                        // update  vendor
                        if ($vid > 0)
                            $query  = "UPDATE vendor SET contact_no_1='$cols[21]',contact_email_1='$cols[22]',
                                        contact_no_2='$cols[23]',contact_email_2='$cols[24]' WHERE id_vendor = $vid ";
                        else
                            $query  = "INSERT INTO vendor(vendor_name,contact_no_1,contact_email_1,contact_no_2,contact_email_2) 
                                        VALUES('$vendor_name','$cols[21]','$cols[22]','$cols[23]','$cols[24]')";
                        mysql_query($query);
                        if ($vid == 0){ // new vendor, update item
                            $vid = mysql_insert_id();
                            $query = "UPDATE item SET id_vendor = $vid WHERE id_item = $id_item";
                            mysql_query($query);
                        }
                        */
                        // store spec
                        if ($kind == 1) { // 1-included in csv, 0-excluded
                            $spec_list = get_specification_list($cid);
                            $specs = array_keys($spec_list);
                            for ($i = 0; $i < count($specs); $i++){
                                $idx = 25 + $i; // start at index 25th in csv zero based
                                $query  = "REPLACE INTO item_specification(id_item, spec_id, spec_value) ";
                                $query .= "VALUES('$id_item', '$specs[$i]', '$cols[$idx]')";
                                mysql_query($query);
                            }
                        }
                        $result['success']++;
                    } else
                        $result['fail']++;
                }
                $result['code'] = $result['success'];
                $result['fail'] += $result['unknown_brand'] + $result['unknown_category'] + 
                                   $result['unknown_user'] + $result['unknown_vendor'] + $result['unknown_status'];
            } else // colums is mismatch
                $result['code'] = -1;
            fclose($fp);
        } else
            $result['code'] = -3; // system error, can't open the file		
    }
    return $result;
}

function export_csv_item($path, $dept = 0, $cat = 0, $status = 0)
{
    $dtf = '%d-%b-%Y %H:%i:%s';
    $dept = (defined('USERDEPT')) ? USERDEPT : 0;
    
    $query =<<<SQL
SELECT item.id_item, item.asset_no, item.serial_no, item.model_no, full_name, date_format(item.issued_date, '$dtf') as issued_date,
category_name, vendor_name, manufacturer_name, department_name, brand_name, location_name location, item.brief,
item.cost, item.invoice, date_format(item.date_of_purchase, '$dtf') as date_of_purchase, item.warranty_periode, date_format(item.warranty_end_date, '$dtf') as warranty_end_date, 
status_name, date_format(status_update, '$dtf') as status_update, status_defect, vendor.contact_no_1, vendor.contact_email_1, vendor.contact_no_2, vendor.contact_email_2 
FROM item 
LEFT JOIN category ON category.id_category = item.id_category 
LEFT JOIN brand ON brand.id_brand = item.id_brand
LEFT JOIN department ON department.id_department = category.id_department 
LEFT JOIN manufacturer ON manufacturer.id_manufacturer = brand.id_manufacturer 
LEFT JOIN vendor ON vendor.id_vendor = item.id_vendor  
LEFT JOIN status ON item.id_status = status.id_status 
LEFT JOIN user ON item.issued_to = user.id_user 
LEFT JOIN location l ON item.id_location = l.id_location 
WHERE item.id_item > 0 
SQL;
    if ($cat>0)
        $query .= " AND item.id_category = '$cat' ";
    if ($status>0)
        $query .= " AND item.id_status = '$status' ";
    $query .= ' ORDER BY item.id_item ';

    $fp = fopen($path, 'w');
    $spec_list = get_specification_list($cat);
    $header  = 'Item ID, Asset No, Serial No, Model No, Issued To, Issued Date, Category, Vendor, Manufacturer, Department, ';
    $header .= 'Brand, Location, Brief, Cost, Invoice No, Date of Purchase, Warranty Period, Warranty End Date, '; 
    $header .= 'Status, Status Update, Update Defect, Vendor Contact No 1, Vendor Contact Email 1, Vendor Contact No 2, Vendor Contact Email 2';
    if (count($spec_list) > 0)
    $header .= ',' . implode(',', array_values($spec_list));
    fputs($fp, $header."\r\n");
    $i = 0;  
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    if (mysql_num_rows($rs)) {   
        while ($rec = mysql_fetch_row($rs)){
          
            $query = 'SELECT spec_id, spec_value  
                    FROM item_specification WHERE id_item = '.$rec[0];
            $rs_spec = mysql_query($query);
            $specs = array_fill_keys(array_keys($spec_list), '');
            while ($rec_spec = mysql_fetch_row($rs_spec)){
                $specs[$rec_spec[0]] = $rec_spec[1];
            }
            $line_rec = array_merge($rec, array_values($specs));
            //array_shift($line_rec);
            fputs($fp, implode(',', $line_rec) . "\r\n");
        }
    }
    fclose($fp);
}


function count_manufacturer()
{
    $result = 0;
    $query  = "SELECT count(*) FROM manufacturer ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_manufacturers($sort = 'asc', $start = 0, $limit = 10)
{
    $query  = "SELECT * 
                FROM manufacturer 
                ORDER BY manufacturer_name $sort 
                LIMIT $start,$limit ";
    return mysql_query($query);
}

function get_manufacturer_list($swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_manufacturer, manufacturer_name FROM manufacturer ORDER BY manufacturer_name ";
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

function count_brand()
{
    $result = 0;
    $query  = "SELECT count(*) FROM brand ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_brands($sort = 'asc', $start = 0, $limit = 10)
{
    $query  = "SELECT * 
                FROM brand b 
                LEFT JOIN manufacturer m ON m.id_manufacturer = b.id_manufacturer 
                ORDER BY brand_name $sort 
                LIMIT $start,$limit ";
    return mysql_query($query);
}

function get_brand_list($swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_brand, brand_name FROM brand ORDER BY brand_name ASC";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] = $rec[0];
        }else
            $data[$rec[0]] = $rec[1];
    return $data;
}

function count_spec_by_department($dept = 0)
{
    $result = 0;
    $query  = "SELECT count(*) 
                FROM specification s 
                LEFT JOIN category c ON c.id_category = s.id_category 
                WHERE id_department = $dept ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_specs_by_department($orderby = 'category_name', $sort = 'asc', $start = 0, $limit = 10, $dept = 0)
{
    $query  = "SELECT s.*, c.category_name 
                FROM specification s 
                LEFT JOIN category c ON c.id_category = s.id_category 
                WHERE id_department = $dept 
                ORDER BY $orderby $sort 
                LIMIT $start,$limit ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    return $rs;
}

function count_spec($cat = 0)
{
    $result = 0;
    $query  = "SELECT count(*) FROM specification WHERE id_category = $cat ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_specs($orderby = 'spec_id', $sort = 'asc', $start = 0, $limit = 10, $cat = 0)
{
    $query  = "SELECT s.*
                FROM specification s 
                WHERE s.id_category = $cat 
                ORDER BY $orderby $sort 
                LIMIT $start,$limit ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    return $rs;
}

function get_spec_list($cat = 0, $swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_spec, spec_name 
                FROM specification 
                WHERE id_category = $cat 
                ORDER BY spec_name ASC";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] = $rec[0];
        } else
            $data[$rec[0]] = $rec[1];
    return $data;
}

function spec_order($cat = 0, $id = 0, $move = null)
{
    $data = array();
    $sort = ($move == 'down') ? 'DESC' : 'ASC';
    $query  = "SELECT * FROM specification WHERE id_category = $cat ORDER BY order_no $sort";
    $rs = mysql_query($query);
    //	echo mysql_error().$query;	
    $prev = array();
    $curr = array();
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs)){
            $curr = $rec;
            if ($rec['spec_id'] != $id) {
                $prev = $rec;
                continue;
            }
            break;
            //$data[$rec['id']] = $rec['order_no'];
        }
        if (!empty($prev) && !empty($curr)){
            $pk = $prev['spec_id'];
            $query = "UPDATE specification SET order_no = $prev[order_no] WHERE spec_id = $id";
            mysql_query($query);
            $query = "UPDATE specification SET order_no = $curr[order_no] WHERE spec_id = $pk";
            mysql_query($query);
        }
    }
    
}


function count_accessories($cat = 0)
{
    $result = 0;
    $query  = "SELECT count(*) FROM accessories WHERE id_category = $cat ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_accessories($orderby = 'id_accessory', $sort = 'asc', $start = 0, $limit = 10, $cat = 0)
{
    $query  = "SELECT s.*
                FROM accessories s 
                WHERE s.id_category = $cat 
                ORDER BY $orderby $sort 
                LIMIT $start,$limit ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    return $rs;
}

function get_accessory_list($cat = 0, $swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_accessory, accessory_name 
                FROM accessories 
                WHERE id_category = $cat 
                ORDER BY accessory_name ASC";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs)){
        //print_r($rec);
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] = $rec[0];
        } else
            $data[$rec[0]] = $rec[1];
    }
    return $data;
}

function accessory_order($cat = 0, $id = 0, $move = null)
{
    $data = array();
    $sort = (strtolower($move) == 'down') ? 'DESC' : 'ASC';
    $query  = "SELECT * FROM accessories WHERE id_category = $cat ORDER BY order_no $sort";
    $rs = mysql_query($query);
    //echo mysql_error().$query;	
    $prev = array();
    $curr = array();
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs)){
            $curr = $rec;
            if ($rec['id_accessory'] != $id) {
                $prev = $rec;
                continue;
            }
            break;
            //$data[$rec['id']] = $rec['order_no'];
        }
        if (!empty($prev) && !empty($curr)){
            $pk = $prev['id_accessory'];
            $query = "UPDATE accessories SET order_no = $prev[order_no] WHERE id_accessory = $id";
            mysql_query($query);
            $query = "UPDATE accessories SET order_no = $curr[order_no] WHERE id_accessory= $pk";
            mysql_query($query);
        }
    }
    
}


function count_vendor()
{
    $result = 0;
    $query  = "SELECT count(*) FROM vendor ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_vendors($sort = 'asc', $start = 0, $limit = 10)
{
    $query  = "SELECT * 
                FROM vendor 
                ORDER BY vendor_name $sort 
                LIMIT $start,$limit ";
    return mysql_query($query);
}

function get_vendor_list($swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_vendor, vendor_name FROM vendor ORDER BY vendor_name";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] = $rec[0];
        } else
            $data[$rec[0]] = $rec[1];
    return $data;
}

function count_item($searchby = null, $searchtext = null, $dept = 0, $all = false)
{
    $result = 0;
    /*
    $query  = "SELECT count(*) FROM item            
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               WHERE category_type = 'EQUIPMENT' ";           
            */
    $query  = "SELECT count(*) FROM item 
               LEFT JOIN category ON item.id_category=category.id_category ";
    switch ($searchby){
    case 'status_name' : $query .= "LEFT JOIN status ON item.id_status=status.id_status "; break;
    case 'vendor_name' : $query .= "LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor "; break;
    case 'id_brand' : 
    case 'brand_name' : $query .= "LEFT JOIN brand ON item.id_brand=brand.id_brand "; break;
    case 'manufacturer_name' : 
        $query .= "LEFT JOIN brand ON item.id_brand=brand.id_brand "; 
        $query .= "LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer "; break;
    }
    //$query .= "WHERE category_type = 'EQUIPMENT' ";            
    $query .= "WHERE category_type = 'EQUIPMENT' ";            
    if (!$all) $query.= " AND item.id_status != " . CONDEMNED;
    if (!empty($searchby) && !empty($searchtext))
        if (strpos($searchby, 'id_')===false)
            $query .= " AND $searchby like '%$searchtext%' ";
        else
            $query .= " AND item.$searchby = '$searchtext' ";
    if ($dept > 0)
        $query .= " AND (item.id_department = $dept OR item.id_owner = $dept) ";
    $rs = mysql_query($query);
    ///cecho mysql_error().$query;
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    //echo $result;
    return $result;
}

function get_items($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0, $all = false)
{
    $fmt = '%d-%b-%Y';
    $query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name,
               DATE_FORMAT(date_of_purchase, '$fmt') date_of_purchase_fmt,
               DATE_FORMAT(warranty_end_date, '$fmt') warranty_end_date_fmt 
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               WHERE category_type = 'EQUIPMENT' ";
    if (!$all) $query.= " AND item.id_status != " . CONDEMNED;
    if (!empty($searchby) && !empty($searchtext))
        if (strpos($searchby, 'id_')===false)
            $query .= " AND $searchby like '%$searchtext%' ";
        else
            $query .= " AND item.$searchby = '$searchtext' ";
    if ($dept > 0)
        $query .= " AND (item.id_department = $dept OR item.id_owner = $dept) ";
    $query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    return $rs;
}

function get_item($id)
{
    $query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               WHERE id_item = $id ";
    $rs = mysql_query($query);
    if ($rs) 
        return mysql_fetch_assoc($rs);
    return null;
}

function get_barcodes($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
    $result = array();
    $rs = get_items($orderby, $sort, $start, $limit. $searchby, $searchtext, $dept);
    while ($rec = mysql_fetch_assoc($rs)){
        $assetno = $rec['asset_no'];
        
    }
    return $rs;
}

function count_issuance($dept = 0)
{
    $result = 0;
    $query  = "SELECT count(*) FROM item_issuance WHERE src_department = $dept ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    
    return $result;
}

function get_issuance($dept = 0, $start = 0, $limit = 10)
{
    $result = array();
    $query  = "SELECT ii.*, d.department_name, c.category_name, date_format(ii.issue_date, '%e-%b-%Y') issue_date  
                FROM item_issuance ii 
                LEFT JOIN category c ON ii.dst_category=c.id_category 
                LEFT JOIN department d ON c.id_department = d.id_department 
                WHERE src_department = '$dept'  
                ORDER BY issue_date DESC LIMIT $start,$limit ";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_assoc($rs))
        $result[] = $rec;
    return $result;
}

function count_category($type = 'equipment', $dept = 0)
{
    $result = 0;
    $query  = "SELECT count(*) FROM category WHERE category_type = '$type' ";
    //if ($dept > 0) $query .= ' AND id_department = ' . $dept;
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_categories($type = 'equipment', $sort = 'asc', $start = 0, $limit = 10, $dept = 0)
{
    /*
    $query  = "SELECT c.*, d.department_name 
                FROM category c 
                LEFT JOIN department d ON c.id_department = d.id_department 
                WHERE category_type = '$type' ";
    if ($dept > 0)
        $query .= ' AND c.id_department = ' . $dept;
    */
    $query  = "SELECT * FROM category WHERE category_type = '$type' ";
    $query .= " ORDER BY category_name $sort LIMIT $start,$limit ";
    return mysql_query($query);
}

function get_item_spec($id) 
{
    $specs = array();
    $query = 'SELECT spec_id, spec_value  
              FROM item_specification WHERE id_item = '.$id;
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
      $specs[$rec[0]] = $rec[1];
    return $specs;
}

function get_pictures($id)
{
    $result = array();
    $query = 'SELECT id_image, filename FROM item_image WHERE id_item = '.$id;
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
      $result[$rec[0]] = $rec[1];
    return $result;
}

function get_invoice_attachments($invoice)
{
    $result = array();
    $query = "SELECT id_attach, filename FROM invoice_attachment WHERE invoice_no = '$invoice'";
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    while ($rec = mysql_fetch_assoc($rs))
      $result[] = $rec;
    return $result;
}

function get_attachments($id)
{
    $result = array();
    $query = 'SELECT id_attach, filename, description FROM item_attachment WHERE id_item = '.$id;
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_assoc($rs))
      $result[] = $rec;
    return $result;
}

function check_machine_record($id = 0)
{
    $result = 0;
    $query = "SELECT id_machine FROM machine_info WHERE id_item = $id";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_item_from_serial_no($serialstr = null){
    $result = array();
    $asset_numbers = array();
    $serial_numbers = array();
    if ($serialstr != null) {
        $serials = explode(',', $serialstr);
        foreach ($serials as $no){
            //echo "-$no-";
            $cols = explode('|', $no);
            if (count($cols) >= 2){
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
            //echo $query;
            $result = array();     
            if ($rs && mysql_num_rows($rs)>0)
                while ($rec = mysql_fetch_assoc($rs))
                    $result[] = $rec['id_item'];
        }
    }
    return $result;
}

function get_item_issue($id)
{
    $result = array();
    $query  = "SELECT ii.*, d.department_name, c.category_name, date_format(ii.issue_date, '%e-%b-%Y %H:%i') issue_date,
                (SELECT category_name FROM category WHERE id_category = ii.src_category) src_category_name,
                full_name issued_by_name, (SELECT full_name FROM user u WHERE u.id_user = loaned_by) loaned_by_name 
                FROM item_issuance ii 
                LEFT JOIN category c ON ii.dst_category=c.id_category 
                LEFT JOIN department d ON c.id_department = d.id_department 
                LEFT JOIN user ON user.id_user = issued_by 
                WHERE id_issue = '$id' ";
    $rs = mysql_query($query);
    if ($rec = mysql_fetch_assoc($rs))
        $result = $rec;
    return $result;

}

function get_item_issue_by_item($id)
{
    $result = 0;
    $query  = "SELECT id_issue FROM item_issuance_list WHERE id_item = '$id' ORDER by id_issue DESC";
    $rs = mysql_query($query);
    if ($rec = mysql_fetch_row($rs))
        $result = $rec[0];
    return $result;

}

function get_item_issue_return($id)
{
    $result = array();
    $query  = "SELECT iir.*, date_format(iir.return_date, '%e-%b-%Y %H:%i') return_date, full_name received_by_name 
                FROM item_issuance_return iir 
                LEFT JOIN user ON user.id_user = received_by 
                WHERE id_issue = '$id' ";
    $rs = mysql_query($query);
    if ($rec = mysql_fetch_assoc($rs))
        $result = $rec;
    return $result;

}


function get_item_issuance_signatures($id)
{
    $result = array();
    $query  = "SELECT * FROM item_issuance_signature WHERE id_issue = '$id' ";
    $rs = mysql_query($query);
    if ($rec = mysql_fetch_assoc($rs))
        $result = $rec;
    return $result;

}

function get_item_issue_list($id)
{
    $result = array();
    $query  = "SELECT iil.*, asset_no, serial_no 
                FROM item_issuance_list iil 
                LEFT JOIN item i ON i.id_item = iil.id_item 
                WHERE id_issue = '$id' ";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_assoc($rs))
        $result[] = $rec;
    return $result;

}


function view_issue($issue)
{
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Select Items 
            <div class="foldtoggle"><a id="btn_select_item" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="select_item">
       <tr valign="top" align="left" class="normal">
        <td align="left" width=100>Category</td>
        <td align="left"><?php echo $issue['src_category_name']?></td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left">Item List</td>
        <td align="left" colspan=3>
            <ul id="item_list" style="padding-left: 0px"><?php echo $issue['item_list']?></ul>
        </td>
        </tr>
    </tbody>
    </table>
<script>
$('#btn_select_item').click(function (e){
    toggle_fold(this);
});
</script>
<?php
} // view_issue

function view_issued_to($issue)
{
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Issued-Out To
            <div class="foldtoggle"><a id="btn_item_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="item_issuance">    
      <tr valign="top">  
        <td align="left" width=100>Department</td>
        <td align="left"><?php echo $issue['department_name'];?> </td>
        </tr>
      <tr valign="top" class="alt">  
        <td align="left">Category</td>
        <td align="left" ><?php echo $issue['category_name'];?></td>
        </tr>  
      <tr valign="top">  
        <td align="left" width=100>Date/Time</td>
        <td align="left"><?php echo $issue['issue_date'];?> </td>
        </tr>
        </tbody>
    </table>
<script>
$('#btn_item_issuance').click(function (e){
    toggle_fold(this);
});
</script>
<?php
} // view_issued_to

function view_issue_signature($issue)
{
?>
    <table width="100%">
    <tr valign="middle">
        <th >&nbsp;</th>
        <th width="210" align="center">Issued By</th>
        <th width="210" align="center">Issued To
        <div class="foldtoggle"><a id="btn_signature" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="signature">
    <tr valign="top">
        <td>Name</td>
        <td><?php echo $issue['issued_by_name']?></td>
        <td><?php echo $issue['loaned_by_name']?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>Remarks</td>
        <td><?php echo $issue['issue_remark'];?></td>
        <td><?php echo $issue['loan_remark'];?></td>
    </tr>
    <tr valign="top">
        <td>Signatures</td>
        <td><img class="signature" src="<?php echo $issue['issue_sign']?>"></td>
        <td><img class="signature" src="<?php echo $issue['loan_sign']?>"></td>
    </tr>
    </tbody>
    </table>
<script>
$('#btn_signature').click(function (e){
    toggle_fold(this);
});
</script>
<?php
}

function view_issue_return($returns)
{
?>
    <table width="100%">
    <tr valign="middle">
        <th >&nbsp;</th>
        <th width="210" align="center">Returned By</th>
        <th width="210" align="center">Received By
        <div class="foldtoggle"><a id="btn_return_signature" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="return_signature">
    <tr valign="top">
        <td>Name</td>
        <td><?php echo $returns['returned_by']?></td>
        <td><?php echo $returns['received_by_name']?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>Remarks</td>
        <td><?php echo $returns['return_remark'];?></td>
        <td><?php echo $returns['receive_remark'];?></td>
    </tr>
    <tr valign="top">
        <td>Signatures</td>
        <td><img class="signature" src="<?php echo $returns['issue_sign']?>"></td>
        <td><img class="signature" src="<?php echo $returns['loan_sign']?>"></td>
    </tr>
    </tbody>
    </table>
<script>
$('#btn_return_signature').click(function (e){
    toggle_fold(this);
});
</script>
<?php
}

function issuance_notification(){
    $rs = null;
    $dtf = '%d-%b-%Y';
    global $configuration;
    $jn = date('j-n');
    if (isset($configuration['item']['issuance_notification_date']) && ($configuration['item']['issuance_notification_date'] == $jn)){
        $query  = "SELECT ii.*, d.department_name, c.category_name, date_format(ii.issue_date, '%e-%b-%Y %H:%i') issue_date,
                    (SELECT category_name FROM category WHERE id_category = ii.src_category) src_category_name,
                    full_name issued_by_name, (SELECT full_name FROM user u WHERE u.id_user = loaned_by) loaned_by_name 
                    FROM item_issuance ii 
                    LEFT JOIN category c ON ii.dst_category=c.id_category 
                    LEFT JOIN department d ON c.id_department = d.id_department 
                    LEFT JOIN user ON user.id_user = issued_by 
                    WHERE status = 'ISSUED' ";
        $rs = mysql_query($query);
    }
    return $rs;
}

function send_issuance_notification($data){
    global $transaction_prefix, $configuration;
    $config = $configuration['item'];
    
    if ($config['enable_notification'] != 'true') return false;
    
    $_dept = $data['id_department'];    
    $items = get_item_issue_list($data['id_issue']);
    $item_list = null;
    foreach ($items as $rec)
        $item_list .= "\t - $rec[asset_no] ($rec[serial_no]) \r\n";
        
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    $data['item_list'] = $item_list;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array();
        $email_rec = get_notification_emails($_dept, 0, 'item');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/item-issuance-notification.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Issuance Reminder';
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'item', 'email');
            process_notification($id_msg);
            writelog('send_issuance_notification(): '. $configuration['global']['system_email'] . '|' . $to . '|' . $message);
        }
    }
    
}

function count_issued_item($dept)
{
    $result = 0;
    $query  = "SELECT count(*) FROM item_issuance_list iil 
                LEFT JOIN item_issuance ii ON ii.id_issue = iil.id_issue 
                WHERE src_department = $dept ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    
    return $result;
}

function get_issued_items($dept = 0, $start = 0, $limit = 10, $orderby = 'asset_no', $sort = 'asc',  $searchby = null, $searchtext = null)
{
    $result = array();
    $query  = "SELECT iil.*, ii.*, d.department_name, c.category_name, date_format(ii.issue_date, '%e-%b-%Y') issue_date,
                (SELECT category_name FROM category WHERE id_category = src_category) src_category_name, item.*  
                FROM item_issuance_list iil 
                LEFT JOIN item_issuance ii ON ii.id_issue=iil.id_issue 
                LEFT JOIN category c ON ii.dst_category=c.id_category 
                LEFT JOIN department d ON ii.dst_department = d.id_department 
                LEFT JOIN item ON item.id_item = iil.id_item  
                WHERE src_department = '$dept' AND status = 'ISSUED'  
                ORDER BY $orderby $sort LIMIT $start,$limit ";
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    while ($rec = mysql_fetch_assoc($rs))
        $result[] = $rec;
    return $result;
}

?>
