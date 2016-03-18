<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;
$_cat = isset($_GET['cat']) ? $_GET['cat'] : 0;
$_status = isset($_GET['status']) ? $_GET['status'] : 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;


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

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = count_item_with_status($_cat, $_status);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;




/*QUERY*/

function machrec_get_histories($id = 0)
{
    $result = null;
	$query  = "SELECT *, date_format(period_from, '%d-%b-%Y') period_from, 
                date_format(period_to, '%d-%b-%Y') period_to, mh.vendor_name mh_vendor_name 
                FROM machine_history mh 
                LEFT JOIN machine_info mi ON mh.id_machine = mi.id_machine  
                LEFT JOIN item ON item.id_item = mi.id_item  
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                LEFT JOIN department dept ON dept.id_department=category.id_department 
                WHERE mh.id_machine = $id ";
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0)
        $result = $rs;
    
	return $result;
}

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
			".$dept_where."
		   LIMIT $_start,  $_limit";
		   
//echo $query;
$res = mysql_query($query);
/*END OF QUERY*/


while ($rec = mysql_fetch_assoc($res)) {   
	$results[] = $rec;
}


echo '
<script>
function export_this(){
	location.href = "./?mod=report&sub=item&act=export_by_status&term=tracking&cat='.$_cat.'&status='.$_status.'";
}
</script>
<h2>Inventory Tracking Report - '.$category.'</h2>


<table>
	<tr>
		<td>
			<h3>Item List with status '.$status_name.' [Total Items: '.$total_item.']<h3>
			<center><a class="button" href="?mod=report&sub=item&act=view&term=tracking&by=category">Back</a><a class="button" href="#" onclick="export_this()">Export</a></center>
		</td>
	</tr>
</table>
<table class="report item-list"  cellpadding=2 cellspacing=1>

<tr>
	<th width=30>No</th>
	<th>Asset No</th>
	<th>Serial No</th>
	<th>Category</th>
	<th>Brand</th>
	<th>Model</th>
	<th>Date Of Purchase</th>
	<th>Warranty End Date</th>';
if($id_department == 0){
	echo "<th>Department</th>";
} else{

}	
if($_status == 1){
	echo "
		<th>Issued Date</th>
	    <th>Issued To</th>
		<th width='70px'>To be Return Date</th>
		<th>Loan Id</th>
		<th>Location</th>
		  ";
} else if($_status == 2){
	echo "
		<th>Issued To</th>
		<th>To Be Returned</th>
		<th>Loan Id</th>
		<th>Location</th>
	";
} elseif( $_status == 7 ){
	echo "
		<th>Cost</th>
		<th width='70px'>Current Total Service Charge</th>
		<th width='70px'>Last Repair Date</th>
		<th>Brief</th>
		<th>Location</th>
	";
} else if($_status == 3){
	echo "
		<th>Issued To</th>
		<th width='70px'>Current Total Service Charge</th>
		<th width='70px'>Last Repair Date</th>
		<th width='70px'>Vendor Name</th>
		<th width='70px'>Vendor Contact</th>
	";

} else if($_status == 9){
	echo "
		<th>Condemned Date</th>
		<th width='70px'>Total Service Charge</th>
		<th width='70px'>To Be Condemned Id</th>
		<th>Location</th>
		
	";
	
} else if($_status == 8){
	echo'
		<th>Brief</th>
		<th>Lost Date</th>
		<th>Last Borrowed</th>
	
	';
	

} elseif($_status == 5){
	echo'
		<th width="70px">Total Service Charge</th>
		<th width="70px">Condemned Id</th>
		<th width="70px">Condemned Date</th>
		<th>Scrapped By</th>
	
	';


} else {
	echo'<th>Location</th>';
}

echo '
	<th>Action</th>
</tr>
';

