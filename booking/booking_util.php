<?php

function booking_subject_rows()
{
	$result = array();
	$query = "SELECT * FROM booking_subject ORDER BY subject_name ";
    $rs = mysql_query($query);
    if ($rs){
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    }
	return $result;
}

function booking_subject_list($reverse = false)
{
	$result = array();
	$rows = booking_subject_rows();
	foreach($rows as $rec)
		if ($reverse)
			$result[strtolower($rec['subject_name'])] = $rec['id_subject'];
		else
			$result[$rec['id_subject']] = $rec['subject_name'];
	return $result;
}

/*
function get_periods($fid = 0)
{
    $result = array();
    $query  = "SELECT bp.*, time_format(time_start, '%H:%i') as time_start, 
				time_format(time_end, '%H:%i') as time_end  
				FROM booking_period bp 
				WHERE id_facility = $fid 
				ORDER BY time_start ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    
    return $result;
}
*/

function book_periods($id = 0)
{
	$booked = array();
	$query = "SELECT blp.*, DATE_FORMAT(FROM_UNIXTIME(booked_date), '%d-%M-%Y') book_date,
				time_start, DATE_FORMAT(time_start, '%H:%i') AS start_time, 
				time_end, DATE_FORMAT(time_end, '%H:%i') AS end_time, subject_name AS subject
				FROM booking_list_period blp
				LEFT JOIN facility_period_timesheet fpt ON fpt.id_time = blp.id_time 
				LEFT JOIN booking_subject bs ON bs.id_subject = blp.id_subject  
                WHERE blp.id_book = $id 
				ORDER BY booked_date ASC, start_time ASC";
    $rs = mysql_query($query);
	//error_log(mysql_error().$query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['booked_date']]["'$rec[id_time]'"] = $rec;
    }

	return $booked;
}


function book_equipments($id_book = 0)
{
    $result = array();
    $query  = "SELECT ble.*, c.category_name name 
				FROM booking_list_equipment ble 
				LEFT JOIN facility_equipment fe ON ble.id_equipment = fe.id_equipment  
				LEFT JOIN category c ON c.id_category = fe.id_category 
				WHERE ble.id_book = $id_book 
				ORDER BY category_name ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    
    return $result;
}

