<?php

if (!defined('FIGIPASS')) exit;

$cat = $_GET['cat'];
$no = $_GET['no'];
$this_year = date('Y');


$query = "SELECT *, category.category_name, brand.brand_name
		FROM item
		LEFT JOIN category ON category.id_category = item.id_category
		LEFT JOIN brand ON brand.id_brand = item.id_brand
		LEFT JOIN status ON status.id_status = item.id_status
		WHERE item.id_category = '".$cat."'";

if(!empty($id_department))
	$query .= " AND item.id_department = '".$id_department."'"; 
 
	$query .= " AND (YEAR(date_of_purchase) + ".$no."=".$this_year.")";
//echo $query;

$rs = mysql_query($query);
$category_title = get_category_name($cat);

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"Report_Age_Category-".$category_title.".csv\"");

echo "Asset No, Serial No, Category, Brand, Model No, Date Of Purchase, Warranty End Date, Status\n";
while ($rec = mysql_fetch_array($rs)) {
	
	
	echo $rec['asset_no'].", ".$rec['serial_no'].", ".$rec['category_name'].", ".$rec['brand_name'].", ".$rec['model_no'].", ".$rec['date_of_purchase'].", ".$rec['warranty_end_date'].", ". $rec['status_name']."\n";
			
}

ob_end_flush();
exit;
?>