<?php
if (!defined('FIGIPASS')) exit;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : date('M-Y');
$dept = defined('USERDEPT') ? USERDEPT : 0;

function count_item_tobe_condemn($My = null, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) FROM item i 
                LEFT JOIN category c ON i.id_category=c.id_category 
                WHERE category_type = 'EQUIPMENT' AND 
                DATE_FORMAT(DATE_ADD(i.date_of_purchase, INTERVAL c.condemn_period MONTH), '%b-%Y') = '$My' ";
    if ($dept > 0)
        $query .= " AND (c.id_department = $dept OR i.id_owner = $dept) ";
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_items_tobe_condemn($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $My = null, $dept = 0)
{
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name,
                DATE_FORMAT(DATE_ADD(item.date_of_purchase, INTERVAL category.condemn_period MONTH), '%d-%b-%Y') AS condemn_date,
                DATE_FORMAT(warranty_end_date, '%d-%b-%Y') AS warranty_end_date_fmt, 
                DATE_FORMAT(date_of_purchase, '%d-%b-%Y') AS purchase_date_fmt 
                FROM item 
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN department ON category.id_department = department.id_department 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                WHERE category_type = 'EQUIPMENT' AND   
                DATE_FORMAT(DATE_ADD(item.date_of_purchase, INTERVAL category.condemn_period MONTH), '%b-%Y') = '$My' ";
	if ($dept > 0)
		$query .= " AND (category.id_department = $dept OR item.id_owner = $dept) ";
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
    //echo $query.mysql_error();
	return $rs;
}

$_limit = count_item_tobe_condemn($_searchtext, $dept);
$_start = 0;
ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=item_list_filtered_by_condemn_date.csv");
echo "Asset No,Serial No,Category,Brand,Model No,Purchase Price,Purchase Date,Warranty End Date,Projected Condemn Date\n";
if ($_limit > 0) {
    $rs = get_items_tobe_condemn($_orderby, $sort_order, $_start, $_limit, $_searchtext, $dept);
    while ($rec = mysql_fetch_array($rs))
    {
    echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[category_name]\",\"$rec[brand_name]\",\"$rec[model_no]\",\"$rec[cost]\",\"$rec[date_of_purchase]\",\"$rec[warranty_end_date]\",\"$rec[condemn_date]\"\n";
    }
}
ob_end_flush();
exit;
?>