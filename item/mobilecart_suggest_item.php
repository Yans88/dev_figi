<?php

include '../util.php';
include '../common.php';

$text = !empty($_POST['queryString']) ? $_POST['queryString'] : null;
$inputId = !empty($_POST['inputId']) ? $_POST['inputId'] : null;
$dept = !empty($_POST['deptId']) ? $_POST['deptId'] : 0;
$cat = !empty($_POST['catId']) ? $_POST['catId'] : 0;
$status_list = implode(', ', array(AVAILABLE_FOR_LOAN, STORAGE));

if ($text != null){
    $query = "SELECT asset_no, serial_no, id_item, category_name, brand_name 
                FROM item 
                LEFT JOIN category ON category.id_category = item.id_category 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE id_status in ($status_list)  AND (asset_no like '$text%' OR serial_no like '$text%') ";
    if ($cat > 0)
        $query  .= " AND id_category = $cat ";
    if ($dept > 0)
        $query  .= " AND item.id_department = $dept ";
    $query .= " ORDER BY asset_no, serial_no ASC LIMIT 10 ";
    $rs = mysql_query($query);
    
	//error_log($query.mysql_error());
    if ($rs && (mysql_num_rows($rs) > 0)){
        echo '<ul>';
        while ($rec = mysql_fetch_row($rs)){
			//error_log(serialize($rec));
            $what = "$rec[1] ($rec[0]), $rec[3], $rec[4]";
            $title = "$rec[1]|$rec[0]|$rec[2]|$rec[3]|$rec[4]";
            echo '<li onclick="fill(\''. $inputId . '\',\''.$title.'\', 1)">' . $what . '</li>';
        }
        echo '</ul>';
    }
}
?>
