<?php
if (!defined('FIGIPASS')) exit;

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : '';
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_searchby = 'invoice';
$total_item = count_item($_searchby, $_searchtext, $dept);
ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=item_list_filtered_by_invoice_no-$_searchtext.csv");
echo "Asset No,Serial No,Category,Brand,Model No,Purchase Price,Purchase Date,Warranty End Date\n";
$_start = 0;
$_limit = $total_item;
if ($total_item > 0) {
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
    while ($rec = mysql_fetch_array($rs))
    {
    echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[category_name]\",\"$rec[brand_name]\",\"$rec[model_no]\",\"$rec[cost]\",\"$rec[date_of_purchase]\",\"$rec[warranty_end_date]\"\n";
    }
}
ob_end_flush();
exit;
?>