<?php

$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;


/* QUERY */
function count_frequecy_by_username($id_department = 0){
	$querys ="
		SELECT count(name) 
		FROM loan_item li, loan_out lo 
		WHERE li.id_loan = lo.id_loan
		";
	
	if($id_department > 0){
	$querys .= " AND lo.id_department =".$id_department." ";
	}
	
	$querys .= " GROUP BY lo.name";
	$count = mysql_query($querys);
	$sql = mysql_fetch_array($count);
	
	$counts = $sql[0];
	return $counts;

}

function get_frequency_loan_by_username($orderby = 'total', $sort = 'desc', $start = 0, $limit = 10, $id_department = null){
	$query ="
		SELECT COUNT(lo.name) AS total, lo.* 
		FROM loan_item li, loan_out lo 
		WHERE li.id_loan = lo.id_loan
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
	$query .= " AND lo.id_department =".$id_department." ";
	}

	$query .= " GROUP BY lo.name ORDER BY ".$orderby." ".$sort."  LIMIT ".$start.",".$limit;

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

	
$total_item = count_frequecy_by_username( $id_department);
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
$order_link = './?mod=report&sub=item&act=view&term=loan&by=username&chgord=1&page='.$_page.'&ordby=';

?>

<h3>Items Frequency of Loan By Username</h3>
<br>
<table cellpadding="2" cellspacing="1" class="itemlist middle" width="500">
<tr>
	<th width="30px">No</th>
	<th>Username</th>
	<th <?php echo ($_orderby == 'total') ? $row_class : null ?>> <a href="<?php echo $order_link ?>total">Total</a> </th>
	
</tr>
<?php  
	$no = 0;
	
	$query_process = get_frequency_loan_by_username($_orderby , $sort_order , $_start , $_limit , $id_department );
	//echo $query_process;
	while($rec = mysql_fetch_array($query_process)){
	$no++;
	$class = ($no % 2 == 0) ? 'class="alt"' : 'class="normal"';
 ?>
<tr <?php echo $class; ?>>
	<td><?php echo $no; ?></td>
	<td><?php echo $rec['name']; ?></td>
	<td align=center width=100px> <a href="?mod=report&sub=item&act=view&term=loan&by=viewlistloan&name=<?php echo $rec['name']?>"><?php echo $rec['total']; ; ?></a></td>
	
	
</tr>
<?php } ?>
</table>

<!--
<?php echo make_paging($_page, $total_page, './?mod=report&sub=item&act=view&term=loan&by=username&page=');?>
-->
