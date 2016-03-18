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
$_field = !empty($_GET['field']) ? explode(',',$_GET['field']) : array();
$dept = defined('USERDEPT') ? USERDEPT : 0;
$item_f = array('id_category'=>$_category,'id_store'=>$_store,'model_no'=>$model_no,'id_status'=>$_status,'id_department'=>$_dept);
$_searchby = 'id_location';
$_searchtext = $_id;
$_limit = count_item($_searchby, $_searchtext);
$_start = 0;
$locations = get_location_list();
$location_name = $locations[$_id];
ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"item_list_filtered_by_location-$location_name.csv\"");
// echo "Asset No,Serial No,Category Name,Status\n";
$head_field = array();
foreach($_field as $row){
	$imar = explode('|',$row);
	$head_field[] = $imar[1];
	$list_row[] = $imar[0];
}
echo implode(',',$head_field)."\n"; 
if ($_limit > 0) {
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept,false,$item_f);
    while ($rec = mysql_fetch_array($rs))
    {
		$texts= '';
		foreach($list_row as $row){
			$texts .= "\"$rec[$row]\"";
			$texts .= (end($list_row) !== $row) ? "," : "\n";
		}
		echo $texts;
		// echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[category_name]\",\"$rec[status_name]\"\n";
    }
}
ob_end_flush();
exit;
?>