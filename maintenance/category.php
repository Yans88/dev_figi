<?php
if (!defined('FIGIPASS')) exit;
require 'maintenance_util.php';

if (!empty($_POST) || !empty($_GET)){
	ob_clean();
	$result = null;
	if (!empty($_POST['create'])){
		$ok = create_category($_POST);
		$result = ($ok) ? 'CREATE:OK' : 'CREATE:ERROR';
	} else 
    if (!empty($_POST['clone'])){
		$ok = clone_category($_POST);
		$result = ($ok) ? 'CLONE:OK:'.$ok : 'CLONE:ERROR';
	} else
	if (!empty($_POST['update'])){
		$ok = update_category($_POST);
		$result = ($ok) ? 'UPDATE:OK' : 'UPDATE:ERROR';
	} else
	if (!empty($_POST['assign'])){
		$ok = assing_item_to_category($_POST);
        if ($ok > 0) $result = 'ASSIGN:OK';
        else if ($ok == -1) $result = 'ASSIGN:EXISTS';
        else $result = 'ASSIGN:ERROR';
        error_log(serialize($_POST));
	} else
	if (!empty($_POST['dele'])){
		$ok = dele_category($_POST);
		$result = ($ok) ? 'DELETE:OK' : 'DELETE:ERROR';
	} else
	if (!empty($_GET['get'])){
		$data = get_category($_GET['get']);
		$result = json_encode($data);
	} 
	echo $result;
	ob_end_flush();
	exit;
} 

function create_category($data)
{
	$category_name = mysql_real_escape_string($data['category_name']);
	$enabled = !empty($data['enabled']) ? 1 : 0;
	$linkable_item = !empty($data['linkable_item']) ? 1 : 0;
	$id_department = USERDEPT;
	$created_by = USERID;
	$query = "INSERT INTO checklist_category(category_name, linkable_item, enabled, id_department, created_by, created_on) ";
	$query .= " VALUE('$category_name', $linkable_item, $enabled, $id_department, $created_by, now())";
	$rs = mysql_query($query);
	//error_log(mysql_error().$query);
	return mysql_affected_rows();
}

function clone_category($data)
{
    $id_category = 0;
    $rec = get_category($data['cat']);
    if (!empty($rec)){
        $category_name = $rec['category_name'].' Copy';
        $enabled = $rec['enabled'];
        $linkable_item = $rec['linkable_item'];
        $id_department = USERDEPT;
        $created_by = USERID;
        $query = "INSERT INTO checklist_category(category_name, linkable_item, enabled, id_department, created_by, created_on) ";
        $query .= " VALUE('$category_name', $linkable_item,  $enabled, $id_department, $created_by, now())";
        $rs = mysql_query($query);
        //error_log(mysql_error().$query);
        if ($rs && mysql_affected_rows()>0){
            $id_category = mysql_insert_id();
            
            // clone the checklist items
            if ($id_category>0){
                $values = array();
                $query = "SELECT * FROM checklist_item WHERE id_category = $data[cat]";
                $rs = mysql_query($query);
                if ($rs){
                    while ($row = mysql_fetch_assoc($rs)){
                        $values[] = "(0, '$row[item_name]', $row[item_type], $id_category)";
                    }
                    if (count($values)>0){
                        $query = "REPLACE INTO checklist_item (id_item, item_name, item_type, id_category) VALUES ";
                        $query .= implode(', ', $values);
                        mysql_query($query);
                        error_log(mysql_error().$query);
                    }
                }
            }
        }
    }
	return $id_category;
}

function update_category($data)
{
	$enabled = !empty($data['enabled']) ? 1 : 0;
	$linkable_item = !empty($data['linkable_item']) ? 1 : 0;
	$category_name = mysql_real_escape_string($data['category_name']);
	$query = "UPDATE checklist_category 
                SET category_name = '$category_name', linkable_item = $linkable_item, enabled = $enabled  
                WHERE id_category='$data[id_category]'";
	mysql_query($query);
	//error_log(mysql_error().$query);
    if ($linkable_item == 0) { // unlink
        $query = "DELETE FROM checklist_checking_equipment WHERE id_category = $data[id_category]";
        mysql_query($query);
    }
	return mysql_affected_rows();
}

function assing_item_to_category($data)
{
    $result = 0;
	$id_item = !empty($data['id_item']) ? $data['id_item'] : 0;
    // check if exist
    $rs = mysql_query('SELECT COUNT(*) FROM checklist_checking_equipment WHERE id_item = '.$id_item);
    $rec = mysql_fetch_row($rs);
    //error_log(mysql_error());
    $is_exists = $rec[0] > 0;
    if (!$is_exists){
        $id_location = $data['id_location'];
        $id_category = $data['id_category'];
        $query = "REPLACE INTO checklist_checking_equipment(id_location, id_category, id_item) 
                    VALUE($id_location, $id_category, $id_item)";
        mysql_query($query);
        //error_log(mysql_error().mysql_affected_rows());
    
        $result = mysql_affected_rows();
    } else $result = -1;
	return $result;
}

function dele_category($data)
{
	$category = mysql_real_escape_string($data['category']);
	$query = "DELETE FROM category WHERE year=$data[year] AND category='$category'";
	mysql_query($query);
	//error_log(mysql_error().$query);
	return mysql_affected_rows();
}

function category_year_list()
{
	$result = array();
	$query = "SELECT DISTINCT year FROM category";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0)
		while($rec = mysql_fetch_row($rs))
			$result[$rec[0]] = $rec[0];
	return $result;

}

function unassign_student_from_category($data)
{
	$result = 0;
	if (!empty($data['year']) && !empty($data['category']) && !empty($data['students'])){
		
		$category = mysql_real_escape_string($data['category']);
		$query = "DELETE FROM student_categoryes WHERE year=$data[year] AND category='$category' AND id_student IN ($data[students])";
		mysql_query($query);
		//error_log(mysql_error().$query);
		$result = mysql_affected_rows();

		if ($result>0){
			$nos = update_register_no($data['year'], $category);
			update_student_count($data['year'], $category, $nos);
		}
	}
	return $result;
}


