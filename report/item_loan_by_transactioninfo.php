<?php

$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;


/* QUERY */
function count_frequecy($id_department = 0){
	$querys ="
		SELECT COUNT( * ) AS total, item . *, 
			category.category_name, department.department_name, brand.brand_name
		FROM loan_out
		JOIN loan_item ON loan_item.id_loan = loan_out.id_loan
		LEFT JOIN item ON item.id_item = loan_item.id_item
		LEFT JOIN category ON item.id_category = category.id_category
		LEFT JOIN brand ON item.id_brand = brand.id_brand
		LEFT JOIN department ON item.id_department = department.id_department
		";
	
	if($id_department > 0){
	$querys .= " WHERE item.id_department =".$id_department." ";
	}

	$querys .= " GROUP BY id_item";

	$count = mysql_query($querys);
	$sql = mysql_fetch_array($count);
	
	$counts = $sql[0];
	return $counts;

}

function get_frequency_loan($orderby = 'total', $sort = 'desc', $start = 0, $limit = 10, $id_department = null){
	$query ="
		SELECT COUNT( loan_item.id_item ) AS total, item . *, 
			category.category_name, department.department_name, brand.brand_name
		FROM loan_out
		JOIN loan_item ON loan_item.id_loan = loan_out.id_loan
		LEFT JOIN item ON item.id_item = loan_item.id_item
		LEFT JOIN category ON item.id_category = category.id_category
		LEFT JOIN brand ON item.id_brand = brand.id_brand
		LEFT JOIN department ON item.id_department = department.id_department
		";
	
	if($id_department > 0){
	$query .= " WHERE item.id_department =".$id_department." ";
	}

	$query .= " GROUP BY id_item ORDER BY ".$orderby." ".$sort."  LIMIT ".$start.",".$limit;

	$rs = mysql_query($query);
	return $rs;
}
/* END OF QUERY*/



if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
	$order_status = array('total' => 'desc');
	
$_changeorder = isset($_GET['chgord']) ? true : false;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'total';
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_limit = RECORD_PER_PAGE;
$_start = 0;

	
$total_item = count_frequecy( $id_department);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;


/*ORDER LINK HERE*/
$sort_order = $order_status[$_orderby];
if ($_changeorder)
    $sort_order = ($order_status[$_orderby] == 'desc') ? 'asc' : 'desc';//$sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;
$buffer = ob_get_contents();
ob_clean();
$_SESSION['ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=report&sub=item&act=view&term=loan&by=transactioninfo&chgord=1&page='.$_page.'&ordby=';

?>

<h3>Items Frequency of Loan</h3>

<table cellpadding="2" cellspacing="1" class="itemlist" width="100%">
<tr>
	<th>No</th>
	<th>Asset No</th>
	<th>Serial No</th>
	<th>Category</th>
	<?php if ($id_department > 0) {} else {?>
	<th>Department</th>
	<?php } ?>
	<th>Brand</th>
	<th>Model No</th>
	<th <?php echo ($_orderby == 'total') ? $row_class : null ?>> <a href="<?php echo $order_link ?>total">Total</a> </th>
	
</tr>
<?php  
	$no = 0;
	
	$query_process = get_frequency_loan($_orderby , $sort_order , $_start , $_limit , $id_department );
	//echo $query_process;
	while($rec = mysql_fetch_array($query_process)){
	$no++;
	$class = ($no % 2 == 0) ? 'class="alt"' : 'class="normal"';
 ?>
<tr <?php echo $class; ?>>
	<td><?php echo $no; ?></td>
	<td><?php echo $rec['asset_no']; ?></td>
	<td><?php echo $rec['serial_no']; ; ?></td>
	<td><?php echo $rec['category_name']; ; ?></td>
	<?php if ($id_department > 0) {} else {?>
	<td><?php echo $rec['department_name']; ; ?></td>
	<?php } ?>
	<td><?php echo $rec['brand_name']; ; ?></td>
	<td><?php echo $rec['model_no']; ; ?></td>
	<td align=center width=30> <a href="?mod=item&act=history&id=<?php echo $rec['id_item']?>"><?php echo $rec['total']; ; ?></a></td>
	
	
</tr>
<?php } ?>
</table>

<!--
<?php echo make_paging($_page, $total_page, './?mod=report&sub=item&act=view&term=loan&by=transactioninfo&page=');?>
-->
