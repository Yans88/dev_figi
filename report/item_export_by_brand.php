<?php
if (!defined('FIGIPASS')) exit;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_id = !empty($_GET['id_brand']) ? $_GET['id_brand'] : 0;
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_searchby = 'id_brand';
$_searchtext = $_id;
$_limit = count_item($_searchby, $_searchtext);
$_start = 0;
$brands = get_brand_list();
$brand_name = $brands[$_id];
ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=item_list_filtered_by_brand-$brand_name.csv");
echo "Asset No,Serial No,Model No,Purchase Price,Purchase Date,Warranty End Date,Status\n";
if ($_limit > 0) {
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
    while ($rec = mysql_fetch_array($rs))
    {
    echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[model_no]\",\"$rec[cost]\",\"$rec[date_of_purchase]\",\"$rec[warranty_end_date]\",\"$rec[status_name]\"\n";
    }
}
ob_end_flush();
exit;
?>