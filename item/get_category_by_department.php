<?php

error_log(serialize($_POST));
include '../util.php';
include '../common.php';
include 'item_util.php';

$dept = !empty($_POST['queryString']) ? $_POST['queryString'] : 0;
$type = !empty($_POST['type']) ? $_POST['type'] : 0;

/** +here **/
$loanable = (strtolower($type) == 'loan') ? " AND loanable='1'" : '';

$type = (strtolower($type) == 'service') ? 'SERVICE' : 'EQUIPMENT';


if ($dept != null){
    $query = "SELECT dc.id_category, category_name, id_parent
                FROM department_category dc 
                LEFT JOIN category c ON c.id_category = dc.id_category 
                WHERE category_type = '$type' AND dc.id_department = $dept 
				$loanable
                ORDER BY category_name ASC ";
    $rs = mysql_query($query);
    //error_log(mysql_error().$query);
	/** +here */
	global $_QUERY_;
	$_QUERY_=$query;
	$data = get_available_category_parent($type, $dept, 1, true);
	$tree_in_array = categoriesToTree($data);
	echo build_option_tree($tree_in_array);
	/*
    if ($rs && (mysql_num_rows($rs) > 0)){
        while ($rec = mysql_fetch_row($rs)){
            echo '<option value="'. $rec[0] . '">' . $rec[1] . '</option>';
        }
    }
	*/
    //else echo '<option value=0>-- none --</option>';
}
?>