function book_save($post)
{
	$ok = false;
	$purpose = mysql_real_escape_string($post['purpose']);
	$remark = mysql_real_escape_string($post['remark']);
	$recurring = $post['recurring'];
	$id_facility = $post['id_facility'];
	$id_subject = $post['id_subject'];
	$id_user = USERID;
	$dt_instance = 0;
	$status = 'BOOK';
	$is_recurring = (strtolower($recurring) != 'none');
	$term = period_term_get(0, $id_facility, 1);
	$id_term = (!empty($term['id_term'])) ? $term['id_term']: 0;
	$recurring_times = 1;
	if ($is_recurring)
		$recurring_times = $_POST['many'];
	
	//error_log(serialize($post));	
	$query = "INSERT INTO booking_list(book_date, id_user, id_facility, recurring, purpose, remark, status, id_subject, id_term, recurring_times)
                VALUE (UNIX_TIMESTAMP(), $id_user, $id_facility, '$recurring', '$purpose', '$remark', '$status', $id_subject, $id_term, $recurring_times)"; 
    mysql_query($query);
	//error_log(mysql_error().$query);
    if (mysql_affected_rows()>0){
        $id_book = mysql_insert_id();
		$ok = $id_book;
		//keep periods
		$subjects = isset($_POST['subjects']) ? $_POST['subjects'] : array();
		$purposes = isset($_POST['purposes']) ? $_POST['purposes'] : array();
		$remarks = isset($_POST['remarks']) ? $_POST['remarks'] : array();
		$periods = unserialize($post['periods']);
		foreach($periods as $line){
			list($dt, $id_time) = explode('-', $line);
			if (empty($subjects[$line])) $tp_subject = $id_subject;
			else $tp_subject = $subjects[$line];
			if (empty($purposes[$line])) $tp_purpose = $purpose;
			else $tp_purpose = mysql_real_escape_string($purposes[$line]);
			if (empty($remarks[$line])) $tp_remark = $remark;
			else $tp_remark = mysql_real_escape_string($remarks[$line]);
			 
			$query = "INSERT INTO booking_list_period(id_book, booked_date, id_time, id_subject, purpose, remark, is_instance) 
						VALUE($id_book, '$dt', $id_time, $tp_subject, '$tp_purpose', '$tp_remark', 0)";
			//error_log($query);
			if (mysql_query($query)){
			
				// if it's recurring, duplicate period with different date as recurring type selected
				if ($is_recurring && $recurring_times>1){
					$valid_from = !empty($term['valid_from_sec']) ? $term['valid_from_sec'] : 0;
					$valid_to = !empty($term['valid_to_sec']) ? $term['valid_to_sec'] : 0;
					$oneday = 60 * 60 * 24; // s*m*d
					$rdt = $dt; // first booked date
					for ($i=1; $i<$recurring_times; $i++){
						if ('weekly'==$recurring) $rdt += $oneday*7;
						elseif ('fortnightly'==$recurring) $rdt += $oneday*14;
						elseif ('monthly'==$recurring) {
							// get same date for next month
							$rdt = mktime(0, 0, 0, date('n', $rdt)+1, date('j', $rdt), date('Y', $rdt));
						}
						//check rdt if out of valid range date
						//error_log("term: $id_term, vf: $valid_from, dt: $rdt, vt: $valid_to\r\n");
						if ($rdt >= $valid_from && $rdt <= $valid_to) {
							$query = "INSERT INTO booking_list_period(id_book, booked_date, id_time, id_subject, purpose, remark, is_instance) 
										VALUE($id_book, '$rdt', $id_time, $tp_subject, '$tp_purpose', '$tp_remark', 1)";
							mysql_query($query);
							//error_log(mysql_error().$query);
						}
					}
				}
			}
			//error_log(mysql_error().$query);
		}
		
		// keep equipment
		if (!empty($post['use_qty'])){
			$equipments = $post['use_qty'];
			if (!empty($equipments) && is_array($equipments)){
				foreach($equipments as $id_equipment => $quantity){
					$query = "INSERT INTO booking_list_equipment(id_book, id_equipment, quantity) VALUE($id_book, $id_equipment, $quantity)";
					mysql_query($query);
					//error_log(mysql_error().$query);
				}
			}
		}
		// keep attachment if any
		attachment_save($id_book);
		
		
	}
 	return $ok;
}

