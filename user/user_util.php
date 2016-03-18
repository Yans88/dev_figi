<?php

function get_user_list($swap = false, $username = false, $lowercase = false){
    $data = array();
    if ($username)
        $query = 'SELECT id_user, user_name FROM user ORDER BY full_name ASC';
    else
        $query = 'SELECT id_user, full_name FROM user ORDER BY full_name ASC';
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

function get_user_data($order_by = 'full_name', $sort_order = 'ASC', $start = 0, $limit = RECORD_PER_PAGE, $searchtext = null, $group = null, $dept=null){
    global $encryption;
    $data = array();
	$query = 'SELECT * 
                FROM user u 
                LEFT JOIN `group` g ON g.id_group = u.id_group 
                LEFT JOIN `department` d ON d.id_department = u.id_department ';
    
	
	if (!empty($searchtext))
        $query .= " WHERE (full_name like '%$searchtext%' OR user_name like '%$searchtext%') ";
	
	if($group > 1)
		$query .= " WHERE (g.id_group = '$group' OR g.id_group = '1' OR g.id_group = '4')";

	if(!empty($dept))
		$query .= " AND u.id_department = '$dept' ";
			
		//echo $query;
		
    $query .= " ORDER BY $order_by $sort_order ";
    $query .= " LIMIT $start, $limit";
	
	$rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs) > 0) {
    
        while ($rec = mysql_fetch_assoc($rs)){
            if (is_object($encryption)){
                $rec['user_name'] = $encryption->decode($rec['user_name']);
                //$rec['user_email'] = $encryption->decode($rec['user_email']);
                //$rec['contact_no'] = $encryption->decode($rec['contact_no']);
            }
            $data[] = $rec;
        }
    }
    return $data;
}

function get_user_id($name = ''){
    $data = 0;
	$query = 'SELECT id_user 
                FROM user u 
                WHERE user_name = "' .$name. '"';
	$rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)) {
        $rec = mysql_fetch_assoc($rs);
        $data = $rec['id_user'];
    }
    return $data;
}

function get_user_id_by_fullname($name = ''){
    $data = 0;
	$query = 'SELECT id_user 
                FROM user u 
                WHERE full_name = "' .$name. '"';
	$rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)) {
        $rec = mysql_fetch_assoc($rs);
        $data = $rec['id_user'];
    }
    return $data;
}

function get_user($id = 0){
    global $encryption;
    $data = array();
	$query = "SELECT * 
                FROM user u 
                LEFT JOIN `group` g ON g.id_group = u.id_group 
                LEFT JOIN `department` d ON d.id_department = u.id_department 
                WHERE id_user = '$id'";
	$rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)) {
        $data = mysql_fetch_assoc($rs);
        if (is_object($encryption)){
            $data['user_name'] = $encryption->decode($data['user_name']);
            //$data['user_email'] = $encryption->decode($data['user_email']);
            //$data['contact_no'] = $encryption->decode($data['contact_no']);
        }
    }
    return $data;
}

function get_user_by_nric($nric = ''){
    $data = array();
	$query = 'SELECT * 
                FROM user u 
                LEFT JOIN `group` g ON g.id_group = u.id_group 
                WHERE nric = "' .$nric . '"';
	$rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)) {
        $data = mysql_fetch_assoc($rs);
    }
    return $data;
}

function get_user_count($searchtext = null){
    $data = 0;
    $query = 'SELECT COUNT(id_user) FROM user';
    if (!empty($searchtext))
        $query .= " WHERE full_name like '%$searchtext%' OR user_name like '%$searchtext%' ";

	$rs = mysql_query($query);
    
	if  ($rec = mysql_fetch_row($rs))
		$data = $rec[0];
  return $data;
}

function get_group_data($start = 0, $limit = RECORD_PER_PAGE){
    $data = array();
	$query = "SELECT * FROM `group` LIMIT $start, $limit";
	$rs = mysql_query($query);
    if ($rs) {
        $num = mysql_num_rows($rs);
        for ($i=0; $i<$num; $i++){
            $rec = mysql_fetch_assoc($rs);
            $data[$i] = $rec;
        }
    }
    return $data;
}

