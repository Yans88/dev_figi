<?php

$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$total_item = count_comparison_asset_no($dept);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;
    
?>

<div class="clear"></div>

<form method="post" action="">
  <h2>Modify Asset Number Which Available in Figi</h2>
	<table>
		<tr>
			<td colspan=5>
				<a class="button" href="./?mod=item&sub=comparison_asset&sub=comparison_asset"> < Top Menu </a>
				<a class="button" href="./?mod=item&sub=comparison_asset&act=modifylocations"> Next > Modify Locations </a>
			</td>
		</tr>
	</table>
	<table id="itemlist" cellpadding=2 cellspacing=0 class="itemlist">
		<tr height=30>
		  <th width=30>No</th>
		  <th width=110>Asset Number In File</th>
		  <th width=100>Asset Number In Figi</th>
		  <th width=100>Serial No In Figi/ File</th>
		  <th width=100><input type="checkbox" name="all" id="checkAll" onClick="checkAll_item(<?php echo $total_item; ?>)"> <span id='check_status'>Check All</span> </th>
		</tr>
		
	<?php
		if($total_item == 0){
				echo"
				<tr><td colspan='5' align='center'>Data Not Available !</td></tr>
				";
		} else {
			$counter = $_start+1;
			$check = 1;
			
			$rs = get_comparison_asset_no($dept, $_start, $_limit);
			//echo $rs;
			while($rec = mysql_fetch_array($rs)){
			
				$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
				
				
				echo"<tr ".$_class.">";
				echo "
					<td align='center'>".$counter."</td>
					<td align='center'>".$rec['asset_no_from_itemtemporaries']."</td>
					<td align='center'>".$rec['asset_no_from_item']."</td>
					<td align='center'>".$rec['serial_no']."</td>
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
				<?php echo make_paging($_page, $total_page, './?mod=item&sub=comparison_asset&act=modifyasset&page=');?> 
			</td>
		</tr>
		<tr>
			<td>
			<div style='float:right;'><input type='submit' name='add_item' value='Modify Asset Number' class='button' onclick='return confirm("Are you sure want to Update item(s) selected ?")'></div>
			</td>
		</tr>
	</table>
		
</form>

<?php

if(@$_POST['add_item']){

	$a = @$_POST['checklist'];
	if(empty($a)){
		echo "<script>alert('Please selected the item file first !');location.href='./?mod=item&sub=comparison_asset&act=modifyasset'</script>";
	} else {
		
		$c = array();
		foreach($a as $b){
			$exec = modify_asset($b);
			$c[] = array($exec);
			$count = count($c);
		}
		
		echo "<script>alert('".$count." Item file(s) Updated Successfully.');location.href='./?mod=item&sub=comparison_asset&act=modifyasset'</script>";
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

function get_comparison_asset_no($dept, $_start = 0, $_limit = 10)
{
    
    $query = "	SELECT  item_temporaries.serial_no, item.serial_no, (item_temporaries.asset_no) as asset_no_from_itemtemporaries, 
						(item.asset_no) as asset_no_from_item, item_temporaries.id_category, item_temporaries.id_department, 
						item_temporaries.id_owner
				FROM item_temporaries, item, category
				WHERE item_temporaries.serial_no = item.serial_no AND item_temporaries.asset_no != item.asset_no AND item_temporaries.id_category = category.id_category";
    
    if ($dept > 0)
        $query .= " AND (item_temporaries.id_department = $dept OR item_temporaries.id_owner = $dept) AND category.id_department = $dept AND item_temporaries.id_category = category.id_category ";
		
	$query .= " LIMIT $_start, $_limit ";
    
    $rs = mysql_query($query);
    error_log($query.mysql_error());
    return $rs;
}


function count_comparison_asset_no($dept, $_start = 0, $_limit = 10)
{
    
    $query = "	SELECT  count(item_temporaries.serial_no) as total, item_temporaries.serial_no, item.serial_no, (item_temporaries.asset_no) as asset_no_from_itemtemporaries, 
						(item.asset_no) as asset_no_from_item, item_temporaries.id_category, item_temporaries.id_department, 
						item_temporaries.id_owner
				FROM item_temporaries, item, category
				WHERE item_temporaries.serial_no = item.serial_no AND item_temporaries.asset_no != item.asset_no ";
    
    if ($dept > 0)
        $query .= " AND (item_temporaries.id_department = $dept OR item_temporaries.id_owner = $dept) AND category.id_department = $dept AND item_temporaries.id_category = category.id_category ";
		
	$query .= " LIMIT $_start, $_limit ";
    
    $rs = mysql_query($query);
    $rec = mysql_fetch_array($rs);
    return $rec['total'];
}

function modify_asset($serial_no){
	$date_now = date('Y-m-d H:i:s');
	$query_select = "SELECT * FROM item_temporaries WHERE serial_no = '".$serial_no."'";
	$execute_query_select = mysql_query($query_select);
	$item_temp = mysql_fetch_array($execute_query_select);
	
	$asset_no = $item_temp['asset_no'];
	$serial_no = $item_temp['serial_no'];
		
		$query_update = "UPDATE item SET asset_no = '$asset_no', status_update = '$date_now' WHERE serial_no = '$serial_no'";
		
		$execute_update = mysql_query($query_update);
		//error_log($query_insert.mysql_error());
		if($query_update){
			return $query_update;
		}
		
}

?>
