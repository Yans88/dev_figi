<?php


    
/* old
$repetitions = array(
    'One time booking', 
    'Daily',
    'Every Weekday (Mon-Fri)',
    'Weekly (Every DOW)',
    'Monthly (Every WEEKDOW)',
    'Monthly (on day DOM)',
    'Yearly (on MD)');
*/

function count_facilities(){
    $result = 0;
    $query  = "SELECT count(id_facility) FROM facility ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_facility_list($skip_no_timesheet = false){
    $result = array();
	if ($skip_no_timesheet)
    	$query  = 'SELECT f.id_facility, location_name facility_no, count(fts.id_facility)  
					FROM facility f 
                    LEFT JOIN location l ON f.id_location = l.id_location, facility_timesheet fts 
					WHERE fts.id_facility = f.id_facility 
				 	GROUP BY f.id_facility, facility_no 
                    ORDER BY location_name ASC';
	else
	    $query  = 'SELECT f.id_facility, location_name facility_no 
					FROM facility f 
                    LEFT JOIN location l ON f.id_location = l.id_location 
                    ORDER BY location_name ASC';
    $rs = mysql_query($query);
	//echo $query.mysql_error();
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[$rec['id_facility']] = $rec['facility_no'];
    }
    return $result;
}

function get_facilities($start = 0, $limit = 10){
    $result = array();
    $query  = "SELECT f.*, l.location_name facility_no, 
                time_format(time_start, '%H:%i') as time_start, time_format(time_end, '%H:%i') as time_end 
                FROM facility f 
                LEFT JOIN location l ON f.id_location = l.id_location 
                ORDER BY l.location_name ASC
                LIMIT $start, $limit ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
		$i = 0;
        while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;
    }
    return $result;
}