function get_group_list($swap = false, $lowercase = false){
    $data = array();
	$query = "SELECT id_group, group_name FROM `group`";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0) {        
		while ($rec = mysql_fetch_assoc($rs))
			if ($swap){
                if ($lowercase)
                    $rec['group_name'] = strtolower($rec['group_name']);
				$data[$rec['group_name']] = $rec['id_group'];
			} else
				$data[$rec['id_group']] = $rec['group_name'];
    }
    return $data;
}

function get_group_count(){
    $data = 0;
    $query = 'SELECT COUNT(id_group) FROM `group`';
	$rs = mysql_query($query);
	if  ($rec = mysql_fetch_row($rs))
		$data = $rec[0];
  return $data;
}

function build_user_combo($selected = -1, $name = 'user_id') {
	
	return build_combo($name, get_user_list(), $selected);
}

function get_user_group_list($group = null) {
  $data = array();
	$query = 'SELECT id_group, group_name FROM `group`'; 
	
	if($group == 16){
		$query .= " WHERE id_group = '$group' OR  id_group = '1' OR id_group = '4' ";
	}
	
	$query .= ' ORDER BY group_name ASC ';
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
		$data[$rec[0]] =$rec[1];
  return $data;
}

function build_group_combo($selected = -1, $group) {
	
	return build_combo('id_group', get_user_group_list($group), $selected);
}

function get_privilege_list($_id = 0) {
  $privileges = array();
  /*
  if ($_id > 0) 
    $query = 'SELECT cp.privilege_id, privilege_name FROM category_privilege cp, privileges p 
              WHERE category_id=' . $_id . ' and p.privilege_id = cp.privilege_id';
  else
    $query = 'SELECT privilege_id, privilege_name FROM privileges';
  $rs = mysql_query($query);
  while ($rec = mysql_fetch_row($rs))
    $privileges[$rec[0]] = $rec[1];
    */
   return $privileges;
}

function build_privilege_combo($category_id = 0, $selected = -1) {
	
	return build_combo('privilege_id', get_user_category_list($category_id), $selected);
}

function build_department_combo($selected = -1) {
	
	return build_combo('id_department', get_department_list(), $selected);
}

function get_user_access_list($gid, $db = 0){
    $result = array();
    if (isset($_SESSION['figi_accesslist']) && ($db != 0))
        $result = unserialize($_SESSION['figi_accesslist']);
    else {
        $query = "SELECT a.*, p.id_module 
                  FROM access a 
                  LEFT JOIN page p ON p.id_page = a.id_page 
                  WHERE id_group = $gid";
        $rs = mysql_query($query);
        while ($rec = mysql_fetch_row($rs)) {
            $modacc = ($rec[2]==1) || ($rec[3]==1) || ($rec[4]==1) || ($rec[5]==1);
            if (!isset($result[$rec[6]]))
                $result[$rec[6]] = array();
            array_push($result[$rec[6]], $rec[0]. ',' . $rec[2] .','. $rec[3] .','. $rec[4] .','. $rec[5]);
            //$result[$rec[6]] = $rec[0]. ',' . $rec[2] .','. $rec[3] .','. $rec[4] .','. $rec[5];
        }        
    }
    return $result;
}

function get_accessible_page($gid, $db = 0){
    $result = array();
    if (isset($_SESSION['figi_accessiblepage']) && ($db != 0))
        $result = unserialize($_SESSION['figi_accessiblepage']);
    else {
        $query = "SELECT id_page, access_view, access_create, access_update, access_delete 
                  FROM access a 
                  WHERE id_group = $gid";
        $rs = mysql_query($query);
        while ($rec = mysql_fetch_row($rs)) {
            if (($rec[1]==1) || ($rec[2]==1) || ($rec[3]==1) || ($rec[4]==1))
				push($result, $rec[0]);
        }        
    }
    return $result;
}

