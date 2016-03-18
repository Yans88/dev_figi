<?php
if (!defined('FIGIPASS')) exit;
$dept = defined('USERDEPT') ? USERDEPT : 0;

if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('asset_no' => 'asc', 
                          'serial_no' => 'asc', 
                          'category_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'brand_name' =>  'asc', 
                          'model_no' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;
$_status = !empty($_GET['id_status']) ? $_GET['id_status'] : 0;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : 0;


$_limit = RECORD_PER_PAGE;
$_start = 0;

function count_item_by_category_status($category = 0, $status = 0, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) FROM item i 
                LEFT JOIN category c ON i.id_category=c.id_category 
                WHERE category_type = 'EQUIPMENT' ";
    if ($category>0) $query .= ' AND i.id_category = '.$category ;
    if ($status>0) $query .= ' AND id_status = '.$status;
    if ($dept>0) $query .= ' AND i.id_department = '.$dept;
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_items_by_category_status($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $category = 0, $status = 0, $dept = 0)
{
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
                FROM item 
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN department ON category.id_department = department.id_department 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                WHERE category_type = 'EQUIPMENT' ";
    if ($category>0) $query .= ' AND item.id_category = '.$category ;
    if ($status>0) $query .= ' AND item.id_status = '.$status;
    if ($dept>0) $query .= ' AND item.id_department = '.$dept;
    $query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
   // echo $query.mysql_error();
	return $rs;
}

$total_item = count_item_by_category_status($_category, $_status, $dept);
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
$order_link = './?mod=report&sub=item&term=list&by=category_status&chgord=1&id_category='.$_category.'&id_status='.$_status.'&page='.$_page.'&ordby=';

$categories[0] ='-- all categories --';
$categories += get_category_list('EQUIPMENT', $dept);
$statuses[0] = '-- all statuses --';
$statuses += get_status_list();


?>
<br/>
<div id="submodhead" >
<form method="get">
<input type="hidden" name="mod" value="report">
<input type="hidden" name="sub" value="item">
<input type="hidden" name="term" value="list">
<input type="hidden" name="by" value="category_status">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div style="text-align: left; float: left; width: 80%;  font-weight:bold" >
    Category: <?php echo build_combo('id_category', $categories, $_category);?> &nbsp; 
    Status: <?php echo build_combo('id_status', $statuses, $_status);?> &nbsp;
    <input type="image" src="images/loupe.png" class="searchsubmit" >
    <br/>
</div>
<?php if ($total_item>0){?>
<div style="float: right">
    <a class="button" href="./?mod=report&sub=item&term=export&by=category_status&chgord=1&id_category=<?php echo $_category?>&id_status=<?php echo $_status?>&ordby=<?php echo $_orderby?>">Export</a>
</div>
<?php } ?>
</div>
</form>
<br>&nbsp;
<br>
<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th  <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
    <th  <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
  <th   <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>category_name">Category</a></th>
  <th  <?php echo ($_orderby == 'brand_name') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>brand_name">Brand</a></th>
  <th  <?php echo ($_orderby == 'model_no') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>model_no">Model No</a></th>
  <th width=50>Action</th>
</tr>

<?php
$rs = get_items_by_category_status($_orderby, $sort_order, $_start, $_limit, $_category, $_status, $dept);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
    $edit_link = null;
    if (!SUPERADMIN && $i_can_update && $i_can_delete && ($rec['id_status']!=CONDEMNED))
    $_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
    echo <<<DATA
    <tr $_class>
    <td align="right">$counter</td>
    <td>$rec[asset_no]</td>
    <td>$rec[serial_no]</td>
    <td title="Department: $rec[department_name]">$rec[category_name]</td>
    <td title="Manufacturer: $rec[manufacturer_name]">$rec[brand_name]</td>
    <td >$rec[model_no]</td>
    <td align="center" nowrap>
    <a href="?mod=item&act=view&id=$rec[id_item]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
    </td>
    </tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=10 class="pagination">';
echo make_paging($_page, $total_page, "./?mod=report&sub=item&term=list&by=category_status&id_category=$_category&id_status=$_status&ordby=$_orderby&page=");
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>
