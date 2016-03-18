

<?php 

if (!defined('FIGIPASS')) exit;
$dept_available = defined('USERDEPT') ? USERDEPT : 0;  // DEPARTMENT LOGGED IN = defined('USERDEPT') ? USERDEPT : 0;  // DEPARTMENT LOGGED IN

?>	
	


<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<table>
	<tr>
		<td align="center"><h3>Select a CSV file</h3></td>
		
	</tr>
	<tr>
		<td align="center">
			<input type="file" name="csv" value="Select file">
		</td>
		
	</tr>
	<tr>
		<td align="center">
			<input type="submit" name="import" value=" Import Item(s) " > 
		</td>
	</tr>
</table>
</form>

<?php

if (isset($_POST['import'])) {
	
	truncate_alternate_table_before_upload();
	
	if (USERDEPT == 0 && (USERGROUP != GRPASSETADMIN)){

		echo "CANNOT IMPORT THE DATA <br />";

	} else {
			
			
		
		$target_dir = $root_path."item/uploads/";
		
		$target_file = $target_dir . basename($_FILES["csv"]["name"]);
		
		if (move_uploaded_file($_FILES["csv"]["tmp_name"], $target_file)) {
			//echo "The file ". basename( $_FILES["csv"]["name"]). " has been imported. Importing ";
			$a = add_to_item_temporary_alternate_table($target_file);
			//echo "<h2>Upload ". ucfirst($a)."</h2>";
			
			
			
		echo "<br />";	
		//Progress bar holder
		echo "Please wait...";
		echo '<div id="progress" style="width:500px;border:1px solid #ccc;"></div>';
		//Progress information
		echo '<div id="information"></div>';
			
		// Total processes
		
		$total = $a;
		// Loop through process
		for($i=1; $i<=$total; $i++){
			// Calculate the percentation
			$percent = intval($i/$total * 100)."%";
			
			// Javascript for updating the progress bar and information
			echo '<script language="javascript">
			document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#ddd;\">&nbsp;</div>";
			document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
			</script>';
			

		// This is for the buffer achieve the minimum size in order to flush data
			echo str_repeat(' ',1024*64);
			

		// Send output to browser immediately
			flush();
			

		// Sleep one second so we can see the delay
			sleep(0.5);
		}
		// Tell user that the process is completed
		echo '
		<script language="javascript">
			document.getElementById("information").innerHTML="Process completed";
			alert("'.$total.' data prepared and processing completed!");
			location.href="./?mod=item&sub=comparison_asset&act=import";
		</script>';
		
			
			//echo $target_dir."<br />";
			//echo $target_file;
			unlink($target_file);
		
			
		} else {
			echo "Sorry, there was an error importing your file.";
		}
		
	}

}