function get_user_module_list($gid, $db = 0){
    $result = array();
    if (isset($_SESSION['figi_modlist']) && ($db != 0))
        $result = unserialize($_SESSION['figi_modlist']);
    else {
        $query = "SELECT DISTINCT(p.id_module), module_path
                  FROM access a 
				  LEFT JOIN page p ON p.id_page = a.id_page 
				  LEFT JOIN module m ON m.id_module = p.id_module
                  WHERE id_group = $gid AND module_active = 1 AND 
				  ((access_view = 1) OR (access_create = 1) OR (access_update = 1) OR (access_delete = 1)) ";
        $rs = mysql_query($query);
		//echo mysql_error().$query;
        while ($rec = mysql_fetch_row($rs))
			array_push($result, $rec[1]);
    }
	
    return $result;
}

function get_user_page_list($gid, $db = 0){
    $result = array();
    if (isset($_SESSION['figi_modlist']) && ($db != 0))
        $result = unserialize($_SESSION['figi_modlist']);
    else {
        $query = "SELECT a.*, page_name 
                  FROM access a 
				  LEFT JOIN page p ON p.id_page = a.id_page 
                  WHERE id_group = $gid ";
        $rs = mysql_query($query);
		//echo mysql_error().$query;
        while ($rec = mysql_fetch_assoc($rs))
			$result[$rec['page_name']] = array($rec['access_view'], $rec['access_create'], $rec['access_update'], $rec['access_delete']);
    }
    return $result;
}

function get_accessible_module($gid, $db = 0){
    $result = array();
    if (isset($_SESSION['figi_accessiblemodule']) && ($db != 0))
        $result = unserialize($_SESSION['figi_accessiblemodule']);
    else {
        $query = "SELECT DISTINCT(id_module)
                  FROM access a 
				  LEFT JOIN page p ON p.id_page = a.id_page 
                  WHERE id_group = $gid AND 
				  ((access_view = 1) OR (access_create = 1) OR (access_update = 1) OR (access_delete = 1))
				  ";
        $rs = mysql_query($query);
		//echo mysql_error().$query;
        while ($rec = mysql_fetch_row($rs))
			array_push($result, $rec[0]);
    }
    return $result;
}

function get_page_access($gid){
    $result = array();
    $query = "SELECT a.*
              FROM access a 
              WHERE id_group = $gid";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    while ($rec = mysql_fetch_row($rs)) {
        $result[$rec[0]] = array($rec[2], $rec[3], $rec[4], $rec[5]);
    }        
    return $result;
}

function get_page_privileges($gid = 0, $pid = 0 ){
    $result = array();
    $query = "SELECT access_view, access_create, access_update, access_delete 
              FROM access  
              WHERE id_group = $gid AND id_page = $pid";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
    while ($rec = mysql_fetch_row($rs)) {
        $result = array($rec[0], $rec[1], $rec[2], $rec[3]);
    }   
	
    return $result;
}

function get_module_id_by_name($name){
    $id = -1;
    $query = "SELECT id_module FROM module WHERE module_name = '$name'";
    $rs = mysql_query($query);
    if (mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $id = $rec[0];
    }
    return $id;
}

function get_page_id_by_name($name){
    $id = -1;
    $query = "SELECT id_page FROM page WHERE REPLACE(page_name, ' ', '') = '$name'";
	//echo mysql_error().$query;
    $rs = mysql_query($query);
    if (mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $id = $rec[0];
    }
    return $id;
}

function get_pages_id_by_name($name){
    $id = -1;
    $query = "SELECT id_page FROM page WHERE page_name = '$name'";
	//echo mysql_error().$query;
    $rs = mysql_query($query);
    if (mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $id = $rec[0];
    }
    return $id;
}

function can_access_module($module){
    if (!is_numeric($module))
        $module = get_module_id_by_name($module); 
	
    return can_access_module_id($module);
}

