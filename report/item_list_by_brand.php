<?php
if (!defined('FIGIPASS')) exit;

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
$_id = !empty($_GET['id_brand']) ? $_GET['id_brand'] : 0;
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$brands[0] = '-- all brands --';
$brands += get_brand_list();

/*
if ($_id==0){
    $brand_keys = array_keys($brands);
    $_id = $brand_keys[0];
}
  */  
$_searchby = 'id_brand';
$_searchtext = $_id;
$total_item = count_item($_searchby, $_searchtext);
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
$order_link = './?mod=report&sub=item&term=list&by=brand&chgord=1&searchby='.$_searchby.'&id_brand='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div id="submodhead" >
<script>
var dept = '<?php echo $dept?>';
function reload_brand(me)
{
	var form = me.form;
	form.submit();
}
</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<form method="get">
<input type="hidden" name="mod" value="report">
<input type="hidden" name="sub" value="item">
<input type="hidden" name="term" value="list">
<input type="hidden" name="by" value="brand">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div style="text-align: left; float: left; width: 80%;  font-weight:bold" >
    List item by Brand <?php echo build_combo('id_brand', $brands, $_id, 'reload_brand(this)');?>
</div>
<?php if($total_item>0){?>
<div style="float: right">
    <a class="button" href="./?mod=report&sub=item&term=export&by=brand&chgord=1&searchby=id_brand&id_brand=<?php echo $_searchtext?>&ordby=<?php echo $_orderby?>">Export</a>
</div>
<?php } ?>
</div>
</form>
<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
	<th <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
  <th <?php echo ($_orderby == 'model_no') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>model_no">Model No</a></th>
  <th width=80>Purchase Price</th>
  <th width=80>Purchase Date</th>
  <th width=80>Warranty End Date</th>
  <th width=110>Status</th>
  <th width=50>Action</th>
</tr>

<?php
$rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
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
	<td >$rec[model_no]</td>
	<td align="center">$rec[cost]</td>
	<td align="center">$rec[date_of_purchase_fmt]</td>
	<td align="center">$rec[warranty_end_date_fmt]</td>
	<td >$rec[status_name]</td>
	<td align="center" nowrap>
	<a href="?mod=item&act=view&id=$rec[id_item]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	</td>
	</tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=10 class="pagination">';
echo make_paging($_page, $total_page, './?mod=report&sub=item&term=list&by=brand&searchby=id_brand&id_brand='.$_searchtext.'&ordby'.$_orderby.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<br>&nbsp;<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>
