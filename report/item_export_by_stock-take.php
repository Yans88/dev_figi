<?php
if (!defined('FIGIPASS')) exit;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_validation = !empty($_GET['status_take']) ? $_GET['status_take'] : 0;
$dept = defined('USERDEPT') ? USERDEPT : 0;

$typ = isset($_GET['typ']) ? true : false;

$_searchby = 'status_take';
$_searchtext = $_validation;
$_limit = count_item_stock_take($_searchby, $_searchtext, $dept, $typ);
$_start = 0;
$validationName = strtolower($_searchtext);
ob_clean();
header("Content-Type: text/csv");
if($typ){
	header("Content-Disposition: attachment; filename=rest_item_unstock_take.csv");
	echo "Asset No,Serial No,Model No,Status,Category,Brand\n";
}
else{
	header("Content-Disposition: attachment; filename=stock_take_report_by-$validationName.csv");
	echo "Asset No,Serial No,Model No,Status,Validation,Remarks,Checker\n";
}
	
if ($_limit > 0) {
    $rs = get_items_statustake($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept, $typ);
    while ($rec = mysql_fetch_array($rs))
    {
		if($typ){
			echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[model_no]\",\"$rec[status_name]\",\"$rec[category_name]\",\"$rec[brand_name]\"\n";
		}
		else{
			$validation = ucfirst(strtolower($rec['status_take']));
			echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[model_no]\",\"$rec[status_name]\",\"$validation\",\"$rec[remarks_take]\",\"$rec[user_name]\"\n";
		}
    }
}
ob_end_flush();
exit;
?>