function get_facility($fid = 0){
    $result = array();
    $query  = "SELECT f.*, l.location_name facility_no, 
                time_format(time_start, '%H:%i') as time_start, time_format(time_end, '%H:%i') as time_end 
                FROM facility f 
                LEFT JOIN location l ON f.id_location = l.id_location  
                WHERE id_facility = '$fid'";
    $rs = mysql_query($query);
	//echo $query.mysql_error();
    if ($rs)// && (mysql_num_rows($rs)>0)
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_timesheets($fid = 0){
    $result = array();
    $query  = "SELECT fs.*, time_format(time_start, '%H:%i') as time_start, 
				time_format(time_end, '%H:%i') as time_end  
				FROM facility_timesheet fs 
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

function get_timesheets_by_time_range($fid, $stm, $etm){
    $result = array();
    $query  = "SELECT id_time
				FROM facility_timesheet fs 
				WHERE id_facility = $fid AND
				LEFT(time_start, 5) >= '$stm' AND LEFT(time_end, 5) <= '$etm' 
				ORDER BY time_start ";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
    }
    return $result;
}

function get_timesheet($fid = 0, $sid = 0){
    $result = array();
    $query  = "SELECT *, time_format(time_start, '%H:%i') as time_start, time_format(time_end, '%H:%i') as time_end  
				FROM facility_timesheet 
				WHERE id_facility = $fid AND id_time = $sid";
    $rs = mysql_query($query);
	//echo mysql_error();
    if ($rs) // && (mysql_num_rows($rs)>0)
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function build_facility_combo($name = 'id_facility', $selected = -1){
	
	return build_combo($name, get_facility_list(), $selected);
}

function build_timesheet($id_facility, $date_from, $date_to = '', $readonly = false){
    $booked = array();
	$days_to_display = FACILITY_DAYS_TO_DISPLAY;
	$stm = strtotime($date_from) + 25200;
	//echo "$stm : " . strtotime('2012-01-15');
	if (!empty($date_to)){
		$etm = strtotime($date_to);	
		$days_to_display = round(($etm - $stm) / 86400)+1;
	} else {
		//$etm = mktime(date('H', $stm), date('i', $stm), date('s', $stm), 
		//			date('n', $stm), date('j', $stm)+$days_to_display, date('Y', $stm));
		$etm = time_add_days($stm, $days_to_display);
	}
    $query = "SELECT fb.*, fbdt.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM facility_book fb
                LEFT JOIN facility_book_datetime fbdt ON fbdt.id_book = fb.id_book 
                LEFT JOIN user ON user.id_user = fb.id_user 
                WHERE unix_timestamp(booked_date) >= unix_timestamp('$date_from') AND unix_timestamp(booked_date) <= $etm ";
                //WHERE date_format(booked_date, '%Y-%m-%d') = '$date_from'";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['book_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	$facility = get_facility($id_facility);
    $timesheets = get_timesheets($id_facility);
    //$result = '<h4>Schedule for Facility "'.$facility['facility_no'].'"</h4>';
    $result = '<table id="timesheet_availability" class="facility_table" cellpadding=1 cellspacing=1>
                <tr align="center"><th width=100>Periode</th>';
	$tm = strtotime($date_from); 
	for ($d = 0; $d < $days_to_display; $d++){
		$checkbox = null;
		if (!$readonly)
			$checkbox = '<input type="checkbox" value="'.date('Y-m-d', $tm).'" onclick="toggle_day(this)">';
		$result .= '<th width=60>'.date('d-M', $tm).'<br/>'.date('D', $tm). '&nbsp;' . $checkbox . '</th>';
		$tm = date_add_day($tm, 1);
	}
	$result .= '</tr>';
    $row = 0;
    foreach ($timesheets as $rec){
        $class = ($row++ % 2 == 0) ? ' class="alt"' : ' class="normal"';
		$checkbox = null;
		if (!$readonly)
			$checkbox = '<input type="checkbox" value="'.$rec['id_time'].'" onclick="toggle_period(this)">';
		$result .= "<tr $class><td align='center'>$rec[time_start]-$rec[time_end]&nbsp;$checkbox</td>";
		$tm = strtotime($date_from); 
		for ($d = 0; $d < $days_to_display; $d++){
			$dt = date('Y-m-d', $tm);
			if (isset($booked[$dt][$rec['id_time']])){
				if ($booked[$dt][$rec['id_time']]['id_user'] == USERID)
					//$available = 'booked by you. <input type=button value="Remove Booking" onclick="remove_this('.$booked[$dt][$rec['id_time']]['id_book'].')">';
					$available = '<img width=16 height=16 src="images/bookmark.png" title="Booked by you">';
				else
					$available = '<img width=16 height=16 src="images/bookmark.png" title="Booked by ' . $booked[$dt][$rec['id_time']]['booked_by'] .'">';
                if (USERGROUP == GRPADM)
                    $available = '<a href="./?mod=facility&sub=booking&act=cancel&id=' . $booked[$dt][$rec['id_time']]['id_book'].
                                 '" onclick="return confirm(\'Are you sure to cancel this book?\');">'.
                                 $available . '</a>';
			} else
                if ($readonly)
                    $available = '<img width=16 height=16 src="images/new.png" title="Facility is available">';
                else
                    $available = '<input class="timesheet_times" type="checkbox" name="times[]" value="'.$dt.'_'.$rec['id_time'].'" onclick="check_time(this)">';
				//$available = '<input class="timesheet_times" type="checkbox" name="times[]" value="'.$rec['id_time'].'" onclick="check_time(this)"> available';
								
			$result .= "<td align='center'>$available</td>";
			$tm = date_add_day($tm, 1);
		}
		$result .= '</tr>';
    }
    $result .= '</table>';
    return $result;
}

function build_timesheet_old($id_facility, $date_from, $date_to = '', $readonly = false){
    $booked = array();
	$days_to_display = FACILITY_DAYS_TO_DISPLAY;
	$stm = strtotime($date_from) + 25200;
	//echo "$stm : " . strtotime('2012-01-15');
	if (!empty($date_to)){
		$etm = strtotime($date_to);	
		$days_to_display = round(($etm - $stm) / 86400)+1;
	} else {
		//$etm = mktime(date('H', $stm), date('i', $stm), date('s', $stm), 
		//			date('n', $stm), date('j', $stm)+$days_to_display, date('Y', $stm));
		$etm = time_add_days($stm, $days_to_display);
	}
    $query = "SELECT fb.*, fbdt.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM facility_book fb
                LEFT JOIN facility_book_datetime fbdt ON fbdt.id_book = fb.id_book 
                LEFT JOIN user ON user.id_user = fb.id_user 
                WHERE unix_timestamp(booked_date) >= unix_timestamp('$date_from') AND unix_timestamp(booked_date) <= $etm ";
                //WHERE date_format(booked_date, '%Y-%m-%d') = '$date_from'";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['book_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	$facility = get_facility($id_facility);
    $timesheets = get_timesheets($id_facility);
    //$result = '<h4>Schedule for Facility "'.$facility['facility_no'].'"</h4>';
    $result = '<table id="timesheet_availability" class="facility_table" cellpadding=1 cellspacing=1>
                <tr align="center"><th width=100>Periode</th>';
	$tm = strtotime($date_from); 
	for ($d = 0; $d < $days_to_display; $d++){
		$checkbox = null;
		if (!$readonly)
			$checkbox = '<input type="checkbox" value="'.date('Y-m-d', $tm).'" onclick="toggle_day(this)">';
		$result .= '<th width=60>'.date('d-M', $tm).'<br/>'.date('D', $tm). '&nbsp;' . $checkbox . '</th>';
		$tm = date_add_day($tm, 1);
	}
	$result .= '</tr>';
    $row = 0;
    foreach ($timesheets as $rec){
        $class = ($row++ % 2 == 0) ? ' class="alt"' : ' class="normal"';
		$checkbox = null;
		if (!$readonly)
			$checkbox = '<input type="checkbox" value="'.$rec['id_time'].'" onclick="toggle_period(this)">';
		$result .= "<tr $class><td align='center'>$rec[time_start]-$rec[time_end]&nbsp;$checkbox</td>";
		$tm = strtotime($date_from); 
		for ($d = 0; $d < $days_to_display; $d++){
			$dt = date('Y-m-d', $tm);
			if (isset($booked[$dt][$rec['id_time']])){
				if ($booked[$dt][$rec['id_time']]['id_user'] == USERID)
					//$available = 'booked by you. <input type=button value="Remove Booking" onclick="remove_this('.$booked[$dt][$rec['id_time']]['id_book'].')">';
					$available = '<img width=16 height=16 src="images/bookmark.png" title="Booked by you">';
				else
					$available = '<img width=16 height=16 src="images/bookmark.png" title="Booked by ' . $booked[$dt][$rec['id_time']]['booked_by'] .'">';
                if (USERGROUP == GRPADM)
                    $available = '<a href="./?mod=facility&sub=booking&act=cancel&id=' . $booked[$dt][$rec['id_time']]['id_book'].
                                 '" onclick="return confirm(\'Are you sure to cancel this book?\');">'.
                                 $available . '</a>';
			} else
                if ($readonly)
                    $available = '<img width=16 height=16 src="images/new.png" title="Facility is available">';
                else
                    $available = '<input class="timesheet_times" type="checkbox" name="times[]" value="'.$dt.'_'.$rec['id_time'].'" onclick="check_time(this)">';
				//$available = '<input class="timesheet_times" type="checkbox" name="times[]" value="'.$rec['id_time'].'" onclick="check_time(this)"> available';
								
			$result .= "<td align='center'>$available</td>";
			$tm = date_add_day($tm, 1);
		}
		$result .= '</tr>';
    }
    $result .= '</table>';
    return $result;
}

function build_timesheet_book($book_for_date , $readonly = false){
    $booked = array();
	$days_to_display = FACILITY_DAYS_TO_DISPLAY;
	$stm = strtotime($book_for_date) + 25200;
	//echo "$stm : " . strtotime('2012-01-15');
	$etm = mktime(date('H', $stm), date('i', $stm), date('s', $stm), 
					date('n', $stm), date('j', $stm)+$days_to_display, date('Y', $stm));
    $query = "SELECT fb.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM facility_book fb
                LEFT JOIN user ON user.id_user = fb.id_user 
                WHERE unix_timestamp(booked_date) >= unix_timestamp('$book_for_date') AND unix_timestamp(booked_date) <= $etm ";
                //WHERE date_format(booked_date, '%Y-%m-%d') = '$book_for_date'";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['book_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	$facility = get_facility($_POST['id_facility']);
    $timesheets = get_timesheets($_POST['id_facility']);
    $result = '<h4>Booking Schedule for Facility "'.$facility['facility_no'].'"</h4>
                <table width="100%" id="timesheet_availability" class="facility_table" cellpadding=1 cellspacing=1>
                <tr align="center"><th width=100>Periode</th>';
	$tm = strtotime($book_for_date); 
	for ($d = 0; $d < $days_to_display; $d++){
		$checkbox = null;
		if (!$readonly)
			$checkbox = '<input type="checkbox" value="'.date('Y-m-d', $tm).'" onclick="toggle_day(this)">';
		$result .= '<th>'.date('d-M-y', $tm).'<br/>'.date('D', $tm). '&nbsp; ' . $checkbox . '</th>';
		$tm = date_add_day($tm, 1);
	}
	$result .= '</tr>';
    $row = 0;
    foreach ($timesheets as $rec){
        $class = ($row++ % 2 == 0) ? ' class="alt"' : ' class="normal"';
		$checkbox = null;
		if (!$readonly)
			$checkbox = '<input type="checkbox" value="'.$rec['id_time'].'" onclick="toggle_period(this)">';
		$result .= "<tr $class><td align='center'>$rec[time_start]-$rec[time_end]&nbsp;$checkbox</td>";
		$tm = strtotime($book_for_date); 
		for ($d = 0; $d < $days_to_display; $d++){
			$dt = date('Y-m-d', $tm);
			if (isset($booked[$dt][$rec['id_time']])){
				if ($booked[$dt][$rec['id_time']]['id_user'] == USERID)
					//$available = 'booked by you. <input type=button value="Remove Booking" onclick="remove_this('.$booked[$dt][$rec['id_time']]['id_book'].')">';
					$available = '<img width=16 height=16 src="images/bookmark.png" title="Booked by you">';
				else
					$available = '<img width=16 height=16 src="images/bookmark.png" title="Booked by ' . $booked[$dt][$rec['id_time']]['booked_by'] .'">';
                if (USERGROUP == GRPADM)
                    $available = '<a href="./?mod=facility&sub=booking&act=cancel&id=' . $booked[$dt][$rec['id_time']]['id_book'].
                                 '" onclick="return confirm(\'Are you sure to cancel this book?\');">'.
                                 $available . '</a>';
			} else
                if ($readonly)
                    $available = '<img width=16 height=16 src="images/new.png" title="Facility is available">';
                else
                    $available = '<input class="timesheet_times" type="checkbox" name="times[]" value="'.$dt.'_'.$rec['id_time'].'" onclick="check_time(this)">';
				//$available = '<input class="timesheet_times" type="checkbox" name="times[]" value="'.$rec['id_time'].'" onclick="check_time(this)"> available';
								
			$result .= "<td align='center'>$available</td>";
			$tm = date_add_day($tm, 1);
		}
		$result .= '</tr>';
    }
    $result .= '</table>';
    return $result;
}


function build_timesheet_book_as_csv($book_for_date, $id_facility  = 0){
	$crlf = "\r\n";
    $booked = array();
	$days_to_display = FACILITY_DAYS_TO_DISPLAY;
	$stm = strtotime($book_for_date) + 25200;

    if (!empty($date_to)){
        $etm = strtotime($date_to);
        $days_to_display = round(($etm - $stm) / 86400)+1;
    } else {
        //$etm = mktime(date('H', $stm), date('i', $stm), date('s', $stm),
        //          date('n', $stm), date('j', $stm)+$days_to_display, date('Y', $stm));
        $etm = time_add_days($stm, $days_to_display);
    }

   $query = "SELECT fb.*, fbdt.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date
                FROM facility_book fb
                LEFT JOIN facility_book_datetime fbdt ON fbdt.id_book = fb.id_book
                LEFT JOIN user ON user.id_user = fb.id_user
                WHERE unix_timestamp(booked_date) >= unix_timestamp('$book_for_date') AND unix_timestamp(booked_date) <= $etm ";
/*
    $query = "SELECT fb.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM facility_book fb
                LEFT JOIN user ON user.id_user = fb.id_user 
                WHERE unix_timestamp(booked_date) >= unix_timestamp('$book_for_date') AND unix_timestamp(booked_date) <= $etm ";
*/
// LEFT JOIN facility_timesheet ft ON ft.id_facility = $id_facility 
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['book_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	$facility = get_facility($id_facility);
    $timesheets = get_timesheets($id_facility);
    $result = 'Periode';
	$tm = strtotime($book_for_date); 
	for ($d = 0; $d < $days_to_display; $d++){
		$result .= ',"'.date('d-M-y', $tm).', '.date('D', $tm) . '"';
		$tm = date_add_day($tm, 1);
	}
	$result .= $crlf;
    $row = 0;
    foreach ($timesheets as $rec){
		$result .= "$rec[time_start]-$rec[time_end]";
		$tm = strtotime($book_for_date); 
		for ($d = 0; $d < $days_to_display; $d++){
			$dt = date('Y-m-d', $tm);
			if (isset($booked[$dt][$rec['id_time']])){
				$result .= ',Booked by ' . $booked[$dt][$rec['id_time']]['booked_by'];
			} else
                $result .= ',Available';
								
			$tm = date_add_day($tm, 1);
		}
		$result .= $crlf;
    }
    return $result;
}

function build_timesheet_book_as_csv_old($book_for_date, $id_facility  = 0){
	$crlf = "\r\n";
    $booked = array();
	$days_to_display = FACILITY_DAYS_TO_DISPLAY;
	$stm = strtotime($book_for_date) + 25200;
	$etm = mktime(date('H', $stm), date('i', $stm), date('s', $stm), 
					date('n', $stm), date('j', $stm)+$days_to_display, date('Y', $stm));
    $query = "SELECT fb.*, user.full_name booked_by, date_format(booked_date, '%Y-%m-%d') book_date 
				FROM facility_book fb
                LEFT JOIN user ON user.id_user = fb.id_user 
                WHERE unix_timestamp(booked_date) >= unix_timestamp('$book_for_date') AND unix_timestamp(booked_date) <= $etm ";
// LEFT JOIN facility_timesheet ft ON ft.id_facility = $id_facility 
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $booked[$rec['book_date']][$rec['id_time']] = $rec;
    }
	//print_r($booked);
	$facility = get_facility($id_facility);
    $timesheets = get_timesheets($id_facility);
    $result = 'Periode';
	$tm = strtotime($book_for_date); 
	for ($d = 0; $d < $days_to_display; $d++){
		$result .= ',"'.date('d-M-y', $tm).', '.date('D', $tm) . '"';
		$tm = date_add_day($tm, 1);
	}
	$result .= $crlf;
    $row = 0;
    foreach ($timesheets as $rec){
		$result .= "$rec[time_start]-$rec[time_end]";
		$tm = strtotime($book_for_date); 
		for ($d = 0; $d < $days_to_display; $d++){
			$dt = date('Y-m-d', $tm);
			if (isset($booked[$dt][$rec['id_time']])){
				$result .= ',Booked by ' . $booked[$dt][$rec['id_time']]['booked_by'];
			} else
                $result .= ',Available';
								
			$tm = date_add_day($tm, 1);
		}
		$result .= $crlf;
    }
    return $result;
}

function get_facility_request($id_in_csv = null){
	$data = array();
	if ($id_in_csv != null){
		$query = "SELECT *, date_format(booked_date, '%Y-%m-%d') booked_date, date_format(request_date, '%Y-%m-%d %H:%i:%s') request_date, 
					date_format(fs.time_start, '%H:%i') time_start, date_format(fs.time_end, '%H:%i') time_end  
					FROM facility_book fb 
					LEFT JOIN facility_timesheet fs ON fb.id_time = fs.id_time 
					LEFT JOIN facility f ON fs.id_facility = f.id_facility 
					LEFT JOIN user u ON u.id_user = fb.id_user 
					WHERE id_book in ($id_in_csv) ";
		$rs = mysql_query($query);
		//echo mysql_error().$query;
		if ($rs)
			while ($rec = mysql_fetch_assoc($rs)){
				$data['facility_no'] = $rec['facility_no'];
				$data['request_date'] = $rec['request_date'];
				$data['remark'] = $rec['remark'];
				$data['user_email'] = $rec['user_email'];
				$data['full_name'] = $rec['full_name'];
				if (!isset($data['periods']))
					$data['periods'] = "\r\n";
				$data['periods'] .= "\t" . $rec['booked_date'] . ' : ' . $rec['time_start']  . ' - ' . $rec['time_end'] ."\r\n";
			}
	}
	return $data;
}

function get_booking_info($id){
	global $repetition_labels;
	$data = array();
	$dtfmt = "'%d-%M-%Y'";
	if ($id > 0){
        $query = "SELECT *, full_name AS requester, 
        			DATE_FORMAT(FROM_UNIXTIME( book_date ), $dtfmt) AS request_date,
        			location_name AS facility_no, 
        			DATE_FORMAT(FROM_UNIXTIME( dt_start ),$dtfmt) AS start_datetime,
        			DATE_FORMAT(FROM_UNIXTIME( dt_end ),$dtfmt) AS end_datetime
                    FROM facility_book_view fb 
                    WHERE id_book = $id";                    
		$rs = mysql_query($query);
		if ($rs){
            $data = mysql_fetch_assoc($rs);
            $repeat = $data['repetition'];
            $data['repetition_info'] = $repetition_labels[$repeat];
            if (!empty($data['dt_last']))
            	$data['repetition_info'] .= ' until '. date('d-M-Y', $data['dt_last']); 
        }
	}
	return $data;
}
function get_booking_attachment($id){
	$result = array();
	if($id > 0){
		$query = "SELECT * FROM  facility_book_attachment where id_book = $id";
		$rs = mysql_query($query);
		while($rec = mysql_fetch_array($rs)){
			$result[] = $rec;
		}
	}
	return $result;
}
function get_attachment_facility($id){
	$query = "SELECT * FROM facility_book_attachment WHERE id_attach = $id";
	$rs = mysql_query($query);
	$data = mysql_fetch_assoc($rs);
	return $data;
}
function get_instance_info($id, $start, $end)
{
	$result = null;
	if ($id > 0){
		$query = "SELECT * FROM facility_book_instances 
					WHERE id_book = $id AND start = $start AND end = $end";
		$rs = mysql_query($query);
		if ($rs)
			$result = mysql_fetch_array($rs);
	}
	return $result;
}


function send_submit_facility_request_notification($id){
    global $transaction_prefix, $configuration;
    //error_log(serialize($configuration));
    $config = $configuration['facility'];
    
    if ($config['enable_notification'] != 'true') return false;
    
    //$id_str = implode(',', $ids);
	$data = book_info($id);
    if (count($data) == 0) return false;
	$period = book_period_info($id);
	$user_info = get_user($data['id_user']);
	$data['requester'] = $user_info['full_name'];
	$data = array_merge($data, $period);   
	$data = array_merge($data, $user_info); 
	$emails = array();  
	$facility = get_facility($data['id_facility']);
	if (!empty($facility['email'])){
		$admins = explode(',', $facility['email']);
	foreach($admins as $line){
	$rec = explode('|', $line);
	if (!empty($rec[0]))
		$emails[] = $rec[0];
	
		}
	}
    //error_log(serialize($data));
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $to =$data['user_email'];
        //$email_rec = get_notification_emails($data['id_department'], 0, 'facility');
        //foreach ($email_rec as $rec)
        //    $emails[] = $rec['email'];
        if (!empty($to)) {
            $message = compose_message('messages/facility-request-submit.msg', $data);
            //$to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Facility ('. $data['facility_no'] . ') has been booked by ' . $data['full_name'];
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'facility', 'email');
            process_notification($id_msg);
            //error_log($to.$cc.$subject.$data['id_department']);
        }
    }
    
	if(sms_facility_booking){
		if ($config['enable_notification_sms'] == 'true'){
			$message = null;
			$to = null;
			if (!empty($facility['handphone'])){
				$admins = explode(',', $facility['handphone']);
				foreach($admins as $line){
					$rec = explode('|', $line);
					if (!empty($rec[0]))
						$hp[] = $rec[0];		
				}
			}
					
			$to .= implode(',', $hp);
			$mobile_rec = get_notification_mobiles($data['id_department'], 0, 'facility');			
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
				array_unshift($mobiles, $data['contact_no']);
			$to .= implode(',', $mobiles);
			$message = compose_message('messages/facility-request-submit.sms', $data);
			//SendSMS(SMS_SENDER, $to, $message);
			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'facility', 'sms');
			process_notification($id_msg);
			writelog('send_submit_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
		}
	}

}

