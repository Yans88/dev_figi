<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;
$_cat = isset($_GET['cat']) ? $_GET['cat'] : 0;
$_status = isset($_GET['status']) ? $_GET['status'] : 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 0;

//print_r($_GET);exit;
$status_name = null;
$category = null;
$res = mysql_query('SELECT status_name FROM `status` WHERE id_status = ' . $_status);
if ($res && ($rec = mysql_fetch_row($res)))
	$status_name = $rec[0];

// ambil category
$query = "SELECT category_name FROM `category` WHERE id_category = '$_cat' ";
$res = mysql_query($query);
if ($res && ($rec = mysql_fetch_row($res))) 
	$category = $rec[0];
    

function count_item_with_status($id_category, $id_status)
{
	$query  = "SELECT COUNT(*) FROM item i 
				LEFT JOIN vendor v ON v.id_vendor = i.id_vendor 
				LEFT JOIN brand b ON b.id_brand = i.id_brand 
				WHERE id_category = '$id_category' AND id_status = $id_status ";
	$res = mysql_query($query);
	$rec = mysql_fetch_row($res);
	return $rec[0];
}

//$crlf = "\n";


/* QUERY */
if($id_department == 0){
	$dept_join = " LEFT JOIN department ON department.id_department = item.id_department";
	$dept_where = " ";
	$dept_name = ", department.department_name ";
} else {
	
	$dept_join = " LEFT JOIN department ON department.id_department = item.id_department";
	$dept_where = " AND item.id_department = '".$id_department."' ";
	$dept_name = ", department.department_name ";
}

if($_status == 7 || $_status == 3 || $_status == 9 || $_status == 8 || $_status == 5){ 
	$query_added = " , machine_info.id_machine, machine_history.vendor_name, machine_history.reference_no, machine_history.period_to, machine_history.charge, condemned_issue.condemn_datetime, condemned_issue.id_issue, condemned_issue.issued_by";
} else {
	$query_added = "";
}

$query  = "SELECT item.*, 
				brand.brand_name, category.category_name, user.full_name, 
				location.location_name ".$dept_name." ".$query_added."
			FROM item
			LEFT JOIN vendor ON vendor.id_vendor = item.id_vendor 
			LEFT JOIN brand ON brand.id_brand = item.id_brand 
			LEFT JOIN location ON location.id_location = item.id_location
			LEFT JOIN category ON category.id_category = item.id_category
			LEFT JOIN user ON user.id_user = item.issued_to
			".$dept_join."
			";
/*			
if($_status == 1)
$query .= " LEFT JOIN loan_item ON loan_item.id_item = item.id_item
			LEFT JOIN loan_out ON loan_item.id_loan = loan_out.id_loan";
*/			

if($_status == 7 || $_status == 3 || $_status == 9 || $_status == 8 || $_status == 5)
$query .= " LEFT JOIN machine_info ON machine_info.id_item = item.id_item
			LEFT JOIN machine_history ON machine_history.id_machine = machine_info.id_machine
			
			LEFT JOIN condemned_item ON condemned_item.id_item = item.id_item
			LEFT JOIN condemned_issue ON condemned_issue.id_issue = condemned_item.id_issue";

			
$query .=" WHERE item.id_category = '$_cat' AND item.id_status = $_status
			".$dept_where."";
		   
//echo $query."<br /><br /><br />";
$res = mysql_query($query);
$no = 0;


ob_clean();
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=item_tracking_by_status-$status_name.csv");
header("Pragma: no-cache");
header("Expires: 0");

echo "Asset No, Serial No, Category, Brand, Model, Date Of Purchase, Warranty End Date";
if($id_department == 0){
	echo ", Department";
} else{

}
if($_status == 1){
	echo ", Issued Date, Issued To, To be Return Date, Loan Id, Location";
} else if($_status == 2){
	echo ", Issued To, To Be Returned, Loan Id, Location";
} elseif( $_status == 7 ){
	echo " , Cost, Current Total Service Charge, Last Repair Date, Brief, Location";
} else if($_status == 3){
	echo ", Issued To, Current Total Service Charge, Last Repair Date, Vendor Name, Vendor Contact";
} else if($_status == 9){
	echo ", Condemned Date, Total Service Charge, To Be Condemned Id, Location";
} else if($_status == 8){
	echo',Brief, Lost Date, Last Borrowed';
} elseif($_status == 5){
	echo',Total Service Charge, Condemned Id, Condemned Date, Scrapped By';
} else {
	echo', Location';
}
echo"\n";

