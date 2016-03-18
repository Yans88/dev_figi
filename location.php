<?php
if (!defined('FIGIPASS')) exit;

$i_can_create = (USERGROUP == GRPADM);
$i_can_update = (USERGROUP == GRPADM);

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$_act = isset($_GET['act']) ? $_GET['act'] : 'list';

if ($_act == 'edit'){
}

if (!empty($_SESSION['LOCATION_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['LOCATION_ORDER_STATUS']);
else
    $order_status = array('location_name' => 'asc');
$order_by = 'location_name';
$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = count_location();
$total_page = ceil($total_item/$_limit);


$_start = ($_page > 0) ? ($_page-1) * $_limit : 0;
$_page = ($_page > $total_page)  ? $total_page : $_page;
$sort_order = $order_status[$order_by];
if ($_sort > 0) {
	$sort_order = ($order_status[$order_by] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$order_by] = $sort_order;
	$_SESSION['LOCATION_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';

if ($i_can_create && !SUPERADMIN) {
?>
<div style="width: 800px">
<h3>Location Management</h3>
<br/>
<?php
if ($total_item > 0){
?>
<div style="text-align: left; width:400px">
<a class="button" href="javascript:add_location()">
	<img width=16 height=16 border=0 src="images/add.png"> Add New Location</a>
</div>
<?php
    }
} // admin non-superadmin
if ($total_item > 0){
?>
<table width=400 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
	<th width=30>No</th>
	<th  <?php echo $row_class ?>>
		<a href="./?sub=location&act=list&page=<?php echo $_page?>&sort=1">Location</a>
	</th>
	<th width=50>Action</th>
</tr>

<?php
$counter = $_start;
$rs = get_locations($sort_order, $_start, $_limit);
while ($rec = mysql_fetch_array($rs))
{
  $edit_link = '<img class="icon" src="images/editx.png" alt="edit"> <img class="icon" src="images/deletex.png" alt="edit">';
  if (!SUPERADMIN && $i_can_update)
    $edit_link =<<<EDIT
    <a href="javascript:void(0)" onclick="inlineedit($rec[id_location])" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="javascript:del_location($rec[id_location])" 
       onclick="return confirm('Are you sure delete $rec[location_name]?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
  <td align="right">$counter.</td>	
  <td id="td$rec[id_location]">$rec[location_name]</td>
  <td align="center">$edit_link</td>
  </tr>
DATA;
}

echo '<tr ><td colspan=3 class="pagination">';
echo make_paging($_page, $total_page, './?sub=location&act=list&page=');
echo  '</td></tr></table>';
} else
    echo '<p class="error" style="margin-top: 10px">Data is not available!. <a href="javascript:add_location()" >Click here</a> to add new location.
</p>';

?>
<br/>
<script>
var orgval = '';

function add_location()
{
    var loc = prompt('Enter location name: ');
    if (loc.length > 0){
        $.post("location_update.php", {name: ""+loc+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('New Location name added!');
                location.reload();
            } else {
                alert('Add new  Location  is fail!');
            }            
        });
    }
}

function del_location(id)
{
    //var del = confirm('Are you sure delete the location? ');
    if (id){
        $.post("location_update.php", {act: "delete",id: ""+id+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Selected location has been deleted!');
                location.reload();
            } else {
                alert('Delete  Location  is fail!');
            }            
        });
    }
}

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
        $.post("location_update.php", {id: ""+id+"", name: ""+newval+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Location name updated!');
            } else {
                alert('Update Location name fail!');
                newval = orgval;
            }            
        });
    }
    $('#td'+id).text(newval);
    orgval = '';
}

</script>
</div>