/* Notification per facility */
function send_submit_facility_request_per_facility($id){
    global $transaction_prefix, $configuration;
    $config = $configuration['facility'];
    
    if ($config['enable_notification'] != 'true') return false;
    
    //$id_str = implode(',', $ids);
	$data = book_info($id);
    if (count($data) == 0) return false;
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['email']);
        $email_rec = get_notification_emails($data['id_department'], 0, 'facility');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/facility-request-submit.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Facility ('. $data['book_date'] . ') has been booked.';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'facility', 'email');
            process_notification($id_msg);
            //error_log($to.$cc.$subject.$data['id_department']);
        }
    }

}

function export_booking_schedule($date, $id_facility){
    $crlf = "\r\n";
    ob_clean();
    ini_set('max_execution_time', 60);
    //$today = date('dMY');
	$facility = get_facility($id_facility);
    $fname = "figi_booking_schedule-$date-$facility[facility_no].csv";
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=$fname");
    header("Pragma: no-cache");
    header("Expires: 0");
	echo build_timesheet_book_as_csv($date, $id_facility);
    ob_end_flush();
    exit;
}


function export_facility_list(){
   $crlf = "\r\n";
    ob_clean();
    ini_set('max_execution_time', 60);
    //$today = date('dMY');
	$total = count_facilities();
	$facilities = get_facilities(0, $total);
    $fname = "figi_facility_list.csv";
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=$fname");
    header("Pragma: no-cache");
    header("Expires: 0");
	echo 'Facility No,Period,Max. Number of Period,Lead Time,Time Usage'. $crlf;
	foreach($facilities as $facility){
		echo "$facility[facility_no],$facility[period_duration],$facility[max_period],$facility[lead_time],";
		echo "$facility[time_start]-$facility[time_end]$crlf";

	}
    ob_end_flush();
    exit;

}