function can_access_module_id($module_id){
    $accesses = get_user_access_list(USERGROUP);   
    //print_r($accesses);
	return isset($accesses[$module_id]);
}

function can_access_page_id($page_id, $access = -1){
    $all_accesses = get_page_access(USERGROUP);
    $page_access = array_keys($all_accesses);
    //  print_r($page_access);echo '<br/>';
    if (in_array($page_id, $page_access))
        foreach ($all_accesses[$page_id] as $acc)
            if ($acc == 1) return true;
    return false;
}

function can_access_page($page, $access = -1){
    if (!is_numeric($page))
        $page = get_page_id_by_name($page);
    return can_access_page_id($page, $access);
}

function get_page_list($mod = 0){
    $result = array();
    $query = "SELECT id_page, page_name FROM page ";
    if ($mod > 0)
        $query .= " WHERE id_module = '$mod'";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs)){
        $result[$rec[0]] = $rec[1];
    }
    return $result;
}

function get_group_name($gid = 0){
    $result = null;
    $query = "SELECT group_name FROM `group` WHERE id_group = $gid";
    $rs = mysql_query($query);
    if (mysql_num_rows($rs)) {
		$rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_email_list(){
    $result = array();
    $query = "SELECT user_email, full_name FROM user ";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs)){
        $result[$rec[0]] = $rec[0];
    }
    return $result;
}

function user_log($activity, $description){
    $userid = !empty($_SESSION['figi_userid']) ? $_SESSION['figi_userid'] : 0;
    if ($userid > 0){
        $query = "INSERT INTO user_log(log_time, log_activity, log_description, log_by) 
                    VALUES(now(), '$activity', '$description', $userid)";
        mysql_query($query);
       // echo mysql_error();
    }
}


function get_user_logs($id = 0, $sort_order = 'ASC', $start = 0, $limit = RECORD_PER_PAGE){
    $data = array();
	$query = 'SELECT * 
                FROM user_log ul 
                WHERE log_by = ' . $id;
    $query .= " ORDER BY log_time $sort_order ";
    $query .= " LIMIT $start, $limit";
	$rs = mysql_query($query);
    if ($rs) {
        $num = mysql_num_rows($rs);
        for ($i=0; $i<$num; $i++){
            $rec = mysql_fetch_assoc($rs);
            $data[$i] = $rec;
        }
    }
    return $data;
}

function count_user_log($id = 0){
    $data = 0;
    $query = 'SELECT COUNT(log_time) FROM user_log WHERE log_by = '.$id;
	$rs = mysql_query($query);
	if  ($rec = mysql_fetch_row($rs))
		$data = $rec[0];
  return $data;
}

function export_csv_user($path = 'export.csv'){
	global $encryption;
	$fp = fopen($path, 'w');
	$header  = 'User ID, Full Name, User Name, Password, Contact No, Email, Department, Group, NRIC, Status';
	fputs($fp, $header."\r\n");
	$i = 0;  
	$query = "SELECT id_user,full_name,user_name,user_pass,contact_no,user_email,department_name,group_name,nric,user_active 
				FROM user 
				LEFT JOIN `group` ON `group`.id_group = user.id_group 
				LEFT JOIN `department` ON `department`.id_department = user.id_department 
				ORDER BY full_name ASC ";
	$rs = mysql_query($query);
	//echo mysql_error();
	if (mysql_num_rows($rs)) {   
		while ($rec = mysql_fetch_row($rs)){
			//array_shift($line_rec);
            if (is_object($encryption)){    
                $rec[2] = $encryption->decode($rec[2]); // username
                $rec[3] = '^v^'.$rec[3]; // add pattern for encrypted pass
                //$rec[4] = $encryption->decode($rec[4]); // contact
            }
			fputs($fp, implode(',', $rec) . "\r\n");
		}
	}
	fclose($fp);

}

