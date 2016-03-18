<?php 
include '../util.php';
include '../common.php';
include '../user/user_util.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_type = isset($_POST['type']) ? $_POST['type'] : null;
$_dept = isset($_POST['dept']) ? $_POST['dept'] : 0;

$result = 0;
if (($_dept > 0) && ($_type != null) && ($_name != null)) {
    $query  = "REPLACE INTO category (id_category, category_name, category_type, id_department)
                VALUES ($_id, '$_name', '$_type', $_dept) ";
    mysql_query($query);
    
    $result = mysql_affected_rows();
    if ($_id == 0) {
        $_id = mysql_insert_id();
        user_log(LOG_CREATE, 'Add new category '. $_name. '(ID:'. $_id.')');
    } else
        user_log(LOG_UPDATE, 'Update category '. $_name. '(ID:'. $_id.')');
}
echo $result;
?>