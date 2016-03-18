<?php 
//session_start();
//ob_start();

include '../util.php';
include '../common.php';
include '../user/user_util.php';
include '../authcheck.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_del = isset($_POST['del']) ? $_POST['del'] : 0;
$_msg = null;
$dept = USERDEPT;

$result = 0;
if (($_del == 1) && ($_id > 0)){
	$query = "DELETE FROM fault_category WHERE id_category = $_id";
	$query = "DELETE FROM fault_category_department WHERE id_category = $_id AND id_department = $dept";
	mysql_query($query);
	error_log(mysql_error().$query);	
	$result = mysql_affected_rows();
	user_log(LOG_DELETE, 'Category '. $_name. '(ID:'. $_id.') deleted.');
} else
if (!empty($_name)) {
    /*
	if ($_id > 0) 
		$query  = "UPDATE fault_category SET category_name = '$_name' WHERE id_category = $_id";
	else
		$query  = "INSERT INTO fault_category(category_name, id_department) VALUES('$_name', $dept)";
    */
    $query  = "REPLACE fault_category(id_category, category_name, id_department) VALUE($_id, '$_name', $dept)";	
	mysql_query($query);
    //error_log( mysql_error().$query);
    if ($_id == 0){
    	$_id = mysql_insert_id();
    	$query = "INSERT INTO fault_category_department VALUE($dept, $_id)";
    	mysql_query($query);
    }
	$result = mysql_affected_rows();
	user_log(LOG_UPDATE, 'Update category '. $_name. '(ID:'. $_id.')');
}
echo $result;
?>