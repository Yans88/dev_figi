<?php
if (!defined('FIGIPASS')) exit;

$id_category = $_GET['id_category'];
$id_dept = defined('USERDEPT') ? USERDEPT : 0;

if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('asset_no' => 'asc', 
                          'category_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'brand_name' =>  'asc', 
                          'model_no' =>  'asc', 
                          'status_name' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = get_count_item_by_category($id_dept, $id_category);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

$sort_order = $order_status[$_orderby];
if ($_changeorder)
    $sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;
$buffer = ob_get_contents();
ob_clean();
$_SESSION['ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=report&sub=item&act=list_by_status_transferred&id_category='.$id_category.'&chgord=1&page='.$_page.'&ordby=';
						  
?>
<h2>Inventory Tracking Report - Transferred <?php echo get_category_name($id_category); ?></h2>


<table id='itemlist'>
	<tr>
		<td align='left'>
			<h3>[Total Items: <?php echo $total_item;?> ] </h3>
		</td>
		<td align='right'>
			<a class="button" href="?mod=report&sub=item&act=view&term=tracking&by=category">Back</a>
		</td>
	</tr>
</table>
<table class="itemlist" id='itemlist' cellpadding=3 >

<tr>
	<th width=30>No</th>
	<th <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>><a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
	<th <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>><a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
	
	<th <?php echo ($_orderby == 'brand_name') ? $row_class : null ?>><a href="<?php echo $order_link ?>brand_name">Brand</a></th>
	<th <?php echo ($_orderby == 'model_no') ? $row_class : null ?>><a href="<?php echo $order_link ?>model_no">Model</a></th>
	<th>Date Of Purchase</th>
	<th>Warranty End Date</th>
	<th <?php echo ($_orderby == 'issued_to_name') ? $row_class : null ?>><a href="<?php echo $order_link ?>issued_to_name">Issued To</a></th>
	<th <?php echo ($_orderby == 'status_name') ? $row_class : null ?>><a href="<?php echo $order_link ?>status_name">Status</a></th>
	<th <?php echo ($_orderby == 'department_name') ? $row_class : null ?>><a href="<?php echo $order_link ?>department_name">Department</a></th>
	<th <?php echo ($_orderby == 'location_name') ? $row_class : null ?>><a href="<?php echo $order_link ?>location_name">Location</a></th>
	<th>Action</th>
</tr>

<?php

if($total_item == 0){
	echo "<tr><td colspan='12' align='center'> Data Not Available </td></tr>";

} else {
	
	$rs = get_item_by_category($_orderby, $sort_order, $_start, $_limit, $id_dept, $id_category );
	//print_r($rs);
	$counter = $_start+1;
	foreach($rs as $value){
		echo "<tr>
			<td align='center'> ".$counter." </td>
			<td align='center'> ".$value['asset_no']." </td>
			<td align='center'> ".$value['serial_no']." </td>
			<td align='center'> ".$value['brand_name']."</td>
			<td align='center'> ".$value['model_no']."</td>
			<td align='center'> ".$value['date_of_purchase_fmt']."</td>
			<td align='center'> ".$value['warranty_end_date_fmt']."</td>
			<td align='center'> ".$value['issued_to_name']."</td>
			<td align='center'> ".$value['status_name']."</td>
			<td align='center'> ".$value['department_name']."</td>
			<td align='center'> ".$value['location_name']."</td>
			<td align='center'> <a href='?mod=item&act=view&id=$value[id_item]' title='view'><img class='icon' src='images/loupe.png' alt='view' ></a></td>
		</tr>";
		$counter++;
	}
}

?>
</table>
<table id='itemlist'>
<tr><td align='center'><?php echo make_paging($_page, $total_page, './?mod=report&sub=item&act=list_by_status_transferred&id_category='.$id_category.'&chgord=1&page='); ?></td></tr>
</table>



<?php
//QUERY
function get_item_by_category($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $id_dept = 0, $id_category = null){
$fmt = '%d-%b-%Y';
$query  = "SELECT item.*, status.status_name, brand.brand_name, category.category_name, vendor.vendor_name, manufacturer.manufacturer_name, department.department_name,
               DATE_FORMAT(date_of_purchase, '$fmt') date_of_purchase_fmt,
               DATE_FORMAT(warranty_end_date, '$fmt') warranty_end_date_fmt,
               location_name, full_name issued_to_name  ,item_store_type.title store_name
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON item.id_department = department.id_department 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               LEFT JOIN item_store_type ON item.id_store=item_store_type.id_store
               LEFT JOIN user ON item.issued_to=user.id_user 
               LEFT JOIN location ON item.id_location=location.id_location   
               WHERE category.id_category = '".$id_category."' ";
   if($id_dept > 0){
	$query .= " AND item.id_owner ='$id_dept' AND item.id_department != '$id_dept'";
   }
   
   $query .= " ORDER BY ".$orderby." ".$sort." LIMIT ".$start.", ".$limit;
   
   $rec = array();
   $rs = mysql_query($query);
   while($ak = mysql_fetch_array($rs)){
	$rec[] = $ak;
   }
   return $rec;

}


function get_count_item_by_category($id_dept = 0, $id_category = null){
$query  = "SELECT count(item.serial_no) as total
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               WHERE category.id_category = '".$id_category."' ";
   if($id_dept > 0){
		$query .= " AND item.id_owner ='$id_dept' AND item.id_department != '$id_dept'";
   }

   $rs = mysql_query($query);
   $rec = mysql_fetch_array($rs);
   
   return $rec['total'];
   
}

?>