$row = $_start;
foreach ($results as $rec){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
		
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
	
	//echo $query_get_loan;
	
	if(empty($id_loan)){ $id_loan = "NA"; } else {$id_loan = "LR".$id_loan;}
	if(empty($return_date)){ $return_date = "NA"; } else {$return_date = $return_date;}
	
	
	
	$view_button = '<a href="?mod=item&act=view&id='.$rec['id_item'].'"><img class="icon" src="images/view.png" alt="view">';
	
	echo '<tr '.$class.'>
			<td>' . $row. '.</td>
			<td>' . $rec['asset_no']. '</td>
			<td>' . $rec['serial_no']. '</td>
			<td>' . $rec['category_name']. '</td>
			<td>' . $rec['brand_name']. '</td>
			<td>' . $rec['model_no']. '</td>
			<td>' . $rec['date_of_purchase']. '</td>
			<td>' . $rec['warranty_end_date']. '</td>';
	if($id_department ==0){
		echo "<td>".$rec['department_name']."</td>";
	} else{

	}
	if($_status == 1){
		echo'<td>' . $rec['issued_date']. '</td>
			 <td>' . $rec['full_name']. '</td>
			 <td>' . $return_date . '</td>
			 <td>' . $id_loan . '</td>
			 <td>' . $rec['location_name']. '</td>
			 ';
	} else if($_status == 2){
		echo '
			<td>' . $rec['full_name']. '</td>
			<td>' . $return_date . '</td>
			<td>' . $id_loan . '</td>
			<td>' . $rec['location_name']. '</td>
		';
	}else if($_status == 7){
	
		$period_to = $rec['period_to'] ? $rec['period_to'] : "NA";
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		echo '
			<td>' . $rec['cost']. '</td>
			<td>' . $total_charge. '</td>
			<td>' . $period_to . '</td>
			<td>' . $rec['brief']. '</td>
			<td>' . $rec['location_name']. '</td>
		';	
	} else if($_status == 3){
		$period_to = $rec['period_to'] ? $rec['period_to'] : "NA";
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		$vendor_name = $rec['vendor_name'] ? $rec['vendor_name'] : "NA";
		$vendor_contact = $rec['reference_no'] ? $rec['reference_no'] : "NA";
		echo '
			<td>' . $rec['full_name']. '</td>
			<td>' . $total_charge. '</td>
			<td>' . $period_to . '</td>
			<td>' . $vendor_name. '</td>
			<td>' . $vendor_contact . '</td>
		';
	
	} else if($_status == 9){
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		$condemned_date = $rec['condemn_datetime'] ? $rec['condemn_datetime'] : "NA";
		$id_issue_condemn = $rec['id_issue'] ? $rec['id_issue'] : "NA";
		echo "
		<td>".$condemned_date."</td>
		<td width='70px'>".$total_charge."</td>
		<td width='70px'>". $id_issue_condemn ."</td>
		<td>".$rec['location_name']."</td>
		
	";
	} else if($_status == 8){
		$condemned_date = $rec['condemn_datetime'] ? $rec['condemn_datetime'] : "NA";
		$id_issue_condemn = $rec['id_issue'] ? $rec['id_issue'] : "NA";
		echo "
		<td>".$rec['brief']."</td>
		<td>".$condemned_date."</td>
		<td>".$return_date."</td>
		
		";
	} elseif($_status == 5){
		$total_charge = $rec['charge'] ? $rec['charge'] : "NA";
		$condemned_date = $rec['condemn_datetime'] ? $rec['condemn_datetime'] : "NA";
		$id_issue_condemn = $rec['id_issue'] ? $rec['id_issue'] : "NA";
		echo'
		<td>'.$total_charge.'</td>
		<td>'.$id_issue_condemn.'</td>
		<td>'.$condemned_date.'</td>
		<td>'.$rec['issued_by'].'</td>
	
	';
	
	} else {

		echo'<td>' . $rec['location_name']. '</td>';
	}
	
	echo'	<td align="center">' . $view_button. '</td>
		  </tr>';
}
?>

</table>
<br />
<div>

<?php 
	if ($total_page > 1)
		echo make_paging($_page, $total_page, './?mod=report&sub=item&act=list_by_status&status='.$_status.'&cat='.$_cat.'&page='); 
?>
	
</div>
<br>&nbsp;
<br>&nbsp;