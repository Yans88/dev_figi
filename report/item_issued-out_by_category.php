<?php
if (!defined('FIGIPASS')) exit;

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

$total_item = count_issued_item(USERDEPT);
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
$order_link = './?mod=report&sub=item&act=view&term=issued-out&by=category&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

echo '<h2>Item Issue-Out Report</h2>';

    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=2 cellspacing=1 class="itemlist" >
<tr >
  <th width=30 >No</th>
  <th width=110  <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
  <th width=100  <?php echo ($_orderby == 'model_no') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>model_no">Model No</a></th>
  <th  <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>category_name">Category</a></th>
  <th  <?php echo ($_orderby == 'dst_department') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>dst_department">Issue-Out To<br/>(Department - Category)</a></th>

</tr>
<?php
/*
  <th width=80  <?php echo ($_orderby == 'status_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>status_name">Status</a></th>

*/

$data = get_issued_items($dept, $_start, $_limit, $_orderby, $sort_order, $_searchby, $_searchtext);
$counter = $_start+1;
foreach ($data as $rec)
{
	$_class = ($counter % 2 == 0) ? 'class="item alt"':'class="item normal"';
	echo <<<DATA
	<tr $_class id="item-$rec[id_item]">
	<td align="right">$counter</td>
	<td title="Serial No.: $rec[serial_no]">$rec[asset_no]</td>
	<td >$rec[model_no]</td>
	<td>$rec[src_category_name]</td>
	<td title="$rec[category_name]">$rec[department_name] - $rec[category_name]</td>
	</tr>
DATA;
  $counter++;
}/*
	<td >$rec[status]</td>
	<td align="center" nowrap>
	<a href="?mod=item&act=view&id=$rec[id_item]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	</td>
*/
echo '<tr ><td colspan=8 class="pagination">';
echo make_paging($_page, $total_page, './?mod=report&sub=item&act=view&term=issued-out&by=category&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<style>
.item:hover{cursor:pointer;}
</style>
<script>
    $('#searchtext').focus();
    $('.item').click(function(e){
        var id = this.id.substring(5);
        location.href="./?mod=report&sub=item&act=view&term=issued-out&by=detail&id="+id;
    });
</script>
