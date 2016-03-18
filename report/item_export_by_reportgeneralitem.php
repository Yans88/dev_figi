<?php


if (!defined('FIGIPASS')) exit;

			  
$filterby = htmlspecialchars(($_GET['filterby']), ENT_QUOTES);
$column = htmlspecialchars(($_GET['column']), ENT_QUOTES);

$var_filter = explode(",", $filterby);
$data_department = $var_filter[0]; $render_id_department = explode('|', $data_department); 
$data_category = $var_filter[1] ;$render_id_category = explode('|', $data_category);
$data_store = $var_filter[2];$render_id_store = explode('|', $data_store);


$variable = explode(",", $column);
$department 			= $variable[0];
$category 				= $variable[1];
$brand_name 			= $variable[2];
$model_no				= $variable[3];
$status					= $variable[4];
$issued_to				= $variable[5];
$location			 	= $variable[6];
$cost					= $variable[7];
$purchase_date 			= $variable[8];
$warranty_end_date 		= $variable[9];
$project_comdemned_date	= $variable[10];
$invoice_no				= $variable[11];
$brief					= $variable[12];

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_id = !empty($_GET['id_location']) ? $_GET['id_location'] : 0;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : 0;
$_store = !empty($_GET['id_store']) ? $_GET['id_store'] : 0;
$_dept = !empty($_GET['id_department']) ? $_GET['id_department'] : 0;
$dept = defined('USERDEPT') ? USERDEPT : 0;
$_limit = RECORD_PER_PAGE;
$_start = 0;
$sort_order = $order_status[$_orderby];
if ($_changeorder)
    $sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;


ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"general_report_item.csv\"");
//echo "Asset No,Serial No,Location Name,Category Name,Department Name,Cost of Item, Invoice Number, Status\n";

?>
<?php
echo "Asset No, Serial No,";

if(!$department){} else { 
 if (SUPERADMIN){ 
	echo "Department,";
	 } 
 } 

 if(!$category){} else { 
	echo "Category,";
 } 
 if(!$brand_name){} else { 
echo "Brand,";
 } 
 if(!$model_no){} else { 
echo "Model No,";
 } 
 if(!$status){} else { 

echo "Status,";
 } 
 if(!$issued_to){} else { 

echo "Issued To,";
 } 
 if(!$location){} else { 

echo "Location,";
 } 
 if(!$cost){} else { 

echo "Cost,";
 } 
 if(!$purchase_date){} else { 

echo "Purchase Date,";
 } 
 if(!$warranty_end_date){} else { 

echo "Warranty End Date,";
 } 
 if(!$project_comdemned_date){} else { 

echo "Project Condemn Date,";
 } 
 if(!$invoice_no){} else { 

echo "Invoice No,";
 } 
 if(!$brief){} else { 

echo "Brief";
 } 
echo "\n";
if ($_limit > 0) {
    
	$item_f = array('id_category'=>$render_id_category[1],'id_store'=>$render_id_store[1], 'id_department'=>$render_id_department[1]);
	$rs = get_item_for_generalreportitem($_orderby, $sort_order, $_start, $_limit, $dept, false, $item_f);
    
	while ($rec = mysql_fetch_array($rs))
    {
    //echo "\"$rec[asset_no]\",\"$rec[serial_no]\",\"$rec[location_name]\",\"$rec[category_name]\",\"$rec[department_name]\",\"$rec[cost]\",\"$rec[invoice]\",\"$rec[status_name]\"\n";
		$dept_name = (USERDEPT > 0) ? null : "	$rec[department_name]";
		$dept_col = (SUPERADMIN) ? "$rec[department_name]" : '';
		?>
		<?php echo $rec['asset_no']." , ".$rec['serial_no'];?>
		<?php if(!$category){} else { ?>
		<?php echo " , ".$rec['category_name'];?>
		<?php } ?>
		<?php if(!$department){} else { ?>
		<?php echo " , ".$dept_col;?>
		<?php }?>
		<?php if(!$brand_name){} else { ?>
		<?php echo " , ".$rec['brand_name'];?>
		<?php } ?>
		<?php if(!$model_no){} else { ?>
		<?php echo " , ".$rec['model_no'];?>
		<?php } ?>
		<?php if(!$status){} else { ?>
		<?php echo " , ".$rec['status_name'];?>
		<?php } ?>
		<?php if(!$issued_to){} else { ?>
		<?php echo " , ".$rec['issued_to_name'];?>
		<?php } ?>
		<?php if(!$location){} else { ?>
		<?php echo " , ".$rec['location_name'];?>
		<?php } ?>
		<?php if(!$cost){} else { ?>
		<?php echo " , ".$rec['cost'];?>
		<?php } ?>
		<?php if(!$purchase_date){} else { ?>
		<?php echo " , ".$rec['date_of_purchase_fmt'];?>
		<?php } ?>
		<?php if(!$warranty_end_date){} else { ?>
		<?php echo " , ".$rec['warranty_end_date_fmt'];?>
		<?php } ?>
		<?php if(!$project_comdemned_date){} else { ?>
		<?php echo " , ".$rec['date_of_purchase_fmt'];?>
		<?php } ?>
		<?php if(!$invoice_no){} else { ?>
		<?php echo " , ".$rec['invoice'];?>
		<?php } ?>
		<?php if(!$brief){} else { ?>
		<?php echo " , ".$rec['brief'];?>
		<?php } ?>
		
<?php	
		
    }
}


ob_end_flush();
exit;


?>