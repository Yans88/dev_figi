<?php
if (!defined('FIGIPASS')) exit; 





require 'maintenance_util.php';

if (!empty($_POST)){
	if (!empty($_POST['save'])){
        
		$new_checklist = $_POST['save']=='create';
        if ($new_checklist)
            $id_check = create_checklist($_POST);
        else
            $id_check = $_POST['id_check'];
		
        if ($id_check > 0)
            save_checklist_result($id_check, $_POST);
            
		
    }
}

echo "<div style='color:#FFF;'>";
$_act = @$_GET['act'];
if (empty($_act)) $_act = 'list';
$_path = 'maintenance/checking_'.$_act.'.php';
if (!file_exists($_path)) $_act = 'list';
$_path = 'maintenance/checking_'.$_act.'.php';
require $_path;
return;
echo "</div>";

function create_checklist($data)
{
	$now = date('Y-m-d H:i:s');
	$id_check = 0;
	$id_user = USERID;
    $id_term = !empty($data['id_term']) ? $data['id_term'] : 0; 
    $id_location = !empty($data['id_location']) ? $data['id_location'] : 0;
	if ($id_location > 0){
        $query = "INSERT INTO checklist_checking(id_location, id_term, created_by, created_on, modified_by, modified_on) ";
        $query .= "VALUE($id_location, $id_term, $id_user, '$now', $id_user, '$now')";
        $rs = mysql_query($query);
        //echo mysql_error().$query;
        if ($rs)
            $id_check = mysql_insert_id();
    }
	return $id_check;
}

function save_checklist_result($id_check, $data)
{
	$now = date('Y-m-d H:i:s');
	$result = 0;
    $id_user = USERID;
    $values = array();
    $statuses = $data['status'];
    $remarks = $data['remarks'];
    $id_items = array();
    foreach($statuses as $id_item => $value){
        $remark = null;
        if (!empty($remarks[$id_item]))
            $remark = mysql_real_escape_string($remarks[$id_item]);
        if (is_array($value)) 
            $value = implode(':', $value);
        $values[] = "($id_check, $id_item, '$value', '$remark', $id_user, '$now')";
        $id_items[] = $id_item;
    }
    if (count($values) > 0){
        // clear up first
        if (count($id_items)>0){
            $id_item_list = implode(', ', $id_items);
            mysql_query("DELETE FROM checklist_checking_result WHERE id_check = $id_check AND id_item IN ($id_item_list)");
        }
        $query =  'INSERT INTO checklist_checking_result(id_check, id_item, result, remark, checked_by, checked_on) VALUES ';
        $query .= implode(', ', $values);
        mysql_query($query);
        //echo mysql_error().$query;
        $result = mysql_affected_rows();
    }
	return $result;
}

