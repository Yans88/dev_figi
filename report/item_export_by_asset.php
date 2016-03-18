<?php
if (!defined('FIGIPASS')) exit;

$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : 'asset_no';
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : '';
$dept = defined('USERDEPT') ? USERDEPT : 0;

$total_item = count_item($_searchby, $_searchtext, $dept, true);
$sort_order = $order_status[$_orderby];
$_limit = $total_item;
$_start = 0;

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=item_list_filtered_by_{$_searchby}-$_searchtext.csv");
echo "Asset No,Serial No,Category,Brand,Model No,Purchase Price,Purchase Date,Warranty End Date\n";
if ($total_item > 0) {
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept, true);
    while ($rec = mysql_fetch_array($rs))
    {
    echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[category_name]\",\"$rec[brand_name]\",\"$rec[model_no]\",\"$rec[cost]\",\"$rec[date_of_purchase]\",\"$rec[warranty_end_date]\"\n";
    }
}
ob_end_flush();
exit;
?>