function book_save_xml($post)
{
	$ok = false;
	$purpose = mysql_real_escape_string($post['purpose']);
	$remark = mysql_real_escape_string($post['remark']);
	$recurring = strtolower($post['recurring']);
	$id_facility = $post['id_facility'];
	$id_subject = $post['id_subject'];
	$id_user = USERID;
	$dt_instance = 0;
	$status = 'BOOK';
	$is_recurring = ($recurring != 'none');
	$term = period_term_get(0, $id_facility, 1);
	$id_term = (!empty($term['id_term'])) ? $term['id_term']: 0;
	$recurring_times = 1;
	if ($is_recurring)
		$recurring_times = $post['many'];
	
	//error_log(serialize($post));	
	$query = "INSERT INTO booking_list(book_date, id_user, id_facility, recurring, purpose, remark, status, id_subject, id_term, recurring_times)
                VALUE (UNIX_TIMESTAMP(), $id_user, $id_facility, '$recurring', '$purpose', '$remark', '$status', $id_subject, $id_term, $recurring_times)"; 
    mysql_query($query);
	//error_log(mysql_error().$query);
    if (mysql_affected_rows()>0){
        $id_book = mysql_insert_id();
		$ok = $id_book;
		//keep periods
		$subjects = isset($post['subjects']) ? $post['subjects'] : array();
		$purposes = isset($post['purposes']) ? $post['purposes'] : array();
		$remarks = isset($post['remarks']) ? $post['remarks'] : array();
		$periods = unserialize($post['periods']);
		foreach($periods as $line){
			list($dt, $id_time) = explode('-', $line);
			if (empty($subjects[$line])) $tp_subject = $id_subject;
			else $tp_subject = $subjects[$line];
			if (empty($purposes[$line])) $tp_purpose = $purpose;
			else $tp_purpose = mysql_real_escape_string($purposes[$line]);
			if (empty($remarks[$line])) $tp_remark = $remark;
			else $tp_remark = mysql_real_escape_string($remarks[$line]);
			 
			$query = "INSERT INTO booking_list_period(id_book, booked_date, id_time, id_subject, purpose, remark, is_instance) 
						VALUE($id_book, '$dt', $id_time, $tp_subject, '$tp_purpose', '$tp_remark', 0)";
			//error_log($query);
			if (mysql_query($query)){
			
				// if it's recurring, duplicate period with different date as recurring type selected
				if ($is_recurring && $recurring_times>1){
					$valid_from = !empty($term['valid_from_sec']) ? $term['valid_from_sec'] : 0;
					$valid_to = !empty($term['valid_to_sec']) ? $term['valid_to_sec'] : 0;
					$oneday = 60 * 60 * 24; // s*m*d
					$rdt = $dt; // first booked date
					for ($i=1; $i<$recurring_times; $i++){
						if ('weekly'==$recurring) $rdt += $oneday*7;
						elseif ('fortnightly'==$recurring) $rdt += $oneday*14;
						elseif ('monthly'==$recurring) {
							// get same date for next month
							$rdt = mktime(0, 0, 0, date('n', $rdt)+1, date('j', $rdt), date('Y', $rdt));
						}
						//check rdt if out of valid range date
						error_log("term: $id_term, vf: $valid_from, dt: $rdt, vt: $valid_to\r\n");
						if ($rdt >= $valid_from && $rdt <= $valid_to) {
							$query = "INSERT INTO booking_list_period(id_book, booked_date, id_time, id_subject, purpose, remark, is_instance) 
										VALUE($id_book, '$rdt', $id_time, $tp_subject, '$tp_purpose', '$tp_remark', 1)";
							mysql_query($query);
							//error_log(mysql_error().$query);
						}
					}
				}
			}
			//error_log(mysql_error().$query);
		}
		
		// keep equipment
		if (!empty($post['use_qty'])){
			$equipments = $post['use_qty'];
			if (!empty($equipments) && is_array($equipments)){
				foreach($equipments as $id_equipment => $quantity){
					$query = "INSERT INTO booking_list_equipment(id_book, id_equipment, quantity) VALUE($id_book, $id_equipment, $quantity)";
					mysql_query($query);
					//error_log(mysql_error().$query);
				}
			}
		}
		// keep attachment if any
		attachment_save($id_book);
		
		
	}
 	return $ok;
}

function book_remove($id_book = 0)
{
	// remove from booking_list_attachment, booking_list_equipment, booking_list_period, booking_list
	$query = 'DELETE FROM booking_list_attachment WHERE id_book ='.$id_book;
	mysql_query($query);
	$query = 'DELETE FROM booking_list_equipment WHERE id_book ='.$id_book;
	mysql_query($query);
	$query = 'DELETE FROM booking_list_period WHERE id_book ='.$id_book;
	mysql_query($query);
	$query = 'DELETE FROM booking_list WHERE id_book ='.$id_book;
	mysql_query($query);
	return mysql_affected_rows();
	
}

