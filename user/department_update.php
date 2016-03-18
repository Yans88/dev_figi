<?php 
include '../common.php';
include '../user/user_util.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_del = isset($_POST['del']) ? ($_POST['del']=='me') : false;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_msg = null;

$result = 0;
if ($_id > 0) {
    if ($_del){
        $query  = "DELETE FROM department WHERE id_department = $_id ";
        mysql_query($query);
        $result = mysql_affected_rows();
        user_log(LOG_DELETE, 'Delete department '. $_name . '(ID:'. $_id.')');
    } else {
        $query  = "UPDATE department 
                    SET department_name = '$_name' 
                    WHERE id_department = $_id ";
        mysql_query($query);
        $result = mysql_affected_rows();
        user_log(LOG_UPDATE, 'Update department '. $_name . '(ID:'. $_id.')');
    }
} 
echo $result;
?>