function get_book_by_date($dt, $_facility){
	$result = Array();
    $bookinfo = array();
    $d = date('Y-m-d', $dt);
    $dtonly = mktime(0,0,0,date('n', $dt),date('j', $dt),date('Y', $dt));
    $query = "SELECT *, date_format(date_start, '%Y%c%e') cal_code, UNIX_TIMESTAMP(repeat_until) repeat_until_t, 
                UNIX_TIMESTAMP(date_start) date_start_t, UNIX_TIMESTAMP(date_finish) date_finish_t, 
                date_format(date_start, '%d-%b-%Y') date_start_fmt, date_format(date_finish, '%d-%b-%Y') date_finish_fmt,  
                date_format(time_start, '%H:%i') time_start_fmt, date_format(time_finish, '%H:%i') time_finish_fmt  
                FROM facility_book_view ce 
				WHERE ce.date_start <= '$d'  AND status IN ('BOOK', 'COMMENCE') AND id_facility = $_facility 
                ORDER BY book_date ASC";
    
	$rs = mysql_query($query);	
    //echo $query.mysql_error();
	while ($rec = mysql_fetch_assoc($rs))
        $bookinfo[$rec['id_book']] = $rec;
    //print_r($bookinfo);
    $y = date('Y', $dt);
    $m = date('n', $dt);
    $d = date('j', $dt);
    $selected_date = mktime(0, 0, 0, $m, date('j', $dt), $y);
    $start_date_of_the_month = mktime(0, 0, 0, $m, 1, $y);
    $last_date_of_the_month = mktime(0, 0, 0, $m, date('t', $start_date_of_the_month), $y);
    foreach ($bookinfo as $id_event => $rec){
        if (!empty($rec)){
            $cal_code = $rec['cal_code'];
            $dtend = $rec['repeat_until_t'];
            if (empty($dtend) || $dtend > $last_date_of_the_month)
                $dtend = $last_date_of_the_month;
            if ($rec['repetition'] == 1) { // daily 
                cal_fill_daily($result, $rec, $dtend, $selected_date);
            } else
            if ($rec['repetition'] == 2) { // weekly
                cal_fill_weekly($result, $rec, $dtend, $selected_date);
            } else
            if ($rec['repetition'] == 3) { // monthly
                cal_fill_monthly($result, $rec, $dtend, $selected_date);
            } else { 
                //print_r($rec);                echo $cal_code.date('Ynj',$dt);
                $ymd = date('Y-m-d',$dt);
                //echo "$rec[date_start] $ymd $rec[date_finish]<br/>"; 
                if (($rec['date_start'] <= $ymd) && ($rec['date_finish'] >= $ymd))
                    $result[$cal_code][] = $rec;
            }
        }
	}
    if (!empty($result)){
        $books = array_values($result);
        $books = $books[0];
    }
    unset($result);
    $result = null;
    $fullday_list = null;
    $event_list = null;
    $events = array();
    $cur_event_date = date('D, d M Y', $dt);

    $sheets = get_timesheets($_facility);
    $rowsheet_top = ' top';
    $no = 1;
    $differ = -1; $id_event = -1;
    // get fullday book
    foreach ($books as $rec){
        $id = str_replace(':', '', $rec['time_start_fmt']);
        $id_event = $rec['id_book'];
        if ($rec['fullday']>0){
            $info = 'Fullday event. ' . $rec['title'] . ' @ ' . $rec['location_name'];
            $fullday_list .= '<div id="info'.$id_event.'" class="bookinfo">' ;
            $fullday_list.= '<div class="bookedtime"><a class="bookitem" id="book'.$id_event.'" onmousemove="show_desc(event,\''.$id_event.'\')" onmouseout="hide_desc(event,\''.$id_event.'\')" class="event" href="#'.$id_event.'">' . $info . "</a></div>\n";
            $fullday_list .=<<<DESC
            <div class="desc" id="desc-$id_event">
            Purpose: $rec[purpose]<br/>
            Date: $rec[cur_event_date]<br/> 
            Time: Fullday event<br/> 
            Remark: $rec[remark] <br/> 
            Booked by: $rec[full_name] 
            </div>\n
DESC;
            
            $fullday_list .= '</div>';
        } else {
            $events[] = $rec;
        }
    }    
    foreach ($sheets as $id_time => $sheet){
        //$id_time = $sheet['id_time'];
        //if (substr($sheet['time_start'], 3, 2) != '00') continue;
        $odd = ($no++ % 2 != 0);
        $id = str_replace(':', '', $sheet['time_start']);
        $event_list .= '<div id="row'.$id_time.'" class="rowsheet '.$rowsheet_top.'">';
        if (strpos($sheet['time_start'], ':00')!==false)
            $event_list .= '<div id="sheet'.$id_time.'" class="timesheet start-period">' . $sheet['time_start'] . '</div>';
        else
            $event_list .= '<div id="sheet'.$id_time.'" class="timesheet">&nbsp;</div>';
        $event_list .= '<div id="info-'.$id.'" class="bookinfo">' ;
        $plus_btn = null;
        if (defined('USERID') && USERID>0){
            $cdtm = mktime(date('G'), 0, 0, $m, $d, $y);
            if (date('YmdH', $cdtm) > date('YmdH'))
                $plus_btn = '<div class="plus" id="plus-'.$id . '" style="display: none; "><a href="javascript:void(0)" class="addbtn" id="addbtn-'.$y.'-'.$m.'-'.$d.'" title="add an event">+</a></div>' ;
        }
        $rowsheet_top = null;
        if (!empty($events)){
            foreach ($events as $rec){
                $id_event = $rec['id_book'];
                if ($rec['cur_event_date'] == $cur_event_date){
                    if (($rec['time_start_fmt'] == $sheet['time_start']) ){ //&& ($rec['time_finish_fmt'] >= $sheet['time_end'])
                        $timerange = $rec['time_start_fmt'] . '-' . $rec['time_finish_fmt'];
                        if ($rec['fullday']>0)
                            $timerange = 'Fullday event';
                        $info = $timerange . '. ' . $rec['title'] . ' @ ' . $rec['location_name'];
                        $event_list.= '<div class="bookedtime"><a class="bookitem" id="book'.$id_event.'" onmousemove="show_desc(event,\''.$id_event.'\')" onmouseout="hide_desc(event,\''.$id_event.'\')" class="event" href="#'.$id_event.'">' . $info . "</a></div>\n";
                        $event_list .=<<<BOOKDESC
                        <div class="desc" id="desc-$id_event">
                        Purpose: $rec[purpose]<br/>
                        Date: $rec[cur_event_date]<br/> 
                        Time: $timerange<br/> 
                        Remark: $rec[remark] <br/> 
                        Booked by: $rec[full_name] 
                        </div>\n
BOOKDESC;
                    } 
                    else {
                        $event_list .= $plus_btn;
                    }
                }
            
            }
        }
        $event_list .= '</div></div>';        
        /*
        $result .= '<div id="row'.$id_time.'" class="rowsheet '.$rowsheet_top.'">';
        $result .= '<div id="sheet'.$id_time.'" class="timesheet">' . $sheet['time_start'] . '</div>';
        $result .= '<div id="info'.$id_time.'" class="bookinfo">' ;
        //$class = ($odd) ? 'firsthalf' : 'secondhalf';
        //$result .= '<div id="info'.$id_time.'" class="'.$class.'">' ;
        $rowsheet_top = null;
        if (!empty($books)){
            foreach ($books as $rec){
                $id_book = $rec['id_book'];
                if (!empty($rec)){
                    //print_r($rec);
                    //echo $rec['time_start_fmt'] .'>='. $sheet['time_start'].' ===== '.$rec['time_finish_fmt'] .'>='. $sheet['time_end'].'<br/>';
                    if (($rec['time_start_fmt'] <= $sheet['time_start']) && ($rec['time_finish_fmt'] >= $sheet['time_end'])){
                        $timerange = $rec['time_start_fmt'] . '-' . $rec['time_finish_fmt'];
                        $info = $timerange . '. ' . $rec['purpose'] . ' @ ' . $rec['location_name'];
                        $result.= '<div class="bookedtime"><a class="bookitem" id="book'.$id_book.'" onmousemove="show_desc(event,\''.$id_book.'\')" onmouseout="hide_desc(event,\''.$id_book.'\')" class="book" href="#'.$id_book.'">' . $info . "</a></div>\n";
                        $result .=<<<BOOKDESC
                        <div class="desc" id="desc-$id_book">
                        Facility: $rec[facility_no]<br/> 
                        Date: $rec[date_start_fmt] - $rec[date_finish_fmt]<br/> 
                        Time: $rec[time_start_fmt] - $rec[time_finish_fmt]<br/> 
                        Purpose: $rec[purpose]<br/>
                        Description: $rec[remark] <br/> 
                        User: $rec[full_name] 
                        </div>\n
BOOKDESC;
                    }
                }
            
            }
        }
        $result .= '</div></div>';
    */
	}
    $result = $fullday_list . $event_list;
	return $result;
}