//$count_item = count_data_in_temporary_alternate();
$count_item = count_item_temporary_alternate();
if($count_item > 0){

// START FORM
$rs_data_error = data_error();
$rs = data_error();
echo "
<br />
<form action='' method='POST'>

There are <b>".$count_item."</b> new items to be added. <br />";
			
	$arrayForBrand = array();
	$arrayforCategory = array();
	
	while($x = mysql_fetch_array($rs)){
		$asset = $x['asset_name'];
		$brand = checkBrand($asset);
		$id_brand_2 = getIdBrand($brand);
		$dept_available = defined('USERDEPT') ? USERDEPT : 0;  
		$category_2 = checkCategory($asset, $dept_available);
		$id_category_2 = getIdCategory($category_2); // id_category
		
		if($id_brand_2 == 0 || $id_category_2 == 0){
			$arrayForBrand[] = $id_brand_2;
			$arrayforCategory[] = $id_category_2;
		}
	}
			
	$countBrandError = count($arrayForBrand);
	$countCategoryError	= count($arrayforCategory);
	
	echo "<input type='submit' name='skip' value='Skip > '><br />";
	
	if($countBrandError > 0 && $countCategoryError > 0){
	echo 	"<div class='error'>
				Some Category and Brand Info need to be updated before you are able to continue to add item.<br />Please see below for the status of each new item.<br /><br />
			</div>";
	} else {
	echo	"
			Are you sure want to the next process ?<br />
			<input type='submit' name='no' value='No'><input type='submit' name='yes' value='Yes'><br /><br />
			";
	
	}
			
			//START TABLE 
			echo "<table id='itemlist' cellpadding=2 cellspacing=0 class='itemlist' >
					
				<tr>
					<th>Serial Number</th>
					<th>Asset Name</th>
					<th>Error Message</th>
					
				</tr>	
			";
			$counter=1;
			
			while($fetch_data = mysql_fetch_array($rs_data_error)){
			
				$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
				
				$asset_name = $fetch_data['asset_name'];
				$serial_no = $fetch_data['serial_no'];
				$asset_no = $fetch_data['asset_code'] ? $fetch_data['asset_code'] : $serial_no;
				
				$brand_name = checkBrand($asset_name);
				$id_brand = getIdBrand($brand_name);
				
				$dept_available = defined('USERDEPT') ? USERDEPT : 0;  
				$category_name = checkCategory($asset_name, $dept_available);
				$id_category = getIdCategory($category_name); // id_category
				
				$model_no = createModelNo($brand_name, $category_name, $asset_name);
				$location_name = getIdLocation($fetch_data['location_name']);
				$issued_date = date('Y-m-d', strtotime($fetch_data['asset_put_to_use']));
				$purchase_date = date('Y-m-d', strtotime($fetch_data['date_of_purchase']));
				$issued_to = 1;
				$cost = $fetch_data['cost'] ? $fetch_data['cost'] : 0 ; // cost
				
				$idDepartmentByCategory = getIdDepartmentByCategory($category_name); // Get Id Department on Category
				$department_name = getIdDepartment($fetch_data['department']); //id_department
				
				$status_name = getIdStatus($fetch_data['status']); //id_status
				$vendor_name = getIdVendor($fetch_data['vendor']); //id_vendor
				$invoice = $fetch_data['invoice'] ? $fetch_data['invoice'] : 0 ; 
				
				
				if($id_brand == 0 || ($id_category == 0 && $idDepartmentByCategory == 0)){
					if($id_brand == 0){
						$error_idBrand = "<br />Brand not defined for this Asset Name";
					} else {
						$error_idBrand = "";
					}
					
					if($id_category == 0 && $idDepartmentByCategory == 0){
						$error_idCategory = "Category not defined for this Asset Name";
					} else {
						$error_idCategory = "";
					}
					
					
					echo "
					<tr ".$_class.">
						<td>".$serial_no."</td>
						<td>".$asset_name."</td>
						<td>".$error_idCategory." ".$error_idBrand."</td>
						
					</tr>
					";
					$counter++;
				} 
				
			}
			//$countBrandError = count($arrayForBrand);
			//$countCategoryError	= count($arrayforCategory);
			
			//echo $countBrandError." " .$countCategoryError;
			if($countBrandError == 0 && $countCategoryError == 0 ){
				echo "
				<tr ".$_class." align='center'>
					<td colspan='4'>You haven't error in this file. You can continue to the Next Step.</td>
				</tr>
				";
			
			}
			// END TABLE
			echo"</table>";
			

echo "</form>"; 
// END FORM

}
	


if(@$_POST['yes']){
	truncate_table_temporaries();
	$a = get_item_temporary_alternate();
	echo "<script>alert('".$a.".');location.href='./?mod=item&sub=comparison_asset&act=list';</script>";
	truncate_alternate_table_before_upload();
	
}

if(@$_POST['skip']){
	truncate_table_temporaries();
	$a = get_item_temporary_alternate_skip();
	//
	echo $a;
	truncate_alternate_table_before_upload();
	
}


//============== QUERY ===================