function book_remove_by_period($id_book = 0, $booked_date = 0, $id_time = 0)
{
	$query = "SELECT * FROM booking_list_period WHERE id_book = $id_book";
	$mysql = mysql_query($query);
	$row = mysql_num_rows($mysql);
	error_log($query);
	if($row <= 1){
	// remove from booking_list_attachment, booking_list_equipment, booking_list_period, booking_list
		$query = 'DELETE FROM booking_list WHERE id_book ='.$id_book;
		mysql_query($query);
	} else {
		
		$query = 'DELETE FROM booking_list_attachment WHERE id_book ='.$id_book;
		mysql_query($query);
		$query = 'DELETE FROM booking_list_equipment WHERE id_book ='.$id_book;
		mysql_query($query);
		$query = 'DELETE FROM booking_list_period WHERE id_book ='.$id_book.' AND booked_date= '.$booked_date.' AND id_time = '.$id_time;
		mysql_query($query);
	}
	
	return mysql_affected_rows();
	
}

function attachment_save($id)
{
	$total = 0;
    if (isset($_FILES['attachment']) && count($_FILES['attachment']) > 0){
        for ($i = 0; $i < count($_FILES['attachment']['name']); $i++){
            $filesize = $_FILES['attachment']['size'][$i];
            $filename = $_FILES['attachment']['name'][$i];
            $filetemp = $_FILES['attachment']['tmp_name'][$i];
            $errorcode = $_FILES['attachment']['error'][$i];

            if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                $data = base64_encode(file_get_contents($filetemp));
                $query  = "INSERT INTO booking_list_attachment(id_book, filename, data, description) ";
                $query .= "VALUES('$id', '$filename', '$data', '')";
                mysql_query($query);
				if (mysql_affected_rows()>0) $total++;
                //echo mysql_error().$query;
            }
        }
    }
	return $total;
}


function book_info($id = 0)
{
	$data = array();
	$dtfmt = "'%d-%M-%Y %h:%s'";
	if ($id > 0){
        $query = "SELECT bl.*, u.full_name AS user_name, bs.subject_name,  
        			DATE_FORMAT(FROM_UNIXTIME( book_date ), $dtfmt) AS booking_date,
        			location_name AS facility_name, f.description description   
                    FROM booking_list bl 
					LEFT JOIN facility f ON f.id_facility = bl.id_facility 
					LEFT JOIN location l ON l.id_location = f.id_location 
					LEFT JOIN user u ON u.id_user = bl.id_user 
					LEFT JOIN booking_subject bs ON bl.id_subject = bs.id_subject  
                    WHERE bl.id_book = $id";                    
		$rs = mysql_query($query);
		if ($rs){
            $data = mysql_fetch_assoc($rs);
        }
	}
	return $data;
}

function book_attachment($id = 0)
{
	$result = array();
	if($id > 0){
		$query = "SELECT * FROM  booking_list_attachment where id_book = $id";
		$rs = mysql_query($query);
		while($rec = mysql_fetch_array($rs)){
			$result[] = $rec;
		}
	}
	return $result;
}

function get_booked($fid, $dts, $dte = null)
{
	$booked = array();
	$query = "SELECT bl.*, blp.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM booking_list_period blp
                LEFT JOIN booking_list bl ON blp.id_book = bl.id_book 
                LEFT JOIN user ON user.id_user = bl.id_user 
                WHERE booked_date >= $dts AND booked_date <= $dte AND id_facility = '$fid'";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['booked_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	return $booked;
}





function booked_date_rows($filter=array())
{
	$booked = array();
	$wheres = array();
	if (empty($filter['start_date']))
		$filter['start_date'] = mktime(0, 0, 0, date('n'), 1, $date('y'));
	if (empty($filter['end_date'])){
		$last_dom = date('t', $end_of_month);
		$filter['end_date'] = mktime(23, 59, 59, date('n'), $last_dom, date('Y'));
	}
	$query = "SELECT bl.*, blp.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM booking_list_period blp
                LEFT JOIN booking_list bl ON blp.id_book = bl.id_book 
                LEFT JOIN user ON user.id_user = bl.id_user 
                WHERE booked_date >= $filter[start_date] AND booked_date <= $filter[end_date] ";
	if (!empty($filter['id_user'])) $wheres[] = ' bl.id_user = '.$filter['id_user'];
	if (!empty($filter['id_subject'])) $wheres[] = ' bl.id_subject = '.$filter['id_subject'];
	if (!empty($filter['id_facility'])) $wheres[] = ' bl.id_facility = '.$filter['id_facility'];
	if (!empty($wheres)) $query .= ' AND '.implode(' AND ', $wheres);
	$query .= ' ORDER BY booked_date ASC, blp.id_time ASC';
    $rs = mysql_query($query);
	//error_log( mysql_error().$query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['booked_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	return $booked;
}

