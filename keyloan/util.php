<?php

function count_key_item($searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$result = 0;
	$query  = "SELECT count(DISTINCT kt.id_item) FROM key_item kt LEFT JOIN department ON kt.id_department = department.id_department";    
    if (!empty($searchtext))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " kt.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$rs = mysql_query($query);
    
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}	
	return $result;	
}

function get_key_item($orderby = 'serial_no', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$query  = "SELECT DISTINCT(kt.id_item), kt.*, department_name
               FROM key_item kt               
               LEFT JOIN department ON kt.id_department = department.id_department ";
    //print_r($query);  
    if (!empty($searchtext) && !empty($searchby))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " kt.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);    
	return $rs;
}

function get_key($id = 0)
{
    $result = array();
    $query  = "SELECT kt.*, department_name
               FROM key_item kt                
               LEFT JOIN department ON kt.id_department = department.id_department              
               WHERE kt.id_item = $id ";    
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);	
    return $result;
}

function get_key_items($ids)
{
    $result = array();
    $query  = 'SELECT ki.*, department_name
               FROM key_item ki              
               LEFT JOIN department ON ki.id_department = department.id_department 
               WHERE id_item IN (' . implode(',', $ids) . ')';
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs)
        while($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}

function get_user_info()
{
    $result = array();
    $query  = 'SELECT * from user ';
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs)
        while($rec = mysql_fetch_assoc($rs)){
            $result[$rec['id_user']]['full_name'] = $rec['full_name'];
            $result[$rec['id_user']]['contact'] = $rec['contact_no'];
		}
    return $result;
}

function get_id_key_by_serial($serial, $status = null)
{
    $result = array();
    $query  = "SELECT ki.*, department_name
               FROM key_item ki              
               LEFT JOIN department ON ki.id_department = department.id_department                 
               WHERE ki.serial_no = '$serial' ";
    if ($status != null)
        $query  .= " AND status = '$status' ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function save_key_item($id, $data)
{
    $dept = USERDEPT;
    $query = "REPLACE INTO key_item(id_item, serial_no, description, id_department)
              VALUES($id, '$data[serial_no]', '$data[description]', $dept)";
     mysql_query($query);    
	$affected  = mysql_affected_rows();
    if (($affected > 0) && ($id == 0))
        $id = mysql_insert_id();
	return $id;
}

function get_data_keyloan($id_loan = 0){
	$result = array();
    $query  = "SELECT kl.*, department_name,u.full_name,u.user_email,u.contact_no
               FROM key_loan kl       
			   LEFT JOIN user u ON u.id_user = kl.id_user  
               LEFT JOIN department ON u.id_department = department.id_department                             
               WHERE kl.id_loan = '$id_loan' ";    
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_serial_by_loan($id_loan = 0){
	$result = array();
    $query  = "SELECT kli.*, ki.serial_no
               FROM key_loan_item kli       
			   LEFT JOIN key_item ki ON ki.id_item = kli.id_item                            
               WHERE kli.id_loan = '$id' ";    
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function export_csv_key_item($path, $dept = 0)
{
    $dtf = '%d-%b-%Y %H:%i:%s';    
    $query  = "SELECT ki.id_item, ki.serial_no, ki.description, ki.status, d.department_name 
               FROM key_item ki             
               LEFT JOIN department d ON d.id_department = ki.id_department ";
    if ($dept > 0)
        $query .= " where ki.id_department = $dept ";

    $fp = fopen($path, 'w');
    $header  = 'ItemID,SerialNo,Description,Status,Department';
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

function get_id_item_by_serial($serial = null){
	$result = array();
    $query  = "SELECT * from key_item ki where serial_no ='$serial'";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function import_csv_key_item($path, $dept = 0){
    $row = 0;	
    $result = 0; // upload failed
	$cnt = 0;
    if (!empty($path) && file_exists($path)) {
		if (($handle = fopen($path, "r")) !== FALSE) {		
			$fp = file($path);	
			$departments = get_department_list();
            $my_dept = strtolower($departments[$dept]);	
			$serial_map = array();				
			while (($data = fgetcsv($handle, 1000, ",",'"')) !== FALSE) {
				if($row > 0){
					$serial_no = $data[0];
					$status = $data[1];
					$description = $data[2];
					$id_department = $dept;
					$tmp = get_id_item_by_serial($serial_no);
					
					if(!empty($tmp)){
						$serial_map[$serial_no] = $serial_no;
						$result = -4;
					}else{
						$query  = 'INSERT INTO key_item (serial_no, id_department, status, description) ';
						$query .= "VALUES ('$serial_no', '$id_department', '$status', '$description')";
						mysql_query($query);
						$cnt++;						
					}						
				}				
				$row++;
			}			
			if ($result == 0) $result = $cnt;
		}
	}else{
		$result = -2;
	}
    return $result;
}

function get_key_serials($id, $key_serial = false)
{
    $result = array();
    $query  = "SELECT *  
               FROM key_item kt 
               WHERE kt.id_item =  $id 
               ORDER BY serial_no ASC";
    $rs = mysql_query($query);
    if ($rs)
        while ($rec = mysql_fetch_assoc($rs))
            if ($key_serial)
                $result[$rec['serial_no']] = $rec;
            else
                $result[] = $rec;
    return $result;
}

?>