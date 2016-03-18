<?php
if (!defined('FIGIPASS')) exit;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_id = !empty($_GET['id_location']) ? $_GET['id_location'] : 0;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : 0;
$_store = !empty($_GET['id_store']) ? $_GET['id_store'] : 0;
$_dept = !empty($_GET['id_dept']) ? $_GET['id_dept'] : 0;
$_status = !empty($_GET['id_status']) ? $_GET['id_status'] : 0;
$model_no = !empty($_GET['model_no']) ? $_GET['model_no'] : '';
$dept = defined('USERDEPT') ? USERDEPT : 0;
$item_f = array('id_category'=>$_GET['id_category'],'model_no'=>$_GET['model_no'],'id_status'=>$_GET['id_status'],'id_department'=>$_dept,'id_location'=>$_id);
$_searchby = 'id_store';
$_searchtext = $_store;
$_limit = count_item($_searchby, $_searchtext);
$_start = 0;
$stores = get_stores_list();
$store_name = $stores[$_store];
ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"item_list_filtered_by_store-$store_name.csv\"");
echo "Asset No,Serial No,Location Name,Category Name,Department Name,Cost of Item, Purchase Date,Invoice Number, Status\n";
if ($_limit > 0) {
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept,false,$item_f);
    while ($rec = mysql_fetch_array($rs))
    {
    echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[location_name]\",\"$rec[category_name]\",\"$rec[department_name]\",\"$rec[cost]\",\"$rec[date_of_purchase_fmt]\",\"$rec[invoice]\",\"$rec[status_name]\"\n";
    }
}
ob_end_flush();
exit;
?>