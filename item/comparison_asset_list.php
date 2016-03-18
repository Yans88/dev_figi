<?php

$dept = defined('USERDEPT') ? USERDEPT : 0;

//echo $dept;

//$total_update = total_update($dept);
//$total_update_will_compare = $total_update ? $total_update : 0;


$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$total_item = count_item_temporaries($dept);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;
    
?>

<div class="clear"></div>

<form method="post" action="">
  <h2>New Items to be added</h2>
  <div class='error'>
  If you do not see the data that will add, it means that the data you entered is already available in Items.<br />Please check your Serial Number field in your template again.
  </div>
	<table>
		<tr>
			<td colspan=10>
				<a class="button" href="./?mod=item&sub=comparison_asset&act=modifyasset"> Next > Modify Asset </a>
			</td>
		</tr>
	</table>
	<table id="itemlist" cellpadding=2 cellspacing=0 class="itemlist" >
			
		<tr height=30>
		  <th width=30>No</th>
		  <th width=110>Asset No</th>
		  <th width=100>Serial No</th>
		  <th width=100>Category</th>
		  <th width=100>Brand</th>
		  <th width=100>Model No</th>
		  <?php if($dept == 0){?>
		  <th width=100>Department</th>
		  <?php }?>
		  <th width=100>Status</th>
		  <th width=100>Issued To</th>
		  <th width=100>Location</th>
		  <th width=150><input type="checkbox" name="all" id="checkAll" onClick="checkAll_item(<?php echo $total_item; ?>)"> <span id='check_status'>Check All</span> </th>
		</tr>
		
	<?php
		
		if($total_item == 0){
				echo"
				<tr><td colspan='10' align='center'>Data Not Available !</td></tr>
				";
		} else {
			$counter = $_start+1;
			$check = 1;
			
			$rs = get_item_temporaries($dept, $_start, $_limit);
			
			
			while($rec = mysql_fetch_array($rs)){
			
				$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
				
				echo"<tr ".$_class.">";
				echo "
					<td align='center'>".$counter."</td>
					<td>".$rec['asset_no']."</td>
					<td>".$rec['serial_no']."</td>
					<td>".$rec['category_name']."</td>
					<td>".$rec['brand_name']."</td>
					<td>".$rec['model_no']."</td>";
				if($dept == 0){
				echo"
					<td>".$rec['department_name']."</td>";
				}
				echo"	
					<td>".$rec['status_name']."</td>
					<td>".$rec['issued_to_name']."</td>
					<td>".$rec['location_name']."</td>
					<td align='center'><input type='checkbox' name='checklist[]' id='checklist_item_$check' value='".$rec['serial_no']."'></td>
				";		
				echo"</tr>";
				
				$counter++;
				$check++;
				
			}
		}

	?>
		
	</table>
	<table>
		<tr>
			<td colspan='11' align='center'> <?php echo make_paging($_page, $total_page, './?mod=item&sub=comparison_asset&page=');?>
			</td>
		</tr>
		<tr class='alt'>
			<td colspan='11'>
			<div style='float:right;'>
			<input type='submit' name='add_item' value='Add To Item List' class='button' onclick='return confirm("Are you sure want to add items selected ?")'>
			<?php if($total_item > 0) {?>
			<input type='submit' name='delete' value='Refresh / Clear All' class='button' onclick='return confirm("Are you sure want to Delete All item file which you have uploaded ?")'>
			<?php } ?>
			</div>
			</td>
		</tr>
	</table>
		
</form>

<?php

if(@$_POST['add_item']){

	$a = $_POST['checklist'];
	if(empty($a)){
		echo "<script>alert('Please selected the item file first !');location.href='./?mod=item&sub=comparison_asset'</script>";
	} else {
		
		$c = array();
		foreach($a as $b){
			$exec = add_to_item_list($b);
			$c[] = array($exec);
			$count = count($c);
		}
		
		echo "<script>alert('".$count." Item file(s) Added Successfully.');location.href='./?mod=item&sub=comparison_asset'</script>";
		//echo $exec;
	}
}

if(@$_POST['delete']){
	$query_delete = "TRUNCATE TABLE item_temporaries";
	mysql_query($query_delete);
	echo "<script>alert('Delete All Item already Successfully.');location.href='./?mod=item&sub=comparison_asset'</script>";
}
?>

<script>
function checkAll_item(total){
	var a = document.getElementById('checkAll').checked;
	for (var i = 1; i <= total; i++) {
		if(a == true){
			document.getElementById('checklist_item_'+i+'').checked = true;
			document.getElementById('check_status').innerHTML = 'Uncheck All' ;
		} else {
			document.getElementById('checklist_item_'+i+'').checked = false;
			document.getElementById('check_status').innerHTML = 'Check All' ;
		}
	}
}
</script>

<!-- FUNCTION QUERY-->
<?php

