<?php

$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;


/* QUERY */
function count_frequecy_by_category($id_department = 0){
	$querys ="
		SELECT count( * ) 
		FROM loan_item li, loan_out lo, item
		LEFT JOIN category ON item.id_category = category.id_category
		WHERE li.id_loan = lo.id_loan
		AND item.id_item = li.id_item
		";
	
	if($id_department > 0){
	$querys .= " AND lo.id_department =".$id_department." ";
	}

	$count = mysql_query($querys);
	$sql = mysql_fetch_array($count);
	
	$counts = $sql[0];
	return $counts;

}

function get_frequecy_by_category($orderby = 'total', $sort = 'desc', $start = 0, $limit = 10, $id_department = null){
	$query ="
		SELECT count( item.id_category ) AS total , category.category_name, item. *
		FROM loan_item li, loan_out lo, item
		LEFT JOIN category ON item.id_category = category.id_category
		WHERE li.id_loan = lo.id_loan
		AND item.id_item = li.id_item
		";
	/*
	SELECT count( loan_request.requester ) AS total_frequence, user.full_name
	FROM loan_request
	LEFT JOIN user ON user.id_user = loan_request.requester
	GROUP BY loan_request.requester
	ORDER BY total_frequence
	LIMIT 0 , 30
	*/
	if($id_department > 0){
	$query .= " AND lo.id_department =".$id_department." AND category.id_department = ".$id_department."";
	}

	$query .= " GROUP BY item.id_category ORDER BY ".$orderby." ".$sort."  LIMIT ".$start.",".$limit;

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

	
$total_item = count_frequecy_by_category($id_department);
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
$order_link = './?mod=report&sub=item&act=view&term=loan&by=category&chgord=1&page='.$_page.'&ordby=';

?>

<h3>Items Frequency of Loan By Category</h3>
<br>
<table cellpadding="2" cellspacing="1" class="itemlist middle" width="500">
<tr>
	<th width="30px">No</th>
	<th>Category Name</th>
	<th <?php echo ($_orderby == 'total') ? $row_class : null ?>> <a href="<?php echo $order_link ?>total">Total</a> </th>
	
</tr>
<?php  
	$no = 0;
	
	$query_process = get_frequecy_by_category($_orderby , $sort_order , $_start , $_limit , $id_department );
	//echo $query_process;
	while($rec = mysql_fetch_array($query_process)){
	$no++;
	$class = ($no % 2 == 0) ? 'class="alt"' : 'class="normal"';
 ?>
<tr <?php echo $class; ?>>
	<td><?php echo $no; ?></td>
	<td><?php echo $rec['category_name']; ?></td>
	<td align=center width=30> <a href="?mod=report&sub=item&act=view&term=loan&by=viewlistcategory&id_category=<?php echo $rec['id_category']?>"><?php echo $rec['total']; ; ?></a></td>
	
	
</tr>
<?php } ?>
</table>
<!--
<div style="width: 100px; margin-top: 10px" class="middle">
<?php echo make_paging($_page, $total_page, './?mod=report&sub=item&act=view&term=loan&by=username&page=');?>
</div>
-->
