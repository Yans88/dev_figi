<?php
if (!defined('FIGIPASS')) exit;
if (!empty($_SESSION['VENDOR_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['VENDOR_ORDER_STATUS']);
else
    $order_status = array('vendor_name' => 'asc');
$order_by = 'vendor_name';
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$total_item = count_vendor();
$total_page = ceil($total_item/$_limit);

if ($_page > 0)
	$_start = ($_page-1) * $_limit;
if ($_page > $total_page)
	$_page = $total_page;
$sort_order = $order_status[$order_by];
if ($_sort > 0) {
	$sort_order = ($order_status[$order_by] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$order_by] = $sort_order;
	$_SESSION['VENDOR_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';

if ($i_can_create && !SUPERADMIN) {
?>
<div style="text-align: left; width:800px">
<a class="button" href="./?mod=item&sub=vendor&act=edit"> Add New Vendor</a>
<a class="button" href="./?mod=item&sub=vendor&act=import"> Import Vendor</a>
</div>
<?php
} // admin non-superadmin
if ($total_item > 0){
?>
<table width=800 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th  <?php echo $row_class ?>>
	<a href="./?mod=item&sub=vendor&act=list&page=<?php echo $_page?>&sort=1">Vendor</a></th>
	<th>Contact 1 No </th>
	<th>Contact 1 Email </th>
	<th>Contact 2 No </th>
	<th>Contact 2 Email </th>
	<th width=50>Action</th>
</tr>

<?php

$counter = $_start;
$rs = get_vendors($sort_order, $_start, $_limit);
while ($rec = mysql_fetch_array($rs))
{
  $edit_link = '<img class="icon" src="images/editx.png" alt="edit"> <img class="icon" src="images/deletex.png" alt="edit">';
  if (!SUPERADMIN && $i_can_update)
    $edit_link =<<<EDIT
    <a href="javascript:void(0)" onclick="inlineedit($rec[id_vendor])" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="?mod=item&sub=vendor&act=del&id=$rec[id_vendor]" 
       onclick="return confirm('Are you sure you want to delete $rec[vendor_name]?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
  <td align="right">$counter.</td>	
  <td id="td$rec[id_vendor]">$rec[vendor_name]</td>
  <td >$rec[contact_no_1]</td>
  <td >$rec[contact_email_1]</td>
  <td >$rec[contact_no_2]</td>
  <td >$rec[contact_email_2]</td>
  <td align="center">$edit_link</td>
  </tr>
DATA;
}

echo '<tr ><td colspan=7 class="pagination">';
echo make_paging($_page, $total_page, './?mod=item&sub=vendor&act=list&page=');
echo  '</td></tr></table>';
} else
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
?>

<br/>
<script type="text/javascript">
var orgval = '';
function inlineedit(id)
{
    location.href = "./?mod=item&sub=vendor&act=edit&id="+id;
    return;
    if (orgval != '') return;
    orgval = $('#td'+id).text();
    //alert(orgval);
    $('#td'+id).html('<input type="text" name="name" value="'+orgval+'" size=30> '+
            '<a href="#" onclick="process_it('+id+', true)" ><img src="images/ok.png" class="icon"></a> '+
            '<a href="#" onclick="process_it('+id+', false)"><img src="images/no.png" class="icon"></a>');
}

function process_it(id, ok)
{
    var dept = $(":input[name^='name']");
    var newval = orgval;
    if (ok){
        newval = dept.val();
        $.post("item/vendor_update.php", {id: ""+id+"", name: ""+newval+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Vendor name updated!');
            } else {
                alert('Update Vendor name fail!');
                newval = orgval;
            }            
        });
    }
    $('#td'+id).text(newval);
    orgval = '';
}

</script>