function get_book_on_date($dt, $_facility, $dte = 0){
	$result = Array();
	if ($dte==0) $dte = $dt;
		//$dte = $dt + (24 * 60 * 60) -1;
    $query = "SELECT *
                FROM facility_book_instances fbi, facility_book fb  
				WHERE fbi.start <= $dt  AND fbi.end >= $dte  AND fbi.id_book = fb.id_book AND 
                fb.status IN ('BOOK', 'COMMENCE') AND fb.id_facility = $_facility 
                ORDER BY fb.book_date ASC";
    
	$rs = mysql_query($query);
	//echo $query.mysql_error();   
	while ($rec = mysql_fetch_assoc($rs))
        $result[] = $rec;
	return $result;
}

function get_book_by_month($y, $m){
    $result = array();
    $query = "SELECT date_format(booked_date, '%Y%c%e') booked_date, id_book 
                FROM facility_book_datetime fbd 
                WHERE date_format(booked_date, '%Y-%c') = '$y-$m'
                GROUP BY booked_date, id_book";
    $rs = mysql_query($query);
    //echo $query;
    while ($rec = mysql_fetch_row($rs))
        $result[$rec[0]][] = $rec[1];
    return $result;
}

function get_bookings($start_date, $end_date, $id_facility = 0, $check_instance = false){
	$result = Array();
    // check for last generated instance, for recurring events
    if ($check_instance){ 
        $query = "SELECT * 
                    FROM facility_book  
                    WHERE status = 'BOOK' AND repetition != 'NONE' AND (dt_last IS NULL OR dt_last > $start_date) 
                    AND (dt_instance IS NULL OR dt_instance < $end_date) AND id_facility = $id_facility"; 
                    //AND dt_start >= $start_date 
        $rs = mysql_query($query);
        //echo $query.mysql_error()."<br>";
        //putLog('check', array($query, mysql_error()));
        while ($rec = mysql_fetch_assoc($rs)){
            $last_generated = $rec['dt_instance'];
            if (empty($last_generated) || $last_generated == 0){
                $last_generated = $rec['dt_start'];
            }
            $last_date = ($rec['dt_last']>0 &&  $rec['dt_last']<$end_date) ? $rec['dt_last'] : $end_date;
            //echo "$rec[id_event] - $rec[dt_start] - $rec[dt_end]- $last_generated - $last_date - $start_date - $end_date<br>\n";
            if ($last_generated >= $rec['dt_start'] && ($last_generated < $last_date)){
                $start_instance = $last_generated+86400;
                $last_generated += ($rec['repetition'] == 'MONTHLY') ? 86400*90 : 86400*30;
                //echo "\n$start_instance - $last_generated ";
                generate_booking_instances($rec['id_book'], $start_instance , $last_generated, false );
            }
        }
    }
    $query = "SELECT cei.*, ce.purpose, ce.location_name, ce.fullday, ce.repetition, ce.remark,
                ce.id_facility, ce.`interval`, ce.dt_last, id_user, full_name, id_group,
                cei.purpose purpose_instance, cei.remark remark_instance, cei.fullday fullday_instance 
                FROM facility_book_instances cei 
                LEFT JOIN facility_book_view ce ON ce.id_book = cei.id_book 
                WHERE status = 'BOOK' AND cei.start >= $start_date  AND cei.end <= $end_date 
                AND id_facility = $id_facility ";
    $rs = mysql_query($query);
    //error_log($query.mysql_error());
    if ($rs){
	    while ($rec = mysql_fetch_assoc($rs)){
	        $result[] = $rec;
	    }
	}
    //echo date('YmdHis', $start_date) . '====' .date('YmdHis', $end_date);
    //print_r($result);
	return $result;
}

function delete_book_instance($id, $dts = 0, $dte = 0){
    $query = "DELETE FROM facility_book_instances WHERE id_book = $id ";
    if ($dts > 0){
        if ($dte > 0)
            $query .= " AND start >= $dts AND end <= $dte"; // range of instance
        else if ($dte == -1)
            $query .= " AND start >= $dts "; // selected intance and future
        else
            $query .= " AND start = $dts"; // an intance
    }
    //echo $query;
    mysql_query($query);
}

function get_bookings_by_date($dt, $id_facility){
	$result = Array();
    $bookinfo = array();
    $d = date('Y-m-d', $dt);
    $dtonly = mktime(0,0,0,date('n', $dt),date('j', $dt),date('Y', $dt));
    $query = "SELECT *, date_format(date_start, '%Y%c%e') cal_code, UNIX_TIMESTAMP(repeat_until) repeat_until_t, 
                UNIX_TIMESTAMP(date_start) date_start_t, UNIX_TIMESTAMP(date_finish) date_finish_t, 
                date_format(date_start, '%d-%b-%Y') date_start_fmt, date_format(date_finish, '%d-%b-%Y') date_finish_fmt,  
                date_format(time_start, '%H:%i') time_start_fmt, date_format(time_finish, '%H:%i') time_finish_fmt  
                FROM facility_book_view fb 
				WHERE fb.date_start <= '$d' ";
    if ($id_facility>0)
        $query .= " AND fb.id_facility = $id_facility ";
    $query.= " ORDER BY book_date ASC";
    
	$rs = mysql_query($query);	
    //echo $query.mysql_error();
	while ($rec = mysql_fetch_assoc($rs))
        $bookinfo[$rec['id_book']] = $rec;
    //print_r($bookinfo);
    $y = date('Y', $dt);
    $m = date('n', $dt);
    $selected_date = mktime(0, 0, 0, $m, date('j', $dt), $y);
    $start_date_of_the_month = mktime(0, 0, 0, $m, 1, $y);
    $last_date_of_the_month = mktime(0, 0, 0, $m, date('t', $start_date_of_the_month), $y);
    foreach ($bookinfo as $id_book => $rec){
        if (!empty($rec)){
            $cal_code = $rec['cal_code'];
            $dtend = $rec['repeat_until_t'];
            if (empty($dtend) || $dtend > $last_date_of_the_month)
                $dtend = $last_date_of_the_month;
            if ($rec['repetition'] == 1) { // daily 
                fill_daily($result, $rec, $dtend, $selected_date);
            } else
            if ($rec['repetition'] == 2) { // weekly
                fill_weekly($result, $rec, $dtend, $selected_date);
            } else
            if ($rec['repetition'] == 3) { // monthly
                fill_monthly($result, $rec, $dtend, $selected_date);
            } else { 
                //print_r($rec);                echo $cal_code.date('Ynj',$dt);
                $ymd = date('Y-m-d',$dt);
                //echo "$rec[date_start] $ymd $rec[date_finish]<br/>"; 
                if (($rec['date_start'] <= $ymd) && ($rec['date_finish'] >= $ymd))
                    $result[$cal_code][] = $rec;
            }
        }
	}
    if (!empty($result)){
        $books = array_values($result);
        $books = $books[0];
    }
    unset($result);
    //print_r($books);
    $sheets = get_timesheets($id_facility);
    $rowsheet_top = ' top';
    $no = 1;
    $differ = -1; $id_book = -1;
    foreach ($sheets as $sheet){
        $id_time = $sheet['id_time'];
        if (substr($sheet['time_start'], 3, 2) != '00') continue;
        $odd = ($no++ % 2 != 0);
        $result .= '<div id="row'.$id_time.'" class="rowsheet '.$rowsheet_top.'">';
        $result .= '<div id="sheet'.$id_time.'" class="timesheet">' . $sheet['time_start'] . '</div>';
        $result .= '<div id="info'.$id_time.'" class="bookinfo">' ;
        //$class = ($odd) ? 'firsthalf' : 'secondhalf';
        //$result .= '<div id="info'.$id_time.'" class="'.$class.'">' ;
        $rowsheet_top = null;
        if (!empty($books)){
            foreach ($books as $rec){
                $id_book = $rec['id_book'];
                if (!empty($rec)){
                    //print_r($rec);
                    //echo $rec['time_start_fmt'] .'>='. $sheet['time_start'].' ===== '.$rec['time_finish_fmt'] .'>='. $sheet['time_end'].'<br/>';
                    if (($rec['time_start_fmt'] <= $sheet['time_start']) && ($rec['time_finish_fmt'] >= $sheet['time_end'])){
                        $timerange = $rec['time_start_fmt'] . '-' . $rec['time_finish_fmt'];
                        $info = $timerange . '. ' . $rec['purpose'] . ' @ ' . $rec['facility_no'];
                        $result.= '<div class="bookedtime"><a class="bookitem" id="book'.$id_book.'" onmousemove="show_desc(event,\''.$id_book.'\')" onmouseout="hide_desc(event,\''.$id_book.'\')" class="event" href="#'.$id_book.'">' . $info . "</a></div>\n";
                        $result .=<<<BOOKDESC
                        <div class="desc" id="desc-$id_book">
                        User: $rec[full_name] <br/> 
                        Date: $rec[date_start_fmt] - $rec[date_finish_fmt]<br/> 
                        Time: $rec[time_start_fmt] - $rec[time_finish_fmt]<br/> 
                        Facility: $rec[facility_no]<br/> 
                        Purpose: $rec[purpose]<br/>
                        Remark: $rec[remark] 
                        </div>\n
BOOKDESC;
                    }
                }
            
            }
        }
        $result .= '</div></div>';
	}
	return $result;
}

