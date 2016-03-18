<?php
if (!defined('FIGIPASS')) exit;
$dept = USERDEPT;
$type = 'expendable';

if (!empty($_SESSION['CATEGORY_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['CATEGORY_ORDER_STATUS']);
else
    $order_status = array('equipment' => 'asc', 'service' => 'asc', 'expendable' => 'asc');
$order_by = 'category_name';

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$total_item = count_category($type, USERDEPT);
$total_page = ceil($total_item/$_limit);

if ($_page > 0)
	$_start = ($_page-1) * $_limit;
if ($_page > $total_page)
	$_page = $total_page;
$sort_order = $order_status[$type];
if ($_sort > 0) {
	$sort_order = ($order_status[$type] == 'asc') ? 'desc' : 'asc';
	$buffer = ob_get_contents();
	ob_clean();
	$order_status[$type] = $sort_order;
	$_SESSION['CATEGORY_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';

if ($i_can_create && !SUPERADMIN) {
?>
<div style="text-align: left; width:400px">
<a class="button" href="javascript:add_new()">
	<img width=16 height=16 border=0 src="images/add.png"> Add New Category</a>
</div>
<?php
}
?>
<table width=400 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th <?php echo $row_class ?>>
	<a href="./?mod=expendable&sub=category&type=<?php echo $type?>&page=<?php echo $_page?>&sort=1">Category</a></th>
  <th width=100>Action</th>
</tr>

<?php
$counter = $_start;
$rs = get_categories($type, $sort_order, $_start, $_limit, USERDEPT);
while ($rec = mysql_fetch_array($rs))
{
  $edit_link = '<img class="icon" src="images/editx.png" alt="edit"> <img class="icon" src="images/deletex.png" alt="edit">';
  if (!SUPERADMIN && $i_can_update)
    $edit_link =<<<EDIT
    <a href="javascript:inlineedit($rec[id_category])" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="?mod=expendable&sub=category&act=del&id=$rec[id_category]" 
       onclick="return confirm('Are you sure you want to delete $rec[category_name]?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  echo <<<DATA
  <tr $_class>
    <td align="right">$counter.</td>
    <td id="td$rec[id_category]">$rec[category_name]</td>
    <td align="center">$edit_link</td>
  </tr>
DATA;
}

echo '<tr ><td colspan=4 class="pagination">';
echo make_paging($_page, $total_page, './?mod=expendable&sub=category&act=list&type='.$type.'&page=');
echo  '</td></tr>';

?>

</table><br/>
<script type="text/javascript">
var id_dept= '<?php echo $dept?>';
var cat_type = '<?php echo $type?>';
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
        $.post("item/category_update.php", {type: ""+cat_type+"", dept: ""+id_dept+"", id: ""+id+"", name: ""+newval+""}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Category name updated!');
            } else {
                alert('Update Category name fail!');
                newval = orgval;
            }            
        });
    }
    $('#td'+id).text(newval);
    orgval = '';
}

function add_new()
{
    var dept = prompt("Input New Category Name: ");
    if (dept != ''){
        $.post("item/category_update.php", {type: ""+cat_type+"", dept: ""+id_dept+"", id: 0, name: ""+dept+""}, function(data){
        		//alert(data)
            if (data.length>0 && parseInt(data) > 0){
                //alert('Vendor name added!');
                location.href = "./?mod=expendable&sub=category";
            } else {
                alert('Add new category fail!');
            }            
        });
    }
    //return false;
}

</script>