function add_to_item_temporary_alternate_table($path){

	$row = 1;
	if (($handle = fopen($path, "r")) !== FALSE) {
		
		$fp = file($path);
		$total = count($fp); // TOTAL ROWS
	
		
		$array_values = array();
		
		while (($data = fgetcsv($handle, 1000, ",",'"')) !== FALSE) {
		
		  $num = count($data);
			
			if($row > 3){
				
				$zone_name = htmlspecialchars($data[0], ENT_QUOTES);
				$asset_code = htmlspecialchars($data[1], ENT_QUOTES);
				$asset_name = htmlspecialchars($data[2], ENT_QUOTES);
				$serial_no = htmlspecialchars($data[3], ENT_QUOTES);
				$location_name = htmlspecialchars($data[4], ENT_QUOTES);
				$asset_put_to_use = htmlspecialchars($data[5], ENT_QUOTES);
				$date_of_purchase = htmlspecialchars($data[6], ENT_QUOTES);
				$cost = htmlspecialchars($data[7], ENT_QUOTES);
				$department = htmlspecialchars($data[8], ENT_QUOTES);
				$department_name = getIdDepartment($department);
				$status = htmlspecialchars($data[9], ENT_QUOTES);
				$retired_date = htmlspecialchars($data[10], ENT_QUOTES);
				$is_active = htmlspecialchars($data[11], ENT_QUOTES);
				$vendor = htmlspecialchars($data[12], ENT_QUOTES);
				$invoice = htmlspecialchars($data[13], ENT_QUOTES);
				$remarks = htmlspecialchars($data[14], ENT_QUOTES);
				$asset_reference_no = htmlspecialchars($data[15], ENT_QUOTES);
				$category_code = htmlspecialchars($data[16], ENT_QUOTES);
				$asset_type = htmlspecialchars($data[17], ENT_QUOTES);
				$nbv = htmlspecialchars($data[18], ENT_QUOTES);
				$accumulated_depreciation = htmlspecialchars($data[19], ENT_QUOTES);
				$age_as_of_today = htmlspecialchars($data[20], ENT_QUOTES);
				$end_of_useful_life = htmlspecialchars($data[21], ENT_QUOTES);
				$last_depreciated_month = htmlspecialchars($data[22], ENT_QUOTES);
				$last_verification_date = htmlspecialchars($data[23], ENT_QUOTES);
				$interface_date_to_ifaas = htmlspecialchars($data[24], ENT_QUOTES);
		
				$dept_available = defined('USERDEPT') ? USERDEPT : 0;		
				if($dept_available > 0){
					if($department_name == $dept_available){
						$array_values[] = " ( '$zone_name','$asset_code', '$asset_name', '$serial_no', '$location_name', '$asset_put_to_use', '$date_of_purchase', '$cost',
						 '$department', '$status', '$retired_date', '$is_active', '$vendor', '$invoice', '$remarks', '$asset_reference_no', '$category_code',
						 '$asset_type', '', '', '', '$end_of_useful_life', '$last_depreciated_month',
						 '$last_verification_date', ''
						) ";
					} 
					
				} else {
					$array_values[] = " ( '$zone_name','$asset_code', '$asset_name', '$serial_no', '$location_name', '$asset_put_to_use', '$date_of_purchase', '$cost',
						 '$department', '$status', '$retired_date', '$is_active', '$vendor', '$invoice', '$remarks', '$asset_reference_no', '$category_code',
						 '$asset_type', '', '', '', '$end_of_useful_life', '$last_depreciated_month',
						 '$last_verification_date', ''
						) ";
				}
				
			}
			
			
			
			$count = count($array_values);
			$row++;
			
			
		}
		
		//$serial_no = implode(', ', $encapsulationSerialNo);
		
		$query = "INSERT INTO item_temporary_alternate
					( zone_name, asset_code, asset_name, serial_no, location_name, asset_put_to_use, date_of_purchase, cost,
					 department, status, retired_date, is_active, vendor, invoice, remarks, asset_reference_no, category_code,
					 asset_type, nbv, accumulated_depreciation, age_as_of_today, end_of_useful_life, last_depreciated_month,
					 last_verification_date, interface_date_to_ifaas
					) VALUES ";
		
		$query .= implode(",", $array_values);
		
		//echo $a;
		
		fclose($handle);
		$exec = mysql_query($query);
		if($exec){ 
		
			return $count;//"Success. Congratulations !"; 
		
		} else { 
			return "Import Error. There is a data error in your template. Please check your data again now.";
			//return $query. "<br />".mysql_error();
			
		}
		//return $query;
		
		
	}
	
}

