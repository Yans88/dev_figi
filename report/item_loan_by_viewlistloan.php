<?php

$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

$name = htmlspecialchars($_GET['name'], ENT_QUOTES);
if(empty($name)){
	echo "<script>alert('Please choose a name first.');location.href='?mod=report'</script>";
}



/* QUERY */
function count_loan_by_username($id_department = 0, $name = null){
	$querys ="
		SELECT COUNT(*)
		FROM loan_item li, loan_out lo
		WHERE li.id_loan = lo.id_loan
		AND lo.name = '".$name."'
		";
	
	if($id_department > 0){
	$querys .= " AND lo.id_department =".$id_department." ";
	}

	$count = mysql_query($querys);
	$sql = mysql_fetch_array($count);
	
	$counts = $sql[0];
	return $counts;

}

function get_loan_by_username($orderby = 'asset_no', $sort = 'desc', $start = 0, $limit = 10, $id_department = null, $name){
	$query = "
		SELECT lo . * , item . *, category.category_name, department.department_name
		FROM loan_item li, loan_out lo, item
		LEFT JOIN category ON item.id_category = category.id_category
		LEFT JOIN department ON item.id_department = department.id_department
		WHERE li.id_loan = lo.id_loan
		AND item.id_item = li.id_item
		AND lo.name = '".$name."'
		";
		
	if($id_department > 0){
	$query .= " AND lo.id_department =".$id_department." ";
	}

	$query .= "ORDER BY ".$orderby." ".$sort."  LIMIT ".$start.",".$limit;

	$rs = mysql_query($query);
	return $rs;
}
/* END OF QUERY*/



if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
	$order_status = array('id_loan' => 'desc', 'asset_no' => 'desc', 'category_name' => 'desc');
	
$_changeorder = isset($_GET['chgord']) ? true : false;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_limit = RECORD_PER_PAGE;
$_start = 0;

	
$total_item = count_loan_by_username( $id_department, $name);
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
$order_link = './?mod=report&sub=item&act=view&term=loan&by=viewlistloan&name='.$name.'&chgord=1&page='.$_page.'&ordby=';

?>

<h3>Items Frequency of Loan - <?php echo $name;?></h3>

<table>
<tr>
	<td colspan="4"><h3>Total Item : <?php echo $total_item; ?> | </h3> </td>
	<td colspan="2"><a class="button" href="?mod=report&sub=item&act=view&term=loan&by=username">Back</a></td>
</tr>
</table>
<table cellpadding="2" cellspacing="1" class="itemlist" width="100%">

<tr>
	<th width="30px">No</th>
	<th <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>> <a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
	<th>Serial No</th>
	<th <?php echo ($_orderby == 'category_name') ? $row_class : null ?>> <a href="<?php echo $order_link ?>category_name">Category</a></th>
	<?php if($id_department > 0) {} else { ?>
	<th>Department</th>
	<?php } ?>
	<th <?php echo ($_orderby == 'id_loan') ? $row_class : null ?>> <a href="<?php echo $order_link ?>id_loan">Id Loan</a> </th>
	
</tr>
<?php  
	$no = $_start;
	
	$query_process = get_loan_by_username($_orderby , $sort_order , $_start , $_limit , $id_department, $name);
	//echo $query_process;
	while($rec = mysql_fetch_array($query_process)){
	$no++;
	$class = ($no % 2 == 0) ? 'class="alt"' : 'class="normal"';
	
 ?>
<tr <?php echo $class; ?>>
	<td><?php echo $no; ?></td>
	<td><?php echo $rec['asset_no']; ?></td>
	<td><?php echo $rec['serial_no']; ?></td>
	<td><?php echo $rec['category_name']; ?></td>
	<?php if($id_department > 0) {} else { ?>
	<td><?php echo $rec['department_name']; ?></td>
	<?php } ?>
	<td align=center> LR<?php echo $rec['id_loan']; ; ?></td>
	
	
</tr>
<?php } ?>
</table>

<!--
<?php echo make_paging($_page, $total_page, '.?mod=report&sub=item&act=view&term=loan&by=viewlistloan&name='.$name.'&page=');?>

-->


