<?php
if (!defined('FIGIPASS')) exit;
if (!empty($_SESSION['MANUFACTURER_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['MANUFACTURER_ORDER_STATUS']);
else
    $order_status = array('manufacturer_name' => 'asc');
$order_by = 'manufacturer_name';
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$total_item = count_manufacturer();
$total_page = ceil($total_item/$_limit);


$_start = ($_page > 0) ? ($_page-1) * $_limit : 0;
$_page = ($_page > $total_page)  ? $total_page : $_page;
$sort_order = $order_status[$order_by];
if ($_sort > 0) {
	$sort_order = ($order_status[$order_by] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$order_by] = $sort_order;
	$_SESSION['MANUFACTURER_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';

if ($i_can_create && !SUPERADMIN) {
?>
<div style="text-align: left; width:400px">
<a class="button" href="./?mod=item&sub=manufacturer&act=edit">  Add New Manufacturer</a>
<a class="button" href="./?mod=item&sub=manufacturer&act=import"> Import Manufacturer</a>
</div>
<?php
} // admin non-superadmin
if ($total_item > 0){
?>
<table width=400 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
	<th width=30>No</th>
	<th  <?php echo $row_class ?>>
		<a href="./?mod=item&sub=manufacturer&act=list&page=<?php echo $_page?>&sort=1">Manufacturer</a>
	</th>
	<th width=50>Action</th>
</tr>

<?php
$counter = $_start;
$rs = get_manufacturers($sort_order, $_start, $_limit);
while ($rec = mysql_fetch_array($rs))
{
  $edit_link = '<img class="icon" src="images/editx.png" alt="edit"> <img class="icon" src="images/deletex.png" alt="edit">';
  if (!SUPERADMIN && $i_can_update)
    $edit_link =<<<EDIT
    <a href="javascript:void(0)" onclick="inlineedit($rec[id_manufacturer])" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="?mod=item&sub=manufacturer&act=del&id=$rec[id_manufacturer]" 
       onclick="return confirm('Are you sure you want to delete $rec[manufacturer_name]?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
  <td align="right">$counter.</td>	
  <td id="td$rec[id_manufacturer]">$rec[manufacturer_name]</td>
  <td align="center">$edit_link</td>
  </tr>
DATA;
}

echo '<tr ><td colspan=3 class="pagination">';
echo make_paging($_page, $total_page, './?mod=item&sub=manufacturer&act=list&page=');
echo  '</td></tr></table>';
} else
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';

?>
<br/>
<script>
var orgval = '';
function inlineedit(id)
{
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
        $.post("item/manufacturer_update.php", {id: ""+id+"", name: ""+newval+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Manufacturer name updated!');
            } else {
                alert('Update Manufacturer name fail!');
                newval = orgval;
            }            
        });
    }
    $('#td'+id).text(newval);
    orgval = '';
}

</script>