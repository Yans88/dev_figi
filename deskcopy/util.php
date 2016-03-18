<?php

function is_isbn_valid($n)
{
    $check = 0;
    for ($i = 0; $i < 9; $i++) $check += (10 - $i) * substr($n, $i, 1);
    $t = substr($n, 9, 1); // tenth digit (aka checksum or check digit)
    $check += ($t == 'x' || $t == 'X') ? 10 : $t;
    return $check % 11 == 0;
}

function is_isbn_13_valid($n)
{
    $check = 0;
    for ($i = 0; $i < 13; $i+=2) $check += substr($n, $i, 1);
    for ($i = 1; $i < 12; $i+=2) $check += 3 * substr($n, $i, 1);
    return $check % 10 == 0;
}

function count_deskcopy_title($searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$result = 0;
	$query  = "SELECT count(DISTINCT dct.id_title)  
                FROM deskcopy_title dct 
                 LEFT JOIN department ON dct.id_department = department.id_department 
                 LEFT JOIN deskcopy_publisher dcp ON dcp.id_publisher=dct.id_publisher 
                 LEFT JOIN deskcopy_author dca ON dct.id_author = dca.id_author            
                 LEFT JOIN deskcopy_item dci ON dct.id_title = dci.id_title ";           
    if (!empty($searchtext))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " dct.id_department = $dept ";
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

function get_deskcopy_titles($orderby = 'isbn', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$query  = "SELECT DISTINCT(dct.id_title), dct.*, department_name, author_name, stock, publisher_name    
               FROM deskcopy_title dct 
               LEFT JOIN deskcopy_author dca ON dca.id_author=dct.id_author 
               LEFT JOIN deskcopy_publisher dcp ON dcp.id_publisher=dct.id_publisher 
               LEFT JOIN department ON dct.id_department = department.id_department  
               LEFT JOIN deskcopy_item dci ON dct.id_title = dci.id_title 
               LEFT JOIN deskcopy_stock dcs ON dcs.id_title = dct.id_title ";
    if (!empty($searchtext) && !empty($searchby))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " dct.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
    //echo mysql_error().$query;
    
	return $rs;
}

function get_deskcopy($id = 0)
{
    $result = array();
    $query  = "SELECT dct.*, department_name, author_name, stock, publisher_name    
               FROM deskcopy_title dct 
               LEFT JOIN deskcopy_author dca ON dca.id_author=dct.id_author 
               LEFT JOIN deskcopy_publisher dcp ON dcp.id_publisher=dct.id_publisher 
               LEFT JOIN department ON dct.id_department = department.id_department  
               LEFT JOIN deskcopy_stock dcs ON dcs.id_title = dct.id_title 
               WHERE dct.id_title = $id ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_deskcopy_title_by_isbn($isbn)
{
    $result = array();
    $query  = "SELECT dct.*, department_name, author_name, stock, publisher_name    
               FROM deskcopy_title dct 
               LEFT JOIN deskcopy_author dca ON dca.id_author=dct.id_author 
               LEFT JOIN deskcopy_publisher dcp ON dcp.id_publisher=dct.id_publisher 
               LEFT JOIN department ON dct.id_department = department.id_department  
               LEFT JOIN deskcopy_stock dcs ON dcs.id_title = dct.id_title 
               WHERE isbn = '$isbn' ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_deskcopy_title_by_serial($serial, $status = null)
{
    $result = array();
    $query  = "SELECT dci.*, dct.*, department_name, author_name, stock, publisher_name    
               FROM deskcopy_item dci 
               LEFT JOIN deskcopy_title dct ON dct.id_title=dci.id_title 
               LEFT JOIN deskcopy_author dca ON dca.id_author=dct.id_author 
               LEFT JOIN deskcopy_publisher dcp ON dcp.id_publisher=dct.id_publisher 
               LEFT JOIN department ON dct.id_department = department.id_department  
               LEFT JOIN deskcopy_stock dcs ON dcs.id_title = dct.id_title 
               WHERE dci.serial_no = '$serial' ";
    if ($status != null)
        $query  .= " AND status = '$status' ";
    $rs = mysql_query($query);
    echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function save_deskcopy_title($id, $data)
{
    $dept = USERDEPT;
    $query = "REPLACE INTO deskcopy_title(id_title, isbn, title, description, id_department)
              VALUES($id, '$data[isbn]', '$data[title]', '$data[description]', $dept)";
    mysql_query($query);
    //echo mysql_error().$query;
    $affected  = mysql_affected_rows();
    if (($affected > 0) && ($id == 0))
        $id = mysql_insert_id();
    if ($id > 0) {        
        // manage author
        $id_author = get_author_id($data['author']);
        $query = "UPDATE deskcopy_title SET id_author = $id_author WHERE id_title = $id";
        mysql_query($query);     
        // manage publisher
        $id_publisher = get_publisher_id($data['publisher']);
        $query = "UPDATE deskcopy_title SET id_publisher = $id_publisher WHERE id_title = $id";
        mysql_query($query);     
        // prepare stock if not avaliable
        $rs = mysql_query('SELECT COUNT(id_title) FROM deskcopy_stock WHERE id_title = ' . $id);
        $rec = mysql_fetch_row($rs);
        if ($rec[0] == 0)
            mysql_query('INSERT INTO deskcopy_stock(id_title, stock)  VALUES (' . $id . ', 0)');
        // manage items (serial_no)
        if (!empty($data['items'])){
            $items = explode(',', $data['items']);
            // get current items 
            $curr_serials = get_deskcopy_serials($id, true);
            $values = array();
            foreach ($items as $serial_no){
                $id_item  = 0;
                $status   = 'Available for Loan';
                $last_use = date('Y-m-d H:i:s');
                if (isset($curr_serials[$serial_no])){
                    $id_item  = $curr_serials[$serial_no]['id_item'];
                    $status   = $curr_serials[$serial_no]['status'];
                    $last_use = $curr_serials[$serial_no]['last_usage'];
                    $curr_serials[$serial_no] = null;
                }
                $values[] = "($id_item, $id, '$serial_no', '$status', '$last_use')";
            }
            if (count($values) > 0){
                $query  = 'REPLACE INTO deskcopy_item (id_item, id_title, serial_no, status, last_usage) VALUES ';
                $query .= implode(',', $values);
                mysql_query($query);
            }
            foreach ($curr_serials as $rec)
                if ($rec != null)
                    mysql_query('DELETE FROM deskcopy_item WHERE id_item=' . $rec['id_item']);
                    
            // count number of serials and update table title
            $rs = mysql_query('SELECT COUNT(*) FROM deskcopy_item WHERE id_title=' . $id);
            $rec = mysql_fetch_row($rs);
            $total = $rec[0];
            mysql_query('UPDATE deskcopy_title SET number_of_items = '. $total.' WHERE id_title=' . $id);
            // update stock, check on loan status
            $rs = mysql_query('SELECT COUNT(*) FROM deskcopy_item WHERE id_title = ' . $id . ' AND status = "On Loan"');
            $rec = mysql_fetch_row($rs);
            mysql_query('UPDATE deskcopy_stock SET stock = '. ($total-$rec[0]) .' WHERE id_title = ' . $id);
            
        }
    }
    return $id;
}

function get_author_id($author = '')
{
    $id = 0;
    $query = "SELECT id_author FROM deskcopy_author WHERE author_name = '$author'";
    $rs = mysql_query($query);
    if ($rs){
        if (mysql_num_rows($rs) > 0){
            $rec = mysql_fetch_row($rs);
            $id = $rec[0];
        } 
        else { // author_name not found, insert as new author
            $query = "INSERT INTO deskcopy_author(author_name) VALUES('$author')";
            mysql_query($query);
            if (mysql_affected_rows() > 0)
                $id = mysql_insert_id();
        }
    }
    return $id;
}

function get_publisher_id($publisher = '')
{
    $id = 0;
    $query = "SELECT id_publisher FROM deskcopy_publisher WHERE publisher_name = '$publisher'";
    $rs = mysql_query($query);
    if ($rs){
        if (mysql_num_rows($rs) > 0){
            $rec = mysql_fetch_row($rs);
            $id = $rec[0];
        } 
        else { // publisher_name not found, insert as new publisher
            $query = "INSERT INTO deskcopy_publisher(publisher_name) VALUES('$publisher')";
            mysql_query($query);
            if (mysql_affected_rows() > 0)
                $id = mysql_insert_id();
        }
    }
    return $id;
}

function get_author_list($swap = false, $lowercase = false)
{
    $result = array();
    $query = "SELECT id_author, author_name FROM author ";
    $rs = mysql_query($query);
    if ($rs){
        while ($rec = mysql_fetch_row($rs))
            if ($swap){
                if ($lowercase)
                    $result[strtolower($rec[1])] = $rec[0];
                else
                    $result[$rec[1]] = $rec[0];
            } else {
                $result[$rec[0]] = $rec[1];
            }
    }
    return $result;
}

// id_item,serial,isbn,title,description,author,publisher,status,department
function export_csv_deskcopy_item($path, $dept = 0)
{
    $dtf = '%d-%b-%Y %H:%i:%s';    
    $query  = "SELECT dci.id_item, serial_no, isbn, title, description, author_name, publisher_name, status, department_name 
               FROM deskcopy_item dci 
               LEFT JOIN deskcopy_title dct ON dct.id_title = dci.id_title 
               LEFT JOIN deskcopy_author dca ON dca.id_author = dct.id_author 
               LEFT JOIN deskcopy_publisher dcp ON dcp.id_publisher=dct.id_publisher 
               LEFT JOIN department d ON d.id_department = dct.id_department 
               WHERE dct.id_title IS NOT NULL ";
    if ($dept > 0)
        $query .= " AND dct.id_department = $dept ";

    $fp = fopen($path, 'w');
    $header  = 'ItemID,SerialNo,ISBN,Title,Description,Author,Publisher,Status,Department';
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

function import_csv_deskcopy_item($path, $dept = 0)
{
    $row = 0;
    $result = 0; // upload failed
    if (!empty($path) && file_exists($path)) {
        if (($fp = fopen($path, 'r')) !== FALSE){
            $cols = fgetcsv($fp, 512, ',');
// id_item,serial,isbn,title,description,author,publisher,status,department
            if (count($cols) >= 9){ // 
                $departments = get_department_list();
                $my_dept = strtolower($departments[$dept]);
                $isbn_map = array();
                while ($cols = fgetcsv($fp, 512, ',')){
                    $row++;
                    $deptname = strtolower($cols[8]); // department
                    if ($my_dept != $deptname) continue;
                    $aid = get_author_id($cols[5]);
                    $pid = get_publisher_id($cols[6]);
                    $isbn = $cols[2];
                    $tid = 0;
                    if (isset($isbn_map[$isbn])) $tid = $isbn_map[$isbn];
                    else {
                        $tmp = get_deskcopy_title_by_isbn($isbn);
                        if (!empty($tmp['id_title'])) $tid = $tmp['id_title'];
                        else $tid = add_deskcopy_title($isbn, $cols[3], $cols[4], $aid, $pid, $dept);
                        if ($tid > 0)
                            $isbn_map[$isbn] = $tid;
                    }
                    if ($tid > 0){
                        $query  = 'INSERT INTO deskcopy_item (id_title, serial_no, status) ';
                        $query .= "VALUES ($tid, '$cols[1]', '$cols[7]')";
                        mysql_query($query);
                        if (mysql_affected_rows() == 1){
                            $id_item = mysql_insert_id();
                            $result++;
                            // update number of items in title
                            mysql_query('UPDATE deskcopy_title SET number_of_items = number_of_items + 1 WHERE id_title=' . $tid);
                            // update stock
                            mysql_query('UPDATE deskcopy_stock SET stock = stock + 1 WHERE id_title=' . $tid);

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

function add_deskcopy_title($isbn, $title, $description, $author_id, $publisher_id, $dept)
{
    $result = 0;
    $query = "INSERT INTO deskcopy_title(isbn, title, description, id_author, id_publisher, id_department)
              VALUES('$isbn', '$title', '$description', $author_id, $publisher_id, $dept)";
    mysql_query($query);
    if (mysql_affected_rows() > 0){
        $result = mysql_insert_id();
        // prepare stock
        mysql_query('INSERT INTO deskcopy_stock(id_title, stock)  VALUES (' . $result . ', 0)');
    }
    return $result;
}

function get_deskcopy_serials($id, $key_serial = false)
{
    $result = array();
    $query  = "SELECT *  
               FROM deskcopy_item dci 
               WHERE dci.id_title =  $id 
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

function get_deskcopy_items($ids)
{
    $result = array();
    $query  = 'SELECT dci.*, department_name, author_name, dct.*    
               FROM deskcopy_item dci 
               LEFT JOIN deskcopy_title dct ON dct.id_title = dci.id_title 
               LEFT JOIN deskcopy_author dca ON dca.id_author = dct.id_author 
               LEFT JOIN department ON dct.id_department = department.id_department 
               WHERE id_item IN (' . implode(',', $ids) . ')';
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs)
        while($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}

function count_deskcopy_item($dept = 0)
{
    $wheres = array();
	$result = 0;
	$query  = "SELECT count(id_item)  
                FROM deskcopy_item dci 
                LEFT JOIN deskcopy_title dct ON dct.id_title = dci.id_title
                WHERE dct.id_title IS NOT NULL ";
    if ($dept > 0)
        $query .= " dct.id_department = $dept ";
	$rs = mysql_query($query);
    
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}


?>