function booking_count($filter = array())
{
	$booked = 0;
	$wheres = array();
	$query = "SELECT COUNT(*) AS total   
				FROM booking_list bl 
				LEFT JOIN booking_subject bs ON bl.id_subject=bs.id_subject 
				LEFT JOIN facility f ON f.id_facility=bl.id_facility 
                LEFT JOIN user ON user.id_user = bl.id_user ";
	if (!empty($filter['id_user'])) $wheres[] = ' bl.id_user = '.$filter['id_user'];
	if (!empty($filter['id_subject'])) $wheres[] = ' bl.id_subject = '.$filter['id_subject'];
	if (!empty($filter['id_facility'])) $wheres[] = ' bl.id_facility = '.$filter['id_facility'];
	if (!empty($filter['start']) && !empty($filter['end']))
		$wheres[] = ' bl.book_date >= '.$filter['start'].' AND bl.book_date <= '.$filter['end'];
	if (!empty($wheres)) $query .= ' WHERE '.implode(' AND ', $wheres);
    $rs = mysql_query($query);
	//echo(mysql_error().$query);
    if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_assoc($rs);
		$booked = $rec['total'];
    }
	return $booked;
}

function booking_rows($filter = array(), $start = 0, $limit = 10,  $order = array('book_date desc'))
{
	$booked = array();
	$wheres = array();
	$query = "SELECT bl.*, user.full_name booked_by, subject_name AS subject, l.location_name AS facility_name,
				date_format(from_unixtime(book_date), '%d-%M-%Y %H:%i') book_date_display, 
				(SELECT CONCAT(DATE_FORMAT(FROM_UNIXTIME(booked_date), '%d-%M-%Y '), SUBSTR(fpt.time_start, 1, 5)) 
					FROM booking_list_period blp 
					LEFT JOIN facility_period_timesheet fpt ON fpt.id_time = blp.id_time 
				 	WHERE blp.id_book = bl.id_book  AND blp.is_instance = 0 
					ORDER BY booked_date ASC, fpt.time_start ASC 
					LIMIT 1) AS first_booked_period
				FROM booking_list bl 
				LEFT JOIN booking_subject bs ON bl.id_subject=bs.id_subject 
				LEFT JOIN facility f ON f.id_facility=bl.id_facility 
				LEFT JOIN location l ON f.id_location=l.id_location 
                LEFT JOIN user ON user.id_user = bl.id_user ";
	if (!empty($filter['id_user'])) $wheres[] = ' bl.id_user = '.$filter['id_user'];
	if (!empty($filter['id_subject'])) $wheres[] = ' bl.id_subject = '.$filter['id_subject'];
	if (!empty($filter['id_facility'])) $wheres[] = ' bl.id_facility = '.$filter['id_facility'];
	if (!empty($filter['start']) && !empty($filter['end']))
		$wheres[] = ' bl.book_date >= '.$filter['start'].' AND bl.book_date <= '.$filter['end'];

	if (!empty($wheres)) $query .= ' WHERE '.implode(' AND ', $wheres);
	if (!empty($order)) $query .= ' ORDER BY ' . implode(', ', $order);
	$query .= " LIMIT $start, $limit ";
    $rs = mysql_query($query);
	//echo(mysql_error().$query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[] = $rec;
		//print_r($rec);
    }
	return $booked;
}

function book_earliest_period($id = 0)
{
	$book = null;
	$booked = book_periods($id);
	if (count($booked)>0){
		$booked = array_pop($booked);
		$book = array_pop($booked);
	}
	return $book;
}