function fill_daily(&$res, $rec, $dtend, $fdt = 0){
    $dt = $rec['date_start_t'];
    while ($dt < $dtend){
        $cal_code = date('Ynj', $dt);
         $rec['cur_date'] = date('Y-m-d', $dt);
        if ($fdt > 0){
            if ($dt == $fdt){
                $res[$cal_code][] = $rec;
                break;
            }
        } else
            $res[$cal_code][] = $rec;
        $dt = date_add_day($dt, $rec['repeat_interval']);
    }
}


function fill_monthly(&$res, $rec, $dtend, $fdt = 0){
    $dt = $rec['date_start_t'];
    $dte = $rec['date_finish_t'];
    $delta = $dte-$dt;
    $dom = date('md', $dt);
    while ($dt < $dtend){
        $sdt = $dt;
        $long = $dt+$delta;
        $cal_code = date('Ynj', $dt);
        /*
        $res[$cal_code][] = $rec;
        */
        while ($dt <= $long){
            $rec['cur_date'] = date('Y-m-d', $dt);
            $cal_code = date('Ynj', $dt);
            if ($fdt > 0){
                if ($dt == $fdt){
                    $res[$cal_code][] = $rec;
                    break;
                }
            } else
                $res[$cal_code][] = $rec;
            $dt = date_add_day($dt, 1);
        }
        $dt = date_add_months($sdt, $rec['repeat_interval']);
    }
}

function fill_weekly(&$res, $rec, $dtend, $fdt = 0){
    $dt = $rec['date_start_t'];
    $dte = $rec['date_finish_t'];
    $dte  = date_add_day($dt, 6);
    $delta = $dte-$dt;
    $options = array();
    if (!empty($rec['repeat_option']))
        $options = explode(',', $rec['repeat_option']);
    $dw = date('w', $dt);
    if (empty($options))
        $options = array($dw);
    
    while (!in_array($dw, $options)){ // find exptected dow
        $dt = date_add_day($dt, 1);
        $dw = date('w', $dt);
    }
    while ($dt < $dtend){
        $sdt = $dt;
        $long = $dt+$delta;
        $cal_code = date('Ynj', $dt);
        /*
        $res[$cal_code][] = $rec;
        */
        while ($dt <= $long){
            $dw = date('w', $dt);
            if (in_array($dw, $options)){
                $rec['cur_date'] = date('Y-m-d', $dt);
                $cal_code = date('Ynj', $dt);
                if ($fdt > 0){
                    if ($dt == $fdt){
                        $res[$cal_code][] = $rec;
                        break;
                    }
                } else
                    $res[$cal_code][] = $rec;
            }
            $dt = date_add_day($dt, 1);
        }
        $dt = date_add_day($sdt, $rec['repeat_interval']*7);
    }
}

function count_book_request_by_user($id = 0){
    $result = 0;
    $query = "SELECT COUNT(*) FROM facility_book fb  WHERE fb.id_user = $id ";
    $rs = mysql_query($query);
    if ($rs) {
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_book_request_by_user($id, $start, $limit = 10){
    $result = array();
    $query = "SELECT *, location_name as facility_no   
                FROM facility_book_view fb 
                WHERE fb.id_user = $id 
                ORDER BY book_date DESC 
                LIMIT $start, $limit";
    
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    while ($rec = mysql_fetch_assoc($rs))
        $result[] = $rec;
    return $result;
}

function save_delete_book_reason($id, $user, $remark){
    $result = false;
    $query = "INSERT INTO facility_book_delete(id_book, id_user, delete_remark) 
                VALUE($id, $user, '$remark')";
    @mysql_query($query);
    $result = mysql_affected_rows() > 0;
    return $result;
}

function delete_book($id, $seldate, $id_user, $remark, $part=1)
{
    /*
    option: 
        1 delete for selected date
        2 delete start from selected date to the end
        3 delete completed event
    */
    $result = false;
    $book = get_booking_info($id);
    if ($book['repetition'] == REPEAT_NONE){
        delete_booking_instance($id);
        $query = "UPDATE facility_book SET status = 'DELETE' WHERE id_book = '$id'";
        @mysql_query($query);
        $result = mysql_affected_rows() > 0;
        if ($result)
            save_delete_book_reason($id, $id_user, $remark);
    } 
    else {
        // 1 - to date, 2 - to date and following, 3 - all in series
        $new_dttm = strtotime($seldate);
        if ($part == 2){
            // limit for original event to date, will stop recurring
            $query = "UPDATE facility_book SET dt_last = $new_dttm WHERE id_book = '$id'";
            @mysql_query($query);
            $result = mysql_affected_rows() > 0;
            $query = "DELETE FROM facility_book_instances WHERE id_book = '$id' && DATE_FORMAT(FROM_UNIXTIME(start), '%Y-%m-%d') >= '$seldate' ";
            @mysql_query($query);
       if ($result)
               save_delete_book_reason($id, $id_user, $remark);
        } else 
        if ($part == 1){
            /*
            $new_book = duplicate_book($id);
            //$new_dts = date('Y-m-d', $new_dttm);
            $dttm_diff = ($book['date_finish_t']-$book['date_start_t']);
            if ($dttm_diff>0) $dttm_diff = round($dttm_diff/60*60*24);
            $dttm_diff++;
            $query = "UPDATE facility_book SET date_start = DATE_ADD('$seldate', INTERVAL 1 DAY), date_finish = DATE_ADD('$seldate', INTERVAL $dttm_diff DAY) WHERE id_book = '$new_book'";
            @mysql_query($query);
            // limit for original event to date
            $query = "UPDATE facility_book SET repeat_until = DATE_SUB('$seldate', INTERVAL 1 DAY) WHERE id_book = '$id'";
            @mysql_query($query);
            */
            $query = "DELETE FROM facility_book_instances WHERE id_book = '$id' && DATE_FORMAT(FROM_UNIXTIME(start), '%Y-%m-%d') = '$seldate' ";
            @mysql_query($query);
            $result = mysql_affected_rows() > 0;
            if ($result)
               save_delete_book_reason($id, $id_user, $remark);
        } else 
        if ($part == 3){
            delete_booking_instance($id);
            $query = "UPDATE facility_book SET status = 'DELETE' WHERE id_book = '$id'";
            @mysql_query($query);
            $result = mysql_affected_rows() > 0;
            if ($result)
               save_delete_book_reason($id, $id_user, $remark);
        }
    }    

    return $result;
}

function cancel_book($id, $id_user, $remark)
{
    $result = false;
    $query = "UPDATE facility_book SET status = 'CANCEL' WHERE id_book = '$id'";
    @mysql_query($query);
    if (mysql_affected_rows()>0){
        $remark = mysql_escape_string($remark);
        $query = "INSERT INTO facility_book_cancel(id_book, id_user, cancel_remark) 
                    VALUES($id, $id_user, '$remark')";
        @mysql_query($query);
        $result = true;
    }
    return $result;
}

function duplicate_book($id)
{
    $query = "INSERT INTO facility_book(book_date, id_user, id_facility, date_start, date_finish, time_start, time_finish, 
                fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, purpose, remark, status)
                SELECT now(), id_user, id_facility, date_start, date_finish, time_start, time_finish, 
                fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, purpose, remark, status 
                FROM facility_book WHERE id_book = $id"; 
    mysql_query($query);
    if (mysql_affected_rows()>0)
        return mysql_insert_id();
    return false;
}


function save_booking_instances_temp($instances){
	//rror_log('save_booking_instances_temp call:'.implode('|',$instances));
    if (is_array($instances)){
        $values = array();
        foreach($instances as $rec){
            $values[] = "($rec[id_book], $rec[start], $rec[end])";
        }

        //error_log('values:' . implode('#', $values));
        if (count($values)>0){
        	$id_book = $instances[0]['id_book'];
        	$table_name = 'temp_book_instances_'.$id_book;
			$query =<<<QUERY
				CREATE TEMPORARY TABLE IF NOT EXISTS `$table_name` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `id_book` int(11) NOT NULL,
				  `start` int(11) NOT NULL,
				  `end` int(11) NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `book_start` (`id_book`,`start`)
				);
QUERY;
			mysql_query('DROP TABLE IF EXISTS '. $table_name);
			mysql_query($query);
            //error_log( $query.mysql_error());
            $query = 'INSERT INTO '.$table_name.'(id_book, start, end) VALUES ';
            $query .= implode(', ', $values);
            mysql_query($query);
            //error_log( $query.mysql_error());
            /*
            if (mysql_affected_rows()>0){
                $query = "UPDATE facility_book ce 
                            SET dt_instance = (SELECT MAX(end) FROM facility_book_instances cei WHERE cei.id_book = $id_book) 
                            WHERE ce.id_book = $id_book";
                mysql_query($query);
            }
            */
        }
    }
    //error_log('SAVE:'.$query.mysql_error());
}