function insert_to_temporary_alternate( $asset_code, $asset_name, $serial_no, $location_name, $asset_put_to_use, $date_of_purchase, $cost,
					 $department, $status, $retired_date, $is_active, $vendor, $invoice, $remarks, $asset_reference_no, $category_code,
					 $asset_type, $nbv, $accumulated_depreciation, $age_as_of_today, $end_of_useful_life, $last_depreciated_month,
					 $last_verification_date, $interface_date_to_ifaas){

$query = "INSERT INTO item_temporary_alternate
					( asset_code, asset_name, serial_no, location_name, asset_put_to_use, date_of_purchase, cost,
					 department, status, retired_date, is_active, vendor, invoice, remarks, asset_reference_no, category_code,
					 asset_type, nbv, accumulated_depreciation, age_as_of_today, end_of_useful_life, last_depreciated_month,
					 last_verification_date, interface_date_to_ifaas
					) VALUES ( '$asset_code', '$asset_name', '$serial_no', '$location_name', '$asset_put_to_use', '$date_of_purchase', '$cost',
					 '$department', '$status', '$retired_date', '$is_active', '$vendor', '$invoice', '$remarks', '$asset_reference_no', '$category_code',
					 '$asset_type', '', '', '', '$end_of_useful_life', '$last_depreciated_month',
					 '$last_verification_date', ''
					)
				";

$exec = mysql_query($query);
if($exec){ return "success"; } else { return "Failed. Because ".mysql_error(); }

}

function truncate_alternate_table_before_upload(){
	$query = "TRUNCATE TABLE item_temporary_alternate";
	mysql_query($query);
}

function truncate_table_temporaries(){
	$query = "TRUNCATE TABLE item_temporaries";
	mysql_query($query);
}

function count_item_temporary_alternate(){
	$query = "SELECT serial_no, COUNT(serial_no) as total_all FROM item_temporary_alternate";
	$rs = mysql_query($query);
	$exec = mysql_fetch_array($rs);
	$total_all = $exec['total_all'];
	return $total_all;
	
}

function get_item_temporary_alternate(){

	$query = "SELECT * FROM item_temporary_alternate";
	$mysql_query = mysql_query($query);
	
	$a = array();
	$arrayBrand = array();
	$arrayDepartment = array(); // array for department
	$array_serial_no = array();
	
	while($fetch_data = mysql_fetch_array($mysql_query)){
		
		$asset_name = $fetch_data['asset_name'];
		$serial_no = $fetch_data['serial_no'];
		$asset_no = $fetch_data['asset_code'] ? $fetch_data['asset_code'] : $serial_no;
		
		$brand_name = checkBrand($asset_name);
		$id_brand = getIdBrand($brand_name);
		
		$dept_available = defined('USERDEPT') ? USERDEPT : 0;  
		$category_name = checkCategory($asset_name, $dept_available);
		$id_category = getIdCategory($category_name); // id_category
		
		$model_no = createModelNo($brand_name, $category_name, $asset_name);
		$location_name = getIdLocation($fetch_data['location_name']);
		$issued_date = date('Y-m-d', strtotime($fetch_data['asset_put_to_use']));
		$purchase_date = date('Y-m-d', strtotime($fetch_data['date_of_purchase']));
		$issued_to = 1;
		$cost = $fetch_data['cost'] ? $fetch_data['cost'] : 0 ; // cost
		
		$idDepartmentByCategory = getIdDepartmentByCategory($category_name); // Get Id Department on Category
		$department_name = getIdDepartment($fetch_data['department']); //id_department
		
		$status_name = getIdStatus($fetch_data['status']); //id_status
		$vendor_name = getIdVendor($fetch_data['vendor']); //id_vendor
		$invoice = $fetch_data['invoice'] ? $fetch_data['invoice'] : 0 ; 
		
		if($id_brand == 0) {
			
			echo '<script>alert("We are sorry. We cannot continue this process because you have an error in Asset Name. \n Asset Name for '.$asset_name.' not have Brand Name. \n Please Insert new Brand Name first.");location.href="./?mod=item&sub=brand";</script>';

		} else if($id_category == 0 && $idDepartmentByCategory == 0) {
		
			echo '<script>alert("We are sorry. We cannot continue this process because you have an error in Asset Name. \n Asset Name for '.$asset_name.' not have Category Name. \n Please Insert new Category Name first." );location.href="./?mod=item&sub=category";</script>';
			
		} else if($dept_available > 0) {
		
			if($dept_available != $idDepartmentByCategory){
				
				$arrayDepartment[] = $idDepartmentByCategory;
				
			} else {
			
				$array_serial_no[] = $serial_no;
				
			}
		
				$added_to_item_table = add_to_item_temporaries_table($asset_no, $id_brand, $id_category, 
														$model_no, $serial_no, $location_name, $issued_date, 
														$purchase_date, $cost, $idDepartmentByCategory, 
														$status_name, $vendor_name, $invoice, $issued_to);
		
		} else {
			/*
			echo "
				Asset No : $asset_no <br />
				Brand Id : $id_brand<br />
				Category Id : $category_name <br />
				Model No : $model_no <br />
				
				Serial No : $serial_no <br />
				Location Id : $location_name <br />
				Issued Date : $issued_date <br />
				Purchase_date : $purchase_date <br />
				Purchase Value : $cost<br />
				Department Id : $idDepartmentByCategory <br />
				
				Status Name : $status_name <br />
				Vendor Name : $vendor_name <br />
				Invoice : $invoice <br /><br />
			";
			*/
			$array_serial_no[] = $serial_no;
			$added_to_item_table = add_to_item_temporaries_table($asset_no, $id_brand, $id_category, 
														$model_no, $serial_no, $location_name, $issued_date, 
														$purchase_date, $cost, $idDepartmentByCategory, 
														$status_name, $vendor_name, $invoice, $issued_to);
			
		
		}
		
	}
	
	$count_1 = count($arrayDepartment);
	$count_2 = count($array_serial_no);
	
	echo "<script>alert('".$count_2." Data Successfully added.');location.href='./?mod=item&sub=comparison_asset&act=list'</script>";
	truncate_alternate_table_before_upload();
	//return $added_to_item_table;
	

}