while ($rec = mysql_fetch_assoc($res)) {   

	

/*FUNCTION TO CHECK THE LOAD ID*/
$query_get_loan = "
		SELECT lo.* 
		  FROM loan_item li, loan_out lo 
			WHERE li.id_item = ".$rec['id_item']." AND li.id_loan = lo.id_loan 
			ORDER BY loan_date DESC ";
$rs_get_loan = mysql_query($query_get_loan); 
$rec_loan = mysql_fetch_array($rs_get_loan);
$id_loan = $rec_loan['id_loan'];
$return_date = $rec_loan['return_date'];
/*END FUNCTION LOAN ID*/
	
	
	
	
    echo $rec['asset_no'].", ". $rec['serial_no'].", ". $rec['category_name'].", ". $rec['brand_name'].", ". $rec['model_no'].", ". $rec['date_of_purchase'].", ". $rec['warranty_end_date'];
	
	
	if(empty($id_loan)){ $id_loan = "NA"; } else {$id_loan = "LR".$id_loan;}
	if(empty($return_date)){ $return_date = "NA"; } else {$return_date = $return_date;}
	
	
	if($id_department ==0){
		echo ", ".$rec['department_name'];
	} else{

	}
	if($_status == 1){
		
		echo ', ' . $rec['issued_date']. ', ' . $rec['full_name']. ', ' . $return_date . ', ' . $id_loan . ', ' . $rec['location_name'];
	} else if($_status == 2){
		echo ', ' . $rec['full_name']. ', ' . $return_date . ', ' . $id_loan . ', ' . $rec['location_name'];
	}else if($_status == 7){
	
		$period_to = $rec['period_to'] ? $rec['period_to'] : "NA";
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		echo ', ' . $rec['cost']. ', ' . $total_charge. ', ' . $period_to . ', ' . $rec['brief']. ', ' . $rec['location_name'];	
	} else if($_status == 3){
		$period_to = $rec['period_to'] ? $rec['period_to'] : "NA";
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		$vendor_name = $rec['vendor_name'] ? $rec['vendor_name'] : "NA";
		$vendor_contact = $rec['reference_no'] ? $rec['reference_no'] : "NA";
		echo ', ' . $rec['full_name']. ', ' . $total_charge. ', ' . $period_to . ', ' . $vendor_name. ', ' . $vendor_contact;
	
	} else if($_status == 9){
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		$condemned_date = $rec['condemn_datetime'] ? $rec['condemn_datetime'] : "NA";
		$id_issue_condemn = $rec['id_issue'] ? $rec['id_issue'] : "NA";
		echo ",".$condemned_date.", ".$total_charge.", ". $id_issue_condemn .", ".$rec['location_name'];
	} else if($_status == 8){
		$condemned_date = $rec['condemn_datetime'] ? $rec['condemn_datetime'] : "NA";
		$id_issue_condemn = $rec['id_issue'] ? $rec['id_issue'] : "NA";
		echo ", ".$rec['brief'].", ".$condemned_date.", ".$return_date;
	} elseif($_status == 5){
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		$condemned_date = $rec['condemn_datetime'] ? $rec['condemn_datetime'] : "NA";
		$id_issue_condemn = $rec['id_issue'] ? $rec['id_issue'] : "NA";
		echo', '.$total_charge.', '.$id_issue_condemn.', '.$condemned_date.', '.$rec['issued_by'];
	
	} else {

		echo', ' . $rec['location_name'];
	}
	
	echo "\n";
	
	
}

ob_end_flush();
exit;

?>