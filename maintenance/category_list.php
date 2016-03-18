<?php
if (!defined('FIGIPASS')) exit;
if (!empty($_SESSION['CATEGORY_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['CATEGORY_ORDER_STATUS']);
else
    $order_status = array('equipment' => 'asc', 'service' => 'asc');
$order_by = 'category_name';
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_type = 'equipment';
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$total_item = count_category($_type, USERDEPT);
$total_page = ceil($total_item/$_limit);

if ($_page > 0) $_start = ($_page-1) * $_limit;
if ($_page > $total_page) 	$_page = $total_page;
$sort_order = $order_status[$_type];
if ($_sort > 0) {
	$sort_order = ($order_status[$_type] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$_type] = $sort_order;
	$_SESSION['CATEGORY_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';

echo '<h4>Item Category</h4>';
if ($i_can_create && !SUPERADMIN) {
?>
<div style="text-align: left; width:700px">
<a class="button" href="./?mod=item&sub=category&act=edit&type=<?php echo $_type?>">
	Add New Category</a>
</div>
<?php
}
?>
<table width=700 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th <?php echo $row_class ?>>
	<a href="./?mod=item&sub=category&type=<?php echo $_type?>&page=<?php echo $_page?>&sort=1">Category</a></th>
  <th width=60>Code</th>
<?php if ($_type == 'equipment') { ?>
  <th width=120>Condemn Period (months)</th>
<?php }// is equipment ?>
  <th width=60>Loanable</th>
  <th width=80>Loan Period (days)</th>
  <th width=60>Action</th>
</tr>

<?php
$counter = $_start;
$rs = get_categories($_type, $sort_order, $_start, $_limit, USERDEPT);
while ($rec = mysql_fetch_array($rs))
{
  $edit_link = '<img class="icon" src="images/editx.png" alt="edit"> <img class="icon" src="images/deletex.png" alt="edit">';
  if (!SUPERADMIN && $i_can_update)
    $edit_link =<<<EDIT
    <a href="?mod=item&sub=category&act=edit&id=$rec[id_category]" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="?mod=item&sub=category&act=del&id=$rec[id_category]" 
       onclick="return confirm('Are you sure you want to delete $rec[category_name]?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
  $counter++;
  $condemn_period = ($_type == 'equipment') ? "<td align='center'>$rec[condemn_period]</td>" : null;
  $loanable = $rec['loanable']=='1' ? 'Yes' : 'No'; /** +here **/
  $loan_period = ($rec['loan_period']==0) ? 'Unlimited' : $rec['loan_period'];
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
    <td align="right">$counter.</td>
    <td>$rec[category_name]</td>
    <td>$rec[category_code]</td>
    $condemn_period
	<td align="center">$loanable</td>
	<td align="center">$loan_period</td>
    <td align="center">$edit_link</td>
  </tr>
DATA;
}

echo '<tr ><td colspan=7 class="pagination">';
echo make_paging($_page, $total_page, './?mod=item&sub=category&act=list&type='.$_type.'&page=');
echo  '</td></tr>';

?>

</table><br/>