function import_csv_user($path){
	global $encryption;
	$result = 0; // upload failed
	$groups = get_group_list(true,true);
	$departments = get_department_list(true,true);
	if (!empty($path) && file_exists($path)) {
		if (($fp = fopen($path, 'r')) !== FALSE){
			$cols = fgetcsv($fp, 512, ',');
			if (count($cols) == 10){
				while ($cols = fgetcsv($fp, 512, ',')){
                    $deptname = strtolower($cols[6]);
                    $grpname = strtolower($cols[7]);
                    $did = (isset($departments[$deptname])) ? $departments[$deptname] : 0;
                    $gid = (isset($groups[$grpname])) ? $groups[$grpname] : 0;
                    //if (preg_match('/^\((.+)\)$/', $cols[3], $matches) > 0){
                    if (substr($cols[3], 0, 3) == '^v^'){
                        $pass = "'" . substr($cols[3], 3) . "'" ;
                    } else
                        $pass = "md5('".$cols[3]."')";
                    if (is_object($encryption)){    
                        $cols[2] = $encryption->encode($cols[2]); // username
                        //$cols[5] = $encryption->encode($cols[5]); // email
                        //$cols[4] = $encryption->encode($cols[4]); // contact
                    }
                    $query  = 'INSERT IGNORE INTO user (full_name,user_name,user_pass,contact_no,user_email,id_department,id_group,nric,user_active) VALUES ';
					$query .= "('$cols[1]', '$cols[2]', $pass, '$cols[4]', '$cols[5]', '$did', '$gid', '$cols[8]', '$cols[9]')";
					mysql_query($query);
					//echo mysql_error() . $query;
					if (mysql_affected_rows() == 1)
						$result++;
				}
				if ($result == 0)
					$result = -3;
			} else // colums is mismatch
				$result = -1;
			fclose($fp);
		} else
			$result = -2; // system error, can't open the file		
	}
	return $result;
}

function count_department(){
	$result = 0;
	$query  = "SELECT count(*) FROM department ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_departments($sort = 'asc', $start = 0, $limit = 10){
	$query  = "SELECT * 
				FROM department 
				ORDER BY department_name $sort 
				LIMIT $start,$limit ";
	return mysql_query($query);
}

