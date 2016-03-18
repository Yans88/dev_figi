<?php

$dept = defined('USERDEPT') ? USERDEPT : 0;

//echo $dept;

//$total_update = total_update($dept);
//$total_update_will_compare = $total_update ? $total_update : 0;


$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$total_item = count_comparison_status($dept);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;
    
?>

<div class="clear"></div>

<form method="post" action="">
  <h2>Modify Status Which Available in Figi</h2>
	<table>
		<tr>
			<td colspan=5>
				<a class="button" href="./?mod=item&sub=comparison_asset&act=modifylocations"> Previous < Modify Locations </a>
			</td>	
		</tr>
	</table>
	<table id="itemlist" cellpadding=2 cellspacing=0 class="itemlist">
		
		<tr height=30>
		  <th width=30>No</th>
		  <th width=110>Status In File</th>
		  <th width=100>Status In Figi</th>
		  <th width=100>Serial No In Figi/ File</th>
		  <th width=100>Brand In Figi</th>
		  <th width=100>Category In Figi</th>
		  <th width=100>Model No In Figi</th>
		  
		  <th width=100><input type="checkbox" name="all" id="checkAll" onClick="checkAll_item(<?php echo $total_item; ?>)"> <span id='check_status'>Check All</span> </th>
		</tr>
		
		<?php
		if($total_item == 0){
				echo"
				<tr><td colspan='8' align='center'>Data Not Available !</td></tr>
				";
		} else {
			$counter = $_start+1;
			$check = 1;
			
			$rs = get_comparison_status($dept, $_start, $_limit);
			//echo $rs;
			
			while($rec = mysql_fetch_array($rs)){
			
				$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
				
				
					echo"<tr ".$_class.">";
					echo "
						<td align='center'>".$counter."</td>
						<td align='center'>".$rec['temporary_status']."</td>
						<td align='center'>".$rec['item_status']."</td>
						<td align='center'>".$rec['serial_no']."</td>
						<td align='center'>".$rec['brand_name']."</td>
						<td align='center'>".$rec['category_name']."</td>
						<td align='center'>".$rec['model_no']."</td>
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
			<td colspan='5' align='center'> 
				<?php echo make_paging($_page, $total_page, './?mod=item&sub=comparison_asset&act=modifystatus&page='); ?> 
			</td>
		</tr>
		<tr>
			<td>
			<div style='float:right;'><input type='submit' name='add_item' value='Modify Status' class='button' onclick='return confirm("Are you sure want to Update item(s) selected ?")'></div>
			</td>
		</td>
	</table>
		
</form>

<?php

if(@$_POST['add_item']){

	$a = @$_POST['checklist'];
	if(empty($a)){
		echo "<script>alert('Please selected the item file first !');location.href='./?mod=item&sub=comparison_asset&act=modifylocations'</script>";
	} else {
		
		$c = array();
		foreach($a as $b){
			$exec = modify_status($b);
			$c[] = array($exec);
			$count = count($c);
		}
		
		echo "<script>alert('".$count." Item file(s) Updated Successfully.');location.href='./?mod=item&sub=comparison_asset&act=modifylocations'</script>";
		//echo $exec;
	}
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

function get_comparison_status($dept, $_start = 0, $_limit = 10)
{
	// THIS QUERY TO MAKE A SURE THAT THERE IS STATUS 'Retired/Scrapped' OR NOT
	$status_name = 'Retired/Scrapped';
	$q = "SELECT * FROM status WHERE status_name='".$status_name."'";
	$qw = mysql_query($q);
	$qr = mysql_fetch_array($qw);
	$stts = $qr['id_status'];
	if($stts > 0){
		$w = @$qr['status_name'];
	} else {
		$w = $status_name;
	}
    
    $query  = "
			SELECT e.serial_no, e.id_department, e.id_owner, e.id_category ,(i.status_name) as item_status, (e.status_name) as temporary_status, 
				(e.id_status) as id_status_itemtemporaries, (i.id_status) as id_status_item, i.model_no, i.brand_name, i.category_name 
			FROM 
				(SELECT item_temporaries.*, status_name FROM item_temporaries LEFT JOIN status ON status.id_status = item_temporaries.id_status ) as e, 
				(SELECT item.*, status_name, category_name, brand_name 
					FROM item 
					LEFT JOIN status ON status.id_status = item.id_status
					LEFT JOIN brand ON brand.id_brand = item.id_brand
					LEFT JOIN category ON category.id_category = item.id_category
				) as i, 
				category 
			WHERE 
				i.id_status != e.id_status AND i.serial_no = e.serial_no AND e.id_category = category.id_category AND 
				i.id_category= category.id_category AND (i.status_name != '$w')"; 
    
    if ($dept > 0)
        $query .= " AND (i.id_department = $dept OR i.id_owner = $dept) AND category.id_department = $dept AND e.id_category = category.id_category ";
		
	$query .= " LIMIT $_start, $_limit ";
    
    $rs = mysql_query($query);
    //error_log($query.mysql_error());
    return $rs;
	
}

function count_comparison_status($dept)
{
// THIS QUERY TO MAKE A SURE THAT THERE IS STATUS 'Retired/Scrapped' OR NOT
	$status_name = 'Retired/Scrapped';
	$q = "SELECT * FROM status WHERE status_name='".$status_name."'";
	$qw = mysql_query($q);
	$qr = mysql_fetch_array($qw);
	$stts = $qr['id_status'];
	if($stts > 0){
		$w = @$qr['status_name'];
	} else {
		$w = $status_name;
	}
    
    
    $query  = "
			SELECT count(e.serial_no) as total, e.id_department, e.id_owner, e.id_category ,(i.status_name) as item_status, (e.status_name) as temporary_status, 
				(e.id_status) as id_status_itemtemporaries, (i.id_status) as id_status_item 
			FROM 
				(SELECT item_temporaries.*, status_name FROM item_temporaries LEFT JOIN status ON status.id_status = item_temporaries.id_status ) as e, 
				(SELECT item.*, status_name FROM item LEFT JOIN status ON status.id_status = item.id_status) as i, 
				category 
			WHERE 
				i.id_status != e.id_status AND i.serial_no = e.serial_no AND (i.status_name != '$w') "; 
    
    if ($dept > 0)
        $query .= " AND (i.id_department = $dept OR i.id_owner = $dept) AND category.id_department = $dept AND e.id_category = category.id_category ";
		
	
    
    $rs = mysql_query($query);
    $rec = mysql_fetch_array($rs);
    return $rec['total'];
}

function modify_status($serial_no){
	$date_now = date('Y-m-d H:i:s');
	$query_select = "SELECT * FROM item_temporaries WHERE serial_no = '".$serial_no."'";
	$execute_query_select = mysql_query($query_select);
	$item_temp = mysql_fetch_array($execute_query_select);
	
	$id_status = $item_temp['id_status'];
	$serial_no = $item_temp['serial_no'];
	
		$query_update = "UPDATE item SET id_status = '$id_status', status_update = '$date_now' WHERE serial_no = '$serial_no'";
		
		$execute_update = mysql_query($query_update);
		//error_log($query_insert.mysql_error());
		if($query_update){
			return $query_update;
		}
		
}
?>

