<?php

ob_clean();
require 'header_popup.php';

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$id_location = isset($_GET['loc']) ? $_GET['loc'] : 0;
$regno = isset($_GET['regno']) ? $_GET['regno'] : 0;$template = isset($_GET['template']) ? $_GET['template'] : 0;

if (!empty($_POST['selected_item'])){
	$id_item = $_POST['selected_item']; 
	if (!empty($id_item)){
		$query = "UPDATE facility_fixed_item SET id_item = '$id_item' WHERE id_facility = '$id_location' AND register_number = '$regno' AND template = '$template'";
		mysql_query($query);		
		//error_log(mysql_error().$query);
		$ok = mysql_affected_rows();
	}
	echo '<script>parent.location.reload();</script>';
	exit;
}
$rs = mysql_query("SELECT location_name FROM location where id_location = '$id_location'");
$row = mysql_fetch_row($rs);
$location_name = $row[0];

$id_department = USERDEPT;
$filters = !empty($_POST) ? $_POST : array();
$query = "SELECT i.*, c.category_name, b.brand_name 
			FROM item i 
			LEFT JOIN category c ON i.id_category = c.id_category
			LEFT JOIN brand b ON i.id_brand = b.id_brand
			LEFT JOIN facility_fixed_item fi ON fi.id_item = i.id_item 
			WHERE i.id_location = '$id_location' AND i.id_department = '$id_department' AND c.category_type = 'EQUIPMENT' AND fi.id_item IS NULL ";
$rs = mysql_query($query);
//echo mysql_error().$query;
$total_item = mysql_num_rows($rs);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

?>

<h4>Select item to be assigned to '<?php echo $location_name?>' with registration  number #<?php echo $regno?></h4>
<?php
/*
<form id="frm_search" method="post">
<div class="filter" style="width:97%" style="display: none">
Search by Asset No<input type="text" name="asset_no" value="<?php echo $asset_no?>">
<button>go</button>
</div>
</form>
*/
if ($total_item>0){
?>
<form method="post">
	<table class='itemlist' cellpadding=3 cellspacing=1 width='99%'>
		<tr>
			<th width=40>No</th><th>Asset No</th><th>Serial No</th><th>Category</th><th>Brand</th>
			<th width=40>Action</th>
		</tr>
<?php 
	$query .= " ORDER BY asset_no ASC LIMIT $_start, $_limit";
	$rs = mysql_query($query);
	$counter = $_start;
	
	while($data = mysql_fetch_array($rs)){
		$counter++;
		$row_class = ($counter % 2 == 0) ? 'alt' : '';
		$cb = "<button name='selected_item' value='$data[id_item]'>select</button>";
		echo "
			<tr class='$row_class'>
				<td class='right'>$counter. &nbsp; </td><td>$data[asset_no]</td><td>$data[serial_no]</td>
				<td>$data[category_name]</td><td>$data[brand_name]</td>
				<td align='center'>$cb </td>
			</tr>
		";
	}
	if ($total_page>1){
?>
		<tr>
			<td colspan=8 class="center border-top pagination">
			<?php
				echo make_paging($_page, $total_page, './?mod=facility&sub=fixed_item_assign&loc='.$id_location.'&regno='.$regno.'&page=');
			?>
			</td>
		</tr>
	<?php 
	} // if there are more pages, show page navigation 
	?>
	</table>
	<div class="right space5-top" style="width:99%"><button id="assign" name="assign" type="submit" value=1>Assign Selected Item</button></div>
</form>
<?php
} else
	echo '<br><br><p class="msg info middle">Data is not available!</p>';

// ================= FUNCTIONS =================

function get_students($filters = array(), $start = 0, $limit = 10){
	$wheres = array();
	if (!empty($filters['nric'])) $wheres['nric'] = $filters['nric'];
	if (!empty($filters['full_name'])) $wheres[] = " full_name like '%$filters[full_name]%' ";

	$query = "SELECT * FROM students ";
	if (!empty($wheres)) $query .= ' WHERE '. implode(' AND ', $wheres);
	$query .= " ORDER BY full_name  ASC LIMIT $start, $limit";
		
	$mysql = mysql_query($query);
	//error_log(mysql_error().$query);
	return $mysql;

}

function count_student(){

	$query = "SELECT count(*) as total FROM students ";
	
	$mysql = mysql_query($query);
	$fetch = mysql_fetch_array($mysql);
	
	return $fetch['total'];
	
}

/*
<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />

	$(document).ready(function() {
		$('.fancybox').fancybox({padding: 5, width: 440, height: 290});
	});


*/
?>

<script>
	$('#cbc').change(function(){
		var is_checked = this.checked;
		$('.cb').each(function(){
			this.checked = is_checked;
		});
	});

	$('#assign').click(function(){
		
	});
</script>
	