function get_item_temporary_alternate_skip(){

	$query = "SELECT * FROM item_temporary_alternate";
	$mysql_query = mysql_query($query);
	
	$array_serial_no = array();
	$array_serial_no_error = array();
	$array_values = array();
	
	while($fetch_data = mysql_fetch_array($mysql_query)){
		
		$asset_name = $fetch_data['asset_name'];
		$serial_no = $fetch_data['serial_no'];
		$serial_no_html = htmlspecialchars($serial_no, ENT_QUOTES);
		$asset_no = $fetch_data['asset_code'] ? $fetch_data['asset_code'] : $serial_no;
		$asset_no_html = htmlspecialchars($asset_no, ENT_QUOTES);
		
		$brand_name = checkBrand($asset_name);
		$id_brand = getIdBrand($brand_name);
		
		$dept_available = defined('USERDEPT') ? USERDEPT : 0;  
		$category_name = checkCategory($asset_name, $dept_available);
		$id_category = getIdCategory($category_name); // id_category
		
		$model_no = createModelNo($brand_name, $category_name, $asset_name);
		$model_no_html = htmlspecialchars($model_no, ENT_QUOTES);
		$location_name = getIdLocation($fetch_data['location_name']);
		$issued_date = date('Y-m-d', strtotime($fetch_data['asset_put_to_use']));
		$purchase_date = date('Y-m-d', strtotime($fetch_data['date_of_purchase']));
		$issued_to = 1;
		$cost = $fetch_data['cost'] ? $fetch_data['cost'] : 0 ; // cost
		
		$idDepartmentByCategory = getIdDepartmentByCategory($category_name); // Get Id Department on Category
		$department_name = getIdDepartment($fetch_data['department']); //id_department
		
		$status_name = getIdStatus($fetch_data['status']); //id_status
		$vendor_name = getIdVendor($fetch_data['vendor']); //id_vendor
		$invoice = $fetch_data['invoice'] ? $fetch_data['invoice'] : 0 ; 
		
		
		if($id_brand > 0 && $id_category > 0){
			/*
			$added_to_item_table = add_to_item_temporaries_table($asset_no, $id_brand, $id_category, 
													$model_no, $serial_no, $location_name, $issued_date, 
													$purchase_date, $cost, $idDepartmentByCategory, 
													$status_name, $vendor_name, $invoice, $issued_to);
			*/
			//echo $serial_no." ". $id_brand ." ".$id_category."<br />";
			
			
			$array_values[] = " ('$asset_no_html', '$id_brand', '$id_category', '$model_no_html', '$serial_no', '$location_name', '$issued_date', 
													'$purchase_date', '$cost', '$idDepartmentByCategory', 
													'$status_name', '$vendor_name', '$invoice', '$issued_to') ";
			$array_serial_no[] = $serial_no;
		}
		$array_serial_no_error[] = $serial_no;
		
	}
	
	
	$count = count($array_serial_no);
	$count_error = count($array_serial_no_error);
	$query  = 'INSERT INTO item_temporaries (asset_no, id_brand, id_category, model_no, serial_no, id_location, issued_date, 
					date_of_purchase, cost, id_department, id_status,id_vendor, invoice, issued_to) VALUES ';
					
	$query .= implode(",", $array_values);
	
	$exec = mysql_query($query);
	if($exec){ 
		return "<script>alert('".$count." data to be added.');location.href='./?mod=item&sub=comparison_asset&act=list';</script>";
	} else { 
		return "<script>alert('".$count_error." Data error cannot be added because you do not have one right sort of data. Please update your Brand and Category first.');location.href='./?mod=item&sub=comparison_asset&act=import';</script>";
		
	}
	//return $query;
	
}