function get_department_list($swap = false, $lowercase = false){
	$data = array();
	$query  = "SELECT id_department, department_name FROM department ORDER BY department_name";
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

function authenticate($username, $userpass){
    global $encryption;
    $result = null;
    $username = $encryption->encode($username);
    $query = 'SELECT u.*, d.department_name, count(dm.id_department_parent) as other_department FROM user u 
				LEFT JOIN department d ON d.id_department = u.id_department
                LEFT JOIN department_member dm ON dm.id_department_parent = d.id_department
                WHERE user_name="'.$username.'" 
                AND user_pass = md5("'.$userpass.'") 
                AND user_active = 1';
				
				error_log($query);
    $res = mysql_query($query);  
    if ($res && (mysql_num_rows($res) > 0)){ 
        $result = mysql_fetch_assoc($res);
        $result['user_name'] = $encryption->decode($result['user_name']);
        //$result['user_email'] = $encryption->decode($result['user_email']);
        //$result['contact_no'] = $encryption->decode($result['contact_no']);
    }
    return $result;
}

function save_user($id, $data){
    global $encryption;
    $result = 0;
    // ecnrypt data
    $_username  = $data['user'];
    $_useremail = $data['email'];
    $_contact_no= $data['contact'];
    $active = ($data['status'] == 'active')  ? 1 : 0;

    if (is_object($encryption)){
        $_username  = $encryption->encode($_username);
        //$_useremail = $encryption->encode($_useremail);
        //$_contact_no= $encryption->encode($_contact_no);
    }
    if ($id == 0) {// new user
        $query = "INSERT INTO user(full_name, contact_no, user_email, id_group, user_active, nric, user_pass, user_name, id_department)
                  VALUES ('$data[name]', '$_contact_no', '$_useremail', '$data[id_group]', '$active', 
                  '$data[nric]', md5('$data[user_pass]'), '$_username', '$data[id_department]')";        
    } else { // edit user
        $query = "UPDATE user SET full_name = '$data[name]', contact_no = '$_contact_no',
                    user_email = '$_useremail', id_group = '$data[id_group]', user_active = '$active', nric = '$data[nric]',
                    id_department = '$data[id_department]'";
        if (!empty($data['user_pass']))
            $query .= ', user_pass=md5("'.$data['user_pass'].'") ';
        $query .= " WHERE id_user=" . $id;
    }
    mysql_query($query);
    //echo mysql_error();
    if (mysql_affected_rows() > 0){
        
        if ($id == 0) {// new user
            $id = mysql_insert_id();
            user_log(LOG_CREATE, 'Create user '. $data['name']. '(ID:'. $id.')');
        } else
            user_log(LOG_UPDATE, 'Update user '. $data['name']. '(ID:'. $id.')');
    }
    $result = $id > 0;
    return $result;
}


function get_name_department($id_dept){
$q = "SELECT * FROM department WHERE id_department = $id_dept";
$mysql_query = mysql_query($q);
$row = mysql_fetch_array($mysql_query);
return $row['department_name'];
}

function switch_department($dept, $id_group){
	$q = "SELECT id_department_parent, department.id_department, department.department_name, id_group 
FROM department_member 
LEFT JOIN department ON department.id_department = department_member.id_department_child
WHERE department_member.id_department_parent = $dept AND department_member.id_group= $id_group ORDER BY id_department";
	$mysql_query = mysql_query($q);
	$data = mysql_fetch_array($mysql_query);
	
	if($data['id_department_parent'] > 0){
	
		return $q;
	
	} else {
	
		$s = "SELECT * FROM department_member WHERE id_department_child = $dept AND id_group=$id_group";
		$t = mysql_query($s);
		$u = mysql_fetch_array($t);
		$id_dept = $u['id_department_parent'];
		$id_grp = $u['id_group'];
		
		$r = "SELECT id_department_parent, department.id_department, department.department_name, id_group 
FROM department_member 
LEFT JOIN department ON department.id_department = department_member.id_department_child
WHERE department_member.id_department_parent = $id_dept AND department_member.id_group= $id_grp ORDER BY id_department";
		
		return $r;//$mysql_query_r;
	}
}

function check_department($dept, $id_group){
	$q = "SELECT id_department_parent, department.id_department, department.department_name, id_group 
FROM department_member 
LEFT JOIN department ON department.id_department = department_member.id_department_child
WHERE department_member.id_department_parent = $dept AND department_member.id_group= $id_group ORDER BY id_department";
	$mysql_query = mysql_query($q);
	$data = mysql_fetch_array($mysql_query);
	
	if($data['id_department_parent'] > 0){
	
		return $q;
	
	} else {
	/*
		$s = "SELECT * FROM department_member WHERE id_department_child = $dept AND id_group=$id_group";
		$t = mysql_query($s);
		$u = mysql_fetch_array($t);
		$id_dept = $u['id_department_parent'];
		$id_grp = $u['id_group'];
		
		$r = "SELECT id_department_parent, department.id_department, department.department_name, id_group 
FROM department_member 
LEFT JOIN department ON department.id_department = department_member.id_department_child
WHERE department_member.id_department_parent = $id_dept AND department_member.id_group= $id_grp ORDER BY id_department";
		*/
		return "";//$mysql_query_r;
	}
}

function add_other_department_execute($parent, $child, $id_group){

	$check = "SELECT * FROM department_member WHERE id_department_parent = $parent AND id_department_child = $child AND id_group = $id_group";
	$mysql_check = mysql_query($check);
	$check_data = mysql_fetch_array($mysql_check);
	
	if($check_data['id_department_parent'] > 0){
		return "You have selected a dept which already existing in your access list. Please select a different dept. Thank You.";
	} else {
		$query = "INSERT INTO department_member (id_department_parent,id_department_child, id_group) VALUES ($parent, $child, $id_group)";
		$mysql_query = mysql_query($query);
		return  "Succcessfully Created. Thank You.";
	}

}
?>