function get_item_temporaries($dept, $_start = 0, $_limit = 10)
{
    $fmt = '%d-%b-%Y';
    $query  = "SELECT item_temporaries.*, status.status_name, brand.brand_name, category.category_name, vendor.vendor_name, manufacturer.manufacturer_name, department.department_name,
               DATE_FORMAT(item_temporaries.date_of_purchase, '$fmt') date_of_purchase_fmt,
               DATE_FORMAT(item_temporaries.warranty_end_date, '$fmt') warranty_end_date_fmt,
               location_name, full_name issued_to_name  ,item_store_type.title store_name
               FROM item_temporaries
               LEFT JOIN category ON item_temporaries.id_category=category.id_category 
               LEFT JOIN department ON item_temporaries.id_department = department.id_department 
               LEFT JOIN status ON item_temporaries.id_status=status.id_status 
               LEFT JOIN vendor ON item_temporaries.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item_temporaries.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               LEFT JOIN item_store_type ON item_temporaries.id_store=item_store_type.id_store
               LEFT JOIN user ON item_temporaries.issued_to=user.id_user 
               LEFT JOIN location ON item_temporaries.id_location=location.id_location   
               WHERE category.category_type = 'EQUIPMENT' AND item_temporaries.id_status != 'CONDEMNED' AND 
			   item_temporaries.serial_no NOT IN (SELECT item.serial_no FROM item) 
			   ";
    
    if ($dept > 0)
        $query .= " AND (item_temporaries.id_department = $dept OR item_temporaries.id_owner = $dept) AND category.id_department = $dept ";
		
	$query .= " ORDER BY serial_no LIMIT $_start, $_limit ";
    
    $rs = mysql_query($query);
    error_log($query.mysql_error());
    return $rs;
}


function count_item_temporaries($dept, $_start = 0, $_limit = 10)
{
    $fmt = '%d-%b-%Y';
    $query  = "SELECT count(item_temporaries.serial_no) as total,item_temporaries.*, status.status_name, brand.brand_name, category.category_name, vendor.vendor_name, manufacturer.manufacturer_name, department.department_name,
               DATE_FORMAT(item_temporaries.date_of_purchase, '$fmt') date_of_purchase_fmt,
               DATE_FORMAT(item_temporaries.warranty_end_date, '$fmt') warranty_end_date_fmt,
               location_name, full_name issued_to_name  ,item_store_type.title store_name
               FROM item_temporaries
               LEFT JOIN category ON item_temporaries.id_category=category.id_category 
               LEFT JOIN department ON item_temporaries.id_department = department.id_department 
               LEFT JOIN status ON item_temporaries.id_status=status.id_status 
               LEFT JOIN vendor ON item_temporaries.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item_temporaries.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               LEFT JOIN item_store_type ON item_temporaries.id_store=item_store_type.id_store
               LEFT JOIN user ON item_temporaries.issued_to=user.id_user 
               LEFT JOIN location ON item_temporaries.id_location=location.id_location   
               WHERE category.category_type = 'EQUIPMENT' AND item_temporaries.id_status != 'CONDEMNED' AND 
			   item_temporaries.serial_no NOT IN (SELECT item.serial_no FROM item) 
			   ";
    
    if ($dept > 0)
        $query .= " AND (item_temporaries.id_department = $dept OR item_temporaries.id_owner = $dept) AND category.id_department = $dept ";
		
	$query .= " LIMIT $_start, $_limit ";
    
    $rs = mysql_query($query);
    $rec = mysql_fetch_array($rs);
    return $rec['total'];
}

function add_to_item_list($serial_no){
	$date_now = date('Y-m-d H:i:s');
	$query_select = "SELECT * FROM item_temporaries WHERE serial_no = '".$serial_no."'";
	$execute_query_select = mysql_query($query_select);
	$item_temp = mysql_fetch_array($execute_query_select);
	
	$asset_no = $item_temp['asset_no'];
	$serial_no = $item_temp['serial_no'];
	$issued_to = $item_temp['issued_to'];
	$issued_date = $item_temp['issued_date'];
	$id_category = $item_temp['id_category'];
	$id_vendor = $item_temp['id_vendor'];
	$id_location = $item_temp['id_location'];
	$model_no = $item_temp['model_no'];
	$brief = $item_temp['brief'];
	$cost = $item_temp['cost'];
	$invoice = $item_temp['invoice'];
	$date_of_purchase = $item_temp['date_of_purchase'];
	$warranty_periode = $item_temp['warranty_periode'];
	$warranty_end_date = $item_temp['warranty_end_date'];
	$id_brand = $item_temp['id_brand'];
	$id_status = $item_temp['id_status'];
	$status_update = $date_now;
	$status_defect = $item_temp['status_defect'];
	$id_owner = $item_temp['id_owner'];
	$id_department = $item_temp['id_department'];
	$id_store = $item_temp['id_store'];
	$hostname = $item_temp['hostname'];
	
		$query_insert = "
		INSERT INTO item (
			asset_no,
			serial_no,
			issued_to,
			issued_date,
			id_category,
			id_vendor,
			id_location,
			model_no,
			brief,
			cost,
			invoice,
			date_of_purchase,
			warranty_periode,
			warranty_end_date,
			id_brand,
			id_status,
			status_update,
			status_defect,
			id_owner,
			id_department,
			id_store,
			hostname
		) VALUES (
			'$asset_no',
			'$serial_no',
			 $issued_to,
			'$issued_date',
			 $id_category ,
			 $id_vendor ,
			 $id_location ,
			'$model_no',
			'$brief',
			'$cost',
			'$invoice',
			'$date_of_purchase',
			 $warranty_periode ,
			'$warranty_end_date',
			 $id_brand ,
			 $id_status ,
			'$status_update',
			'$status_defect',
			 $id_owner ,
			 $id_department ,
			 $id_store ,
			'$hostname'
			)";
		
		$execute_insert = mysql_query($query_insert);
		//error_log($query_insert.mysql_error());
		if($execute_insert){
			return $query_insert;
		}
		
}
?>