function data_error(){

	$query = "SELECT * FROM item_temporary_alternate";
	$mysql_query = mysql_query($query);
	
	return $mysql_query;

}







//============= CREATE =============

function createModelNo($words1, $words2, $words){
	
	$word1 = htmlspecialchars(strtolower($words1), ENT_QUOTES);
	$word2 = htmlspecialchars(strtolower($words2), ENT_QUOTES);
	$word3 = htmlspecialchars(strtolower($words), ENT_QUOTES);
	
	$a = explode(" ",$word1); 
	$b = explode(" ",$word2);
	$c = array_merge($a, $b);
	$d = explode(" ", $word3);
	$e = array_diff($d, $c);
	$f = implode(" ", $e);
	
	return ucfirst($f);
	
}

//============= CHECK =============

function checkBrand($words){
	
	$a = getBrandName();
	
	
	foreach($a as $data){
		if (stripos($words,$data) !== false) { return $data; } 
		
	}
	
	return $words;
	
}

function checkCategory($words, $dept){
	$b = getCategoryName($dept); // get Category
	foreach($b as $data){
		if (stripos($words,$data) !== false) return $data;
	}
	return $words;
}


//============= GET =============
function getIdDepartment($words){

	$query = "SELECT * FROM department WHERE department_name = '$words'";
	$mysql_query = mysql_query($query);
	$fetch = mysql_fetch_array($mysql_query);
	
	if(empty($fetch)){ return 0; } else { return $fetch['id_department']; }
}

function getIdCategory($words){
	$words = htmlspecialchars($words, ENT_QUOTES);
	$query = "SELECT * FROM category WHERE category_name = '$words'";
	$mysql_query = mysql_query($query);
	$fetch = mysql_fetch_array($mysql_query);
	
	if(empty($fetch)){ return 0; } else { return $fetch['id_category']; }
}

function getBrandName(){
	//$array = array();
	$query = "SELECT id_brand, brand_name FROM brand";
	$mysql = mysql_query($query);
	$array = array();
	while($data = mysql_fetch_array($mysql)){
		$array[] = $data['brand_name'];
	}
	return $array;
}

function getCategoryName($dept = 0){
	$query = "SELECT category_name FROM category ";
	
	if($dept > 0){
		$query .= " WHERE id_department=".$dept."";
	}
	
	$mysql = mysql_query($query);
	$array = array();
	while($data = mysql_fetch_array($mysql)){
		$array[] = $data['category_name'];
	}
	return $array;
	
}

function getIdBrand($words){
	$words = htmlspecialchars($words, ENT_QUOTES);
	$query = "SELECT * FROM brand WHERE brand_name = '".$words."'";
	$mysql_query = mysql_query($query);
	$data = mysql_fetch_array($mysql_query);
	if(empty($data['id_brand'])){
		return 0;
	} else {
		return $data['id_brand'];
	}
	
	
}


function getIdVendor($words){
	$words= htmlspecialchars($words, ENT_QUOTES);
	$query = "SELECT * FROM vendor WHERE vendor_name = '$words'";
	$mysql_query = mysql_query($query);
	$check_vendor = mysql_num_rows($mysql_query);
	
	if($check_vendor > 0){
		$fetch = mysql_fetch_array($mysql_query);
		if(empty($fetch)){ return 0; } else { return $fetch['id_vendor']; }
	} else {
	
		$insert = "INSERT INTO vendor (vendor_name) VALUES ('$words')";
		mysql_query($insert);
		
		$vendor = "SELECT id_vendor FROM vendor WHERE vendor_name = '$words'";
		$execute_vendor = mysql_query($vendor);
		$fetch = mysql_fetch_array($execute_vendor);
		if(empty($fetch)){ return 0; } else { return $fetch['id_vendor']; }
	
	}
	
}