/*
	bookable facility
*/
function bookable_facility_count()
{
    $result = 0;
    $query  = "SELECT count(id_facility) FROM facility";
    $rs = mysql_query($query);
    if ($rs){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function bookable_facility_rows($start = 0, $limit = 10)
{
    $result = array();
    $query  = "SELECT f.*, l.location_name facility_name, fpt.term term_name, fpt.active   
                FROM facility f 
				LEFT JOIN facility_period_map fpm ON f.id_facility = fpm.id_facility 
                LEFT JOIN location l ON f.id_location = l.id_location 
                LEFT JOIN facility_period_term fpt ON fpt.id_term = fpm.id_term 
                ORDER BY l.location_name ASC ";
	if ($limit > 0)
    	$query .= " LIMIT $start, $limit ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    return $result;
}


function bookable_facility_list()
{
    $result = array();
	$rows = bookable_facility_rows(0, 0);
	foreach ($rows as $rec)
		$result[$rec['id_facility']] = $rec['facility_name'];
    return $result;
}

function bookable_facility_info($id = 0)
{
    $result = array();
    $query  = "SELECT f.*, l.location_name facility_name 
                FROM facility f 
				LEFT JOIN facility_period_map fpm ON f.id_facility = fpm.id_facility 
                LEFT JOIN location l ON f.id_location = l.id_location  
                LEFT JOIN facility_period_term fpt ON fpm.id_term = fpt.id_term 
                WHERE f.id_facility = '$id'
				LIMIT 1";
    $rs = mysql_query($query);
    if ($rs)
        $result = mysql_fetch_assoc($rs);
    return $result;
}

/*
period term and timesheet
*/
function period_term_count()
{
    $result = 0;
    $query  = "SELECT count(*) FROM facility_period_term ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function period_term_rows($start = 0, $limit = 10)
{
    $result = array();
	$fmt = DATE_FORMAT_MYSQL;
    $query  = "SELECT *, u.full_name AS modified_by_name,  
                date_format(valid_from, '$fmt') AS valid_from_display, 
                date_format(valid_to, '$fmt') AS valid_to_display, 
                date_format(modified_on, '$fmt %H:%i') AS modified_on_display  
                FROM facility_period_term fpt 
                LEFT JOIN user u ON fpt.modified_by = u.id_user 
                ORDER BY valid_to  DESC
                LIMIT $start, $limit ";
    $rs = mysql_query($query);
	//error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    return $result;
}

function period_term_list($with_status = false)
{
    $result = array();
    $query  = "SELECT id_term, term, active  FROM facility_period_term ORDER BY term ASC";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			if ($with_status)
				$result[$rec['id_term']] = $rec['term']. (($rec['active']>0) ? ' ( Active )' : ' ( Inactive )');
			else
				$result[$rec['id_term']] = $rec['term'];
    }
    return $result;
}

function facility_period_term_rows($id_facility = 0)
{
    $result = array();
	$fmt = DATE_FORMAT_MYSQL;
	$query = "SELECT fpt.* , u.full_name AS modified_by_name,  
				date_format(valid_from, '$fmt') AS valid_from_display, 
				date_format(valid_to, '$fmt') AS valid_to_display, 
				date_format(modified_on, '$fmt %H:%i') AS modified_on_display  
				FROM facility_period_map map 
				LEFT JOIN facility_period_term fpt ON fpt.id_term = map.id_term 
				LEFT JOIN user u ON fpt.modified_by = u.id_user 
				WHERE map.id_facility = $id_facility";

    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    return $result;
}

function facility_period_term_list($id_facility = 0)
{
	$result = array();
	$rows = facility_period_term_rows($id_facility);
	foreach($rows as $rec){
		$result[$rec['id_term']] = $rec['term'];
	}
	return $result;
}

function period_term_get($id_term = 0, $id_facility = 0, $is_active = 0)
{
    $result = array();
	$query = null;
	$fmt = DATE_FORMAT_MYSQL;
	if ($id_facility>0){
		$query = "SELECT fpt.* , u.full_name AS modified_by_name,  
					date_format(valid_from, '$fmt') AS valid_from_display, 
					date_format(valid_to, '$fmt') AS valid_to_display, 
					date_format(modified_on, '$fmt %H:%i') AS modified_on_display, 
					unix_timestamp(valid_from) AS valid_from_sec,
					unix_timestamp(valid_to) AS valid_to_sec
					FROM facility_period_map  map 
					LEFT JOIN facility_period_term fpt ON fpt.id_term = map.id_term 
					LEFT JOIN user u ON fpt.modified_by = u.id_user
					WHERE map.id_facility = $id_facility";
		if ($is_active) $query .= ' AND active=1 ';
		if ($id_term>0) $query .= " AND map.id_term = $id_term ";
    } else if ($id_term>0){
		$query  = "SELECT *, u.full_name AS modified_by_name,  
					date_format(valid_from, '$fmt') AS valid_from_display, 
					date_format(valid_to, '$fmt') AS valid_to_display, 
					date_format(modified_on, '$fmt %H:%i') AS modified_on_display, 
					unix_timestamp(valid_from) AS valid_from_sec,
					unix_timestamp(valid_to) AS valid_to_sec
					FROM facility_period_term fpt 
					LEFT JOIN user u ON fpt.modified_by = u.id_user 
					WHERE id_term = $id_term";
	}
	if (!empty($query)){
		$rs = mysql_query($query);
		//error_log(mysql_error().$query);
		if ($rs && (mysql_num_rows($rs)>0)){
			$result = mysql_fetch_assoc($rs);
		}
    }
    return $result;
}

function period_timesheet_rows($id_term = 0, $id_facility = 0)
{ //THIS IS
    $result = array();
	$fmt = DATE_FORMAT_MYSQL;
    $query  = "SELECT *, u.full_name AS modified_by_name,
				SUBSTR(time_start, 1, 5) AS start_time,
				SUBSTR(time_end, 1, 5) AS end_time 
                FROM facility_period_timesheet fpt 
                LEFT JOIN user u ON fpt.modified_by = u.id_user 
				WHERE fpt.id_term = '$id_term'
                ORDER BY time_start ASC
	";
    $rs = mysql_query($query);
	//error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    return $result;
}


/*
	relate to facility's equipment

*/

function get_equipment($fid = 0, $eid = 0)
{
    $result = array();
    $query  = "SELECT fe.*, c.category_name name 
				FROM facility_equipment fe 
				LEFT JOIN category c ON c.id_category = fe.id_category 
				WHERE id_facility = $fid AND id_equipment = $eid ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result = $rec;
    }
    
    return $result;
}

function get_equipment_by_category($fid = 0, $cid = 0)
{
    $result = array();
    $query  = "SELECT fe.*, c.category_name name 
				FROM facility_equipment fe 
				LEFT JOIN category c ON c.id_category = fe.id_category 
				WHERE id_facility = $fid AND fe.id_category = $cid ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result = $rec;
    }
    
    return $result;
}

function remove_equipment($fid = 0, $eid = 0)
{
    $result = false;
    $query  = "DELETE FROM facility_equipment 
				WHERE id_facility = $fid AND id_equipment = $eid ";
    mysql_query($query);
	$result = mysql_affected_rows()>0;
    
    return $result;
}

function get_equipments($fid = 0)
{
    $result = array();
    /*
	$query  = "SELECT fe.*, c.category_name name 
				FROM facility_equipment fe 
				LEFT JOIN category c ON c.id_category = fe.id_category 
				WHERE id_facility = $fid 
				ORDER BY category_name ";
	*/
    $query  = "SELECT c.category_name, COUNT(*) AS quantity 
				FROM facility f 
				LEFT JOIN item i ON i.id_location = f.id_location 
				LEFT JOIN category c ON c.id_category = i.id_category 
				WHERE id_facility = $fid 
				GROUP BY c.category_name ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        while ($rec = mysql_fetch_assoc($rs)){
			$result[$rec['category_name']] = $rec['quantity'];
    	}
    
    return $result;
}
