<?php
if (!defined('FIGIPASS')) exit;

$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_status = !empty($_GET['id_status']) ? $_GET['id_status'] : 0;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : 0;
$dept = defined('USERDEPT') ? USERDEPT : 0;

function count_item_by_category_status($category = 0, $status = 0, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) FROM item i 
                LEFT JOIN category c ON i.id_category=c.id_category 
                WHERE category_type = 'EQUIPMENT' ";
    if ($category>0) $query .= ' AND i.id_category = '.$category ;
    if ($status>0) $query .= ' AND id_status = '.$status;
    if ($dept>0) $query .= ' AND i.id_department = '.$dept;
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_items_by_category_status($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $category = 0, $status = 0, $dept = 0)
{
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
                FROM item 
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN department ON category.id_department = department.id_department 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                WHERE category_type = 'EQUIPMENT' ";
    if ($category>0) $query .= ' AND item.id_category = '.$category ;
    if ($status>0) $query .= ' AND item.id_status = '.$status;
    if ($dept>0) $query .= ' AND item.id_department = '.$dept;
    $query .= " ORDER BY $orderby $sort  ";//LIMIT $start,$limit ";
	$rs = mysql_query($query);
    //echo $query.mysql_error();
	return $rs;
}


$_limit = count_item_by_category_status($_category, $_status, $dept);
$_start = 0;
ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=item_list_filtered_by_category_and_status.csv");
echo "Asset No,Serial No,Category,Brand,Model No\n";
if ($_limit > 0) {
    $rs = get_items_by_category_status($_orderby, $sort_order, $_start, $_limit, $_category, $_status, $dept);
    while ($rec = mysql_fetch_array($rs))
    {
    echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[category_name]\",\"$rec[brand_name]\",\"$rec[model_no]\"\n";
    }
}
ob_end_flush();
exit;
?>