function getIdLocation($words){
	$words= htmlspecialchars($words, ENT_QUOTES);
	$query = "SELECT * FROM location WHERE location_name = '$words'";
	$mysql_query = mysql_query($query);
	$check = mysql_num_rows($mysql_query);
	
	if($check > 0) {
		
		$fetch = mysql_fetch_array($mysql_query);
		if(empty($fetch)){ return 0; } else { return $fetch['id_location']; }
		
	} else {
	
		$insert = "INSERT INTO location ( location_name, location_desc) VALUES ('$words', '$words')";
		mysql_query($insert);
		
		$query = "SELECT * FROM location WHERE location_name = '$words'";
		$mysql_query = mysql_query($query);
		$fetch = mysql_fetch_array($mysql_query);
		if(empty($fetch)){ return 0; } else { return $fetch['id_location']; }
		
	}
}

function getIdStatus($words){
	
	if ($words == 'Available'){
		$words = "Available For Loan";
	}
	$words = htmlspecialchars($words, ENT_QUOTES);
	$query = "SELECT * FROM status WHERE status_name = '$words'";
	$mysql_query = mysql_query($query);
	$row = mysql_num_rows($mysql_query);
	if($row > 0){
		$fetch = mysql_fetch_array($mysql_query);
	
		if(empty($fetch)){ return 0; } else { return $fetch['id_status']; }
	} else {
		$insert = "INSERT INTO status (status_name) VALUES ('$words')";
		$insert_query = mysql_query($insert);
		
		$query = "SELECT * FROM status WHERE status_name = '$words'";
		$mysql_query = mysql_query($query);
		$fetch = mysql_fetch_array($mysql_query);
	
		if(empty($fetch)){ return 0; } else { return $fetch['id_status']; }
	}
	
	
}

function getIdDepartmentByCategory($category_name){
	$category_name = htmlspecialchars($category_name, ENT_QUOTES);
	$query = "SELECT id_department FROM category WHERE category_name = '$category_name'";
	$mysql_query = mysql_query($query);
	$fetch = mysql_fetch_array($mysql_query);
	
	if(empty($fetch)){ return 0; } else { return $fetch['id_department']; }

}

function add_to_item_temporaries_table($asset_no, $id_brand, $id_category, $model_no, $serial_no, $location_name, $issued_date, 
				$purchase_date, $cost, $department_name, $status_name, $vendor_name, $invoice, $issued_to){

	$check = "SELECT serial_no FROM item_temporaries WHERE serial_no = '$serial_no'";
	$mysql_check = mysql_query($check);
	$row = mysql_num_rows($mysql_check);
	
	if($row > 0){
		//$a = $asset_no . " already in Database.";
		$status_update = date('Y-m-d H:i:s');
		
		$query = "UPDATE item_temporaries SET
					asset_no = '$asset_no' , 
					id_brand = '$id_brand' ,
					id_category = '$id_category' ,
					model_no = '$model_no' ,
					id_location = '$location_name' ,
					issued_date = '$issued_date' ,
					issued_to = '$issued_to' ,
					status_update = '$status_update',
					date_of_purchase = '$purchase_date',
					cost = $cost ,
					id_department = '$department_name',
					id_status = '$status_name',
					id_vendor = '$vendor_name',
					invoice = '$invoice'
					WHERE serial_no = '$serial_no'
				";
				
		mysql_query($query);
		
		$a = "Data in temporary list was updated successfully.";
		
			
	} else {
	
		$status_update = date('Y-m-d H:i:s');
		
		$query  = 'INSERT INTO item_temporaries (asset_no, id_brand, id_category, model_no, serial_no, id_location, issued_date, status_update,
					date_of_purchase, cost, id_department, id_status,id_vendor, invoice, issued_to) ';
					
		$query .= "VALUES ('$asset_no', '$id_brand', '$id_category', '$model_no', '$serial_no', '$location_name', '$issued_date', '$status_update',
					'$purchase_date', '$cost', '$department_name', '$status_name', '$vendor_name', '$invoice', '$issued_to')";
					
		mysql_query($query);
		error_log($query.mysql_error());
		$a = "Data Added to Temporary Table.";
		
	}
	return $a;
}

?>

<script>
	function checkfile(frm){
		if (frm.csv.files.length == 0){
			alert('Please select a csv file to be uploaded!');
			return false;
		}
		return true;
	}
</script>