function generate_booking_instances_to_check_conflict($book, $start = 0, $end = 0){
    //error_log('generate_booking_instances_to_check_conflict call:');
    $result = array();
    if (empty($book)) return false;
    
    $dtend = $book['dt_last'];
    if (empty($dtend)||$dtend ==0) $dtend = $end;
    //error_log( date("Y-m-d",$start )." . ".date("Y-m-d",$end)." . ".date("Y-m-d",$dtend)."  <br>");
	//error_log('book:'.implode('|', $book));
    if ($dtend > $start) { // process active book in selected range
        switch ($book['repetition']) { 
        case REPEAT_DAILY:
            get_booking_daily($result, $book, $start, $end);
            break;
        case REPEAT_WEEKLY:
            get_booking_weekly($result, $book, $start, $end);
            break;
        case REPEAT_MONTHLY:
            get_booking_monthly($result, $book, $start, $end, $init);
            break;
        case REPEAT_NONE:
            $rec['id_book']  = $book['id_book'];
            $rec['start']  = $book['dt_start'];
            $rec['end']  = $book['dt_end'];
            $result[] = $rec;
			//error_log('repeat none:'. implode('|', $rec));
        }
        save_booking_instances_temp($result);
    }
}

function check_conflict_book($data, $sdate,  $fullday = false)
{
	
	/*
	case: 
		- non-repeated booking, particular or full day
		- repeated booking
			- daily
			- weekly 
			- monthly
		
			
		- generate instances and stores into temporary table
		- compares temporary instances to main instances
		- if there is conflict occures
		
	*/
    $fmt = '%Y%m%d';
    $books = array();
	
    $data['id_book'] = !empty($data['_id']) ? $data['_id'] :time();
    $id_facility = $data['_facility'];
	$num_of_days = 30 * 20;
	$instance_start = $data['dt_start'];
	$instance_end = date_add_day($instance_start, $num_of_days);
	generate_booking_instances_to_check_conflict($data, $instance_start, $instance_end, true);    
	$table_name = 'temp_book_instances_'.$data['id_book'];
	/*
    $query = 'SELECT * FROM facility_book_instances fbi, facility_book fb WHERE '; 
				
    if ($fullday)
    	$query .= " DATE_FORMAT( FROM_UNIXTIME(fbi.start), '$fmt') = DATE_FORMAT(FROM_UNIXTIME($sdate), '$fmt')  ";
	else
		$query .= " fbi.start <= $dt  AND fbi.end >= $dte  AND fbi.id_book = fb.id_book ";
	$query .= " AND fbi.id_book = fb.id_book AND fb.status IN ('BOOK', 'COMMENCE') AND fb.id_facility = $id_facility 
					ORDER BY fb.book_date ASC";
	$rs = mysql_query("Select * from $table_name");
	while($rec = mysql_fetch_assoc($rs))
	error_log(implode('|', $rec));
	*/

    $query = 'SELECT COUNT(*) FROM '.$table_name.' tbi, facility_book_instances fbi  
    			LEFT JOIN facility_book fb ON fb.id_book = fbi.id_book WHERE '; 
	/*
	if ($data['repetition'] == 'NONE'){
		$query .= " fbi.start < tbi.start+1  AND fbi.end > tbi.end-1  ";
	} else
	*/
    {
		if ($fullday)
			$query .= " DATE_FORMAT( FROM_UNIXTIME(fbi.start), '$fmt') = DATE_FORMAT(FROM_UNIXTIME(tbi.start), '$fmt')  ";
		else
			$query .= " !((fbi.start > tbi.end-1) OR (fbi.end < tbi.start+1)) ";
			//$query .= " ((fbi.start < tbi.start+1  AND fbi.end > tbi.start-1) OR (fbi.start < tbi.end-1  AND fbi.end > tbi.end-1)) ";
	}
	$query .= " AND fbi.id_book = fb.id_book AND fb.status IN ('BOOK', 'COMMENCE') AND fb.id_facility = $id_facility";
	if (!empty($data['_id']))
		$query .= ' AND fbi.id_book!=tbi.id_book ';

	$rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	$result = $rec[0];
	//error_log( $query.mysql_error().' result: ' .$result);       
	
    return $result>0;
}

function save_booking($data){
    $_id = 0;
	//error_log("awal: $data[dt_start], $data[dt_end] ");
	/*
	if ($data['fullday']){
		$s = date('Y-m-d', $data['dt_start']);
		$data['dt_start'] = strtotime($s);
		$s = date('Y-m-d', $data['dt_end']);
		$data['dt_end'] = strtotime($s) + 86399; // a day less 1''
	}
	*/
    $query = "INSERT INTO facility_book(book_date, id_user, id_facility, dt_start, dt_end, duration, 
                fullday, repetition, `interval`, dt_last, wd_start, purpose, remark, status)
                VALUE (UNIX_TIMESTAMP(), '$data[userid]', $data[_facility], $data[dt_start], $data[dt_end], $data[duration], 
                $data[fullday], '$data[repetition]', $data[interval], $data[dt_last], '$data[wd_start]', 
                '$data[purpose]', '$data[remark]', '$data[status]')"; 
    mysql_query($query);
    if (mysql_affected_rows()>0){
        $_id = mysql_insert_id();
        //$num_of_days = ($data['repetition'] == 'MONTHLY') ? 90 : 30;
        $num_of_days = 30 * 20;
        $instance_start = $data['dt_start'];
        $instance_end = date_add_day($instance_start, $num_of_days);
        generate_booking_instances($_id, $instance_start, $instance_end, true);    
    }
    return $_id;
}

function generate_booking_instances($id, $start = 0, $end = 0){
    $result = array();
    $book = get_booking_info($id);    
    if (empty($book)) return false;
    
    $dtend = $book['dt_last'];
    if (empty($dtend)||$dtend ==0) $dtend = $end;
    //error_log( "generate_booking_instances:  $id . ".date("Y-m-d",$start )." . ".date("Y-m-d",$end)." . ".date("Y-m-d",$dtend)."  <br>");

    if ($dtend > $start) { // process active book in selected range
        switch ($book['repetition']) { 
        case REPEAT_DAILY:
            get_booking_daily($result, $book, $start, $end);
            break;
        case REPEAT_WEEKLY:
            get_booking_weekly($result, $book, $start, $end);
            break;
        case REPEAT_MONTHLY:
            get_booking_monthly($result, $book, $start, $end, $init);
            break;
        case REPEAT_NONE:
        	
            $rec['id_book']  = $book['id_book'];
            $rec['start']  = $book['dt_start'];
            $rec['end']  = $book['dt_end'];
            $result[] = $rec;
            /*
			$dt_start = $book['dt_start'];
			$dt_end = $book['dt_end'];
            if ($book['fullday']){
            	$facility = get_facility($book['id_facility']);
            	$dsstart = date('Y-m-d ', $dt_start).$facility['time_start'];
            	$dsend =  date('Y-m-d ', $dt_end).$facility['time_end'];
            	$book['dt_start'] = strtotime($dsstart);
            	$book['dt_end'] = strtotime($dsend);
            } else {
            	$tsstart = date('H:i:s', $dt_start).$dt_start;
            	$tsend =  date('H:i:s', $dt_end).$dt_end;
            	$dsstart = date('Y-m-d ', $dt_start).$time_start;
            	$dsend =  date('Y-m-d ', $dt_end).$time_end;
            	//$book['dt_start'] = strtotime($dsstart);
            	$book['dt_end'] = strtotime($dsend);
            	
            }
            $book['dt_last'] = $book['dt_end'];
            get_booking_daily($result, $book, $start, $end);
            */
			//error_log('repeat none:'. implode('|', $rec));
        }
        save_booking_instances($result);
    }
}

function save_booking_instances($instances){
	//error_log('save_booking_instances call:'.implode('|',$instances));
    if (is_array($instances)){
        $values = array();
        foreach($instances as $rec){
            $values[] = "($rec[id_book], $rec[start], $rec[end])";
        }
        //error_log('values:' . implode('#', $values));
        if (count($values)>0){
            $id_book = $instances[0]['id_book'];
            $query = 'INSERT INTO facility_book_instances(id_book, start, end) VALUES ';
            $query .= implode(', ', $values);
            mysql_query($query);
            //error_log( $query.mysql_error());
            if (mysql_affected_rows()>0){
                $query = "UPDATE facility_book ce 
                            SET dt_instance = (SELECT MAX(end) FROM facility_book_instances cei WHERE cei.id_book = $id_book) 
                            WHERE ce.id_book = $id_book";
                mysql_query($query);
            }
        }
    }
    //error_log('SAVE:'.$query.mysql_error());
}

