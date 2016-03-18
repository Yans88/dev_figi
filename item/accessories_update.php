<?php 
include '../util.php';
include '../common.php';
include '../user/user_util.php';

$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_name = isset($_POST['name']) ? $_POST['name'] : null;
$_cat = isset($_POST['cat']) ? $_POST['cat'] : null;
$_del = isset($_POST['del']) ? $_POST['del'] : 0;
$_msg = null;

$result = 0;

if ($_del == 1){
	if ($_id > 0){
		$query  = "DELETE FROM accessories WHERE id_accessory=$_id";
		mysql_query($query);
		$result = mysql_affected_rows();
		if ($result > 1)
			user_log(LOG_UPDATE, 'Delete accessory '. $_name. '(ID:'. $_id.')');

	}
} else {
	if ($_id > 0){
		$query  = "UPDATE accessories SET accessory_name = '$_name' WHERE id_accessory = $_id";
		mysql_query($query);
		if ($result > 0)
			user_log(LOG_UPDATE, 'Update accessory '. $_name. '(ID:'. $_id.')');
	} else {
		$query  = "INSERT INTO accessories VALUE($_id, '$_name', $_cat, 999)";
		mysql_query($query);
		$result = mysql_affected_rows();
		if ($result > 0){
			$_id = mysql_insert_id();
			user_log(LOG_UPDATE, 'Add new accessory '. $_name. '(ID:'. $_id.')');

			// re-order the order_no
			$query = "SELECT id_accessory FROM accessories WHERE id_category = $_cat ORDER BY order_no ASC";
			$rs = mysql_query($query);
			$accessories = array();
			while ($row = mysql_fetch_row($rs))
				$accessories[] = $row[0];
			if (count($accessories)>0){
				$order_no = 1;
				foreach($accessories as $id_accessory){
					$query = "UPDATE accessories SET order_no = $order_no WHERE id_accessory = $id_accessory";
					mysql_query($query);
					$order_no++;
				}
			}
		}
	}
}
echo $result;
?>
