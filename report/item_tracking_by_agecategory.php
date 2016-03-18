
<?php
$id_department = (!SUPERADMIN) ? USERDEPT : 0;
$cat = $_GET['cat'];
$no = $_GET['no'];

//echo $cat. "<br />";
//echo $id_department;

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
//$category_name_title = 
$counter = 0;

?>
<h3>Item Age Report - <?php echo $category_title;?></h3>
<table class="report" width="100%" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=7 align="right"><a class='button' href='./?mod=report&sub=item&act=view&term=export&by=ageofcategory&cat=<?php echo $cat; ?>&no=<?php echo $no;?>'>Export</a></td>
</tr>
<tr><th>No</th> <th>Asset No</th> <th>Serial No</th><th>Category</th><th>Brand</th><th>Model No</th><th>Date Of Purchase</th><th>Warranty End Date</th><th>Status</th><th>Action</th></tr>

	<?php while ($rec = mysql_fetch_array($rs)) { ?>
			<?php 
			$counter++;
			$class = ($counter % 2 == 0) ? 'class="alt"' : 'class="normal"'; ?>
			<tr <?php echo $class; ?>>
			<td><?php echo $counter; ?></td>
			<td><?php echo $rec['asset_no'];?></td>
			<td><?php echo $rec['serial_no'];?></td>
			<td><?php echo $rec['category_name'];?></td>
			<td><?php echo $rec['brand_name'];?></td>
			<td><?php echo $rec['model_no'];?></td>
			<td><?php echo $rec['date_of_purchase'];?></td>
			<td><?php echo $rec['warranty_end_date'];?></td>
			<td><?php echo $rec['status_name'];?></td>
			<td align="center"><a href="?mod=item&act=view&id=<?php echo $rec['id_item'];?>" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a></td>
			</tr>
	<?php } ?>



</table>