function get_latest_booking_instance($id){
    $query = "SELECT MAX(end) FROM facility_book_instances WHERE id_book = $id";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    return $rec[0];
}

function delete_booking_instance($id, $dts = 0, $dte = 0){
    $query = "DELETE FROM facility_book_instances WHERE id_book = $id ";
    if ($dts > 0){
        if ($dte > 0)
            $query .= " AND start >= $dts AND end <= $dte"; // range of instance
        else if ($dte == -1)
            $query .= " AND start >= $dts "; // selected intance and future
        else
            $query .= " AND start = $dts"; // an intance
    }
    //echo $query;
    mysql_query($query);
}

function get_booking_daily(&$res, $rec, $dtstart, $dtend, $fdt = 0){
	//error_log('get_booking_daily call:');
    $dt = $rec['dt_start'];
    $dte = $rec['dt_end'];
    $delta = ($dte-$dt);
    
    //if ($delta>86400) $delta /= (24 * 60 * 60 ); 
    if ($dt < $dtstart) $dt = $dtstart;
    $interval = !empty($rec['interval']) ? $rec['interval'] : 1;
    $repeat_until = $rec['dt_last'];
    //error_log("$dt - $dte - $delta - $dtstart - $repeat_until");
    if ($repeat_until > 0 && $repeat_until < $dtend) $dtend = $repeat_until;
    while ($dt <= $dtend){
        $duration = date_add_sec($dt, $delta);
        $rec['start'] = $dt;
        $rec['end'] = $duration;                
        $res[] = $rec;
        $dt = date_add_day($dt, $interval);
    }
}


function get_booking_monthly(&$res, $rec, $dtstart, $dtend, $init = false){
	//error_log('get_booking_monthly call:');
    $dt = $rec['dt_start'];
    $dte = $rec['dt_end'];
    $delta = ($dte-$dt) / (24 * 60 * 60 );
    //if ($delta>86400) $delta /= (24 * 60 * 60 ); 
    $dom = date('d', $dt);
    $interval = !empty($rec['interval']) ? $rec['interval'] : 1;
    $repeat_until = $rec['dt_last'];
    if ($repeat_until > 0 && $repeat_until < $dtend) $dtend = $repeat_until;
    if ($dt < $dtstart) $dt = $dtstart;
    // make sure start date is selected date of month
    $dt = mktime(date('H', $dt), date('i', $dt), date('s', $dt), date('m', $dt), $dom, date('Y', $dt));
    //echo date('Y-m-d', $dt) . '--'.date('Y-m-d', $dtend)."\n<br>";
    if (!$init)
        $dt = date_add_months($dt, $interval); // if not the initial generating, then continual
    //echo date('Y-m-d', $dt) ."\n<br>";
    while ($dt <= $dtend){
        $sdt = $dt;
        $duration = date_add_sec($dt, $delta);
        $rec['start'] = $dt;
        $rec['end'] = $duration;                
        $res[] = $rec;
        $dt = date_add_months($sdt, $interval);
    }
}

function get_booking_weekly(&$res, $rec, $dtstart, $dtend){
	//error_log('get_booking_weekly call:');
    $dt = $rec['dt_start'];
    $dte = $rec['dt_end'];    
    $delta = ($dte - $dt);
    //if ($delta>86400) $delta /= (24 * 60 * 60 ); 

    $interval = !empty($rec['interval']) ? $rec['interval'] : 1;
    $repeat_until = $rec['dt_last'];
    if ($repeat_until > 0 && $repeat_until < $dtend) $dtend = $repeat_until;
    
    if ($dt < $dtstart) $dt = $dtstart;
    $dw = date('w', $dt);
    
	//error_log('wd_start:'. $rec['wd_start']);
    if (!empty($rec['wd_start']))
        $options = explode(',', $rec['wd_start']);
    else $options = array($dw);
	//error_log('wd_start:'.implode('#', $options));
    while ($dt <= $dtend){
        $sdt = $dt;
        $long  = date_add_day($dt, 6);
        while ($dt <= $long && $dt <= $dtend){
            $dw = date('w', $dt);
            if (in_array($dw, $options)){
                $duration = ($delta == 0) ? $dt : date_add_sec($dt, $delta);
                $rec['start'] = $dt;
                $rec['end'] = $duration;                
                $res[] = $rec;
                //echo date('Y-m-d', $dt) ." - $dw - $delta #$rec[id_event]<br>\n";
            }
            $dt = date_add_day($dt, 1);
        }
        $dt = date_add_day($sdt, $interval*7);
    }
}


function get_facility_fixed($start = 0, $limit = 10){
	$result = array();
	$query = "SELECT st.id_trans, st.id_location, l.location_name, st.id_class, st.status, st.start_date, st.end_date ";
	$query .="FROM students_trans st, location l where st.id_location=l.id_location order by id_trans asc LIMIT $start, $limit ";
	$rs = mysql_query($query);
	error_log(mysql_error().$query);
    if ($rs && (mysql_num_rows($rs)>0)){
		$i = 0;
        while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;
    }
    return $result;
}

function count_facility_fixed(){
    $result = 0;
    $query  = "SELECT count(id_trans) FROM students_trans";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_location_with_fixed_item_list()
{
	$data = array();
    $query  = "SELECT ffi.id_facility, location_name
                FROM (SELECT id_facility, COUNT(id_item) total_item FROM facility_fixed_item GROUP BY id_facility) ffi
                LEFT JOIN location l ON ffi.id_facility = l.id_location 
                WHERE total_item > 0";
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs)
        while ($rec = mysql_fetch_row($rs))
            $data[$rec[0]] = $rec[1];
    return $data;
}

function fixed_item_list($_facility = 0)
{
    $items = array();
    $query = "SELECT f.id_item, f.register_number, i.asset_no 
                FROM facility_fixed_item f
                LEFT JOIN item i ON f.id_item = i.id_item 
                WHERE f.id_facility = '$_facility' 
                ORDER by f.register_number ASC"; 
    $rs = mysql_query($query);
    if ($rs)
        while ($rec = mysql_fetch_assoc($rs))
            $items[$rec['register_number']] = $rec;
    return $items;
}

function class_student_list($_class = null)
{
		$students = array();
		$query = "SELECT s.id_student, s.register_number, s.full_name 
					FROM students s 
					WHERE s.class = '$_class' AND s.id_student NOT IN (SELECT id_student FROM students_trans_detail std LEFT JOIN students_trans st ON std.id_trans=st.id_trans WHERE st.status=0)
					ORDER BY s.register_number";
		$rs = mysql_query($query);
		if ($rs)
			while ($rec = mysql_fetch_assoc($rs)){
				$students[$rec['register_number']] = $rec;
			}
		return $students;
	
}


function get_class_list($swap = false, $lowercase = false)
{
	$data = array();
    $query  = "SELECT DISTINCT class FROM students ORDER BY class";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[0] = strtolower($rec[0]);
            $data[$rec[0]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[0];
    return $data;
}

function get_list_room_have_template(){
	$query = "SELECT ffi.id_facility, ffi.template, l.location_name FROM facility_fixed_item ffi
			LEFT JOIN  location l ON l.id_location = ffi.id_facility
			GROUP BY id_facility";
	$mysql_query = mysql_query($query);
	
	while($rs = mysql_fetch_assoc($mysql_query)){
		$data[] = array($rs['location_name'], $rs['template']);
		
	}
	return $data;
}


function get_template($_facility){
$query = "SELECT MAX(register_number) max_regno, MAX(template) as template FROM facility_fixed_item WHERE id_facility = '$_facility'";
		$rs = mysql_query($query);
		$rec = mysql_fetch_assoc($rs);
		$max_regno = $rec['max_regno'];
		$choose_template = $rec['template'];
		return array($choose_template, $max_regno);
}

function checkNRICStudent($nric){
	$query = mysql_query("SELECT * FROM students WHERE nric = '".$nric."' AND active = 1");
	$data = mysql_fetch_array($query);
	$row = mysql_num_rows($query);
	
	if($row > 0){
		return array($data['full_name'], $data['nric'], $data['id_student']);
	} else {
		return 0;
	}
}

function checkStudentWhetherLocationUse($id_student){
	$query = "
		SELECT t . * , i.asset_no, s.full_name, st.end_date
		FROM students_trans_detail t
		LEFT JOIN item i ON i.id_item = t.id_item
		LEFT JOIN students s ON s.id_student = t.id_student
			LEFT JOIN students_trans st ON st.id_trans = t.id_trans
		WHERE s.id_student = ".$id_student." AND st.end_date = '0000-00-00 00:00:00'
	";

	$mysql_query = mysql_query($query);
	$row = mysql_num_rows($mysql_query);
	
	if($row > 0){ return 1; } else { return 0; }

}