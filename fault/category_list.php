<?php
if (!defined('FIGIPASS')) exit;
if (!empty($_SESSION['FAULT_CATEGORY_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['FAULT_CATEGORY_ORDER_STATUS']);
else
    $order_status = array('category_name' => 'asc');
$order_by = 'category_name';

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$total_item = count_fault_category(0);
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
	$_SESSION['FAULT_CATEGORY_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';
$i_can_create = ((USERDEPT>0) && (USERGROUP==GRPADM));
$i_can_update = $i_can_create;
$view_only = !$i_can_create ;

if ($i_can_create) {
?>
<div style="text-align: left; width:440px" class="middle">
<a class="button" href="javascript:void(0)" onclick="add_category()">Add New Category</a>
</div>
<?php
}
?>
<table width=440 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th <?php echo $row_class ?> >
	<a href="./?mod=fault&sub=category&page=<?php echo $_page?>&sort=1">Category</a></th>
 <?php
    if (!$view_only)
    echo ' <th width=50>Action</th>';
  ?>
</tr>

<?php
$counter = $_start;
$rs = get_fault_categories($sort_order, $_start, $_limit, 0);
while ($rec = mysql_fetch_array($rs))
{
    $edit_link = '';
  if ($i_can_update)
    $edit_link .= '<a href="javascript:void(0)" onclick="inlineedit('.$rec['id_category'].')" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>';
  if ($i_can_delete)
    $edit_link .= '<a href="javascript:void(0)" onclick="del_category('.$rec['id_category'].')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>';
  $counter++;
  $_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
  echo '<tr '.$_class.'> <td id="td'.$rec['id_category'].'">'.$rec['category_name'].'</td>';
  if (!$view_only)
    echo  '<td align="center">'.$edit_link.'</td>';
  echo ' </tr>';
}

echo '<tr ><td colspan=7 class="pagination">';
echo make_paging($_page, $total_page, './?mod=fault&sub=category&act=list&&page=');
echo  '</td></tr>';

?>

</table><br/>
<script>
function add_category()
{
  var val = prompt('Enter new category name: ');
  if ((val != null) && (val != '') && (val != 'null'))
    $.post("fault/category_update.php", {id: "0", name: ""+val+""}, function(data){
     // alert(data)
        if (data.length>0 && parseInt(data) > 0){
            alert('New Category added!');
            location.reload();
        } else {
            alert('Fail to add new category!');
        }            
    });
  return false;
}

function del_category(id)
{
  var ok = confirm('Are you sure delete the category?');
  if (ok)
    $.post("fault/category_update.php", {id: ""+id+"", del: "1"}, function(data){
        if (data.length>0 && parseInt(data) > 0){
     // alert(data)
            alert('Selected Category deleted!');
            location.reload();
        } else {
            alert('Fail to delete category!');
        }            
    });
  return false;
}

var orgval = '';
function inlineedit(id)
{
    if (orgval != '') return;
    orgval = $('#td'+id).text();
    $('#td'+id).html('<input type="text" name="name" value="'+orgval+'" size=30> '+
            '<a href="#" onclick="process_it('+id+', true)" title="save"><img src="images/ok.png" class="icon"></a> '+
            '<a href="#" onclick="process_it('+id+', false)" title="cancel"><img src="images/no.png" class="icon"></a>');
}

function process_it(id, ok)
{
    var dept = $(":input[name^='name']");
    var newval = orgval;
    if (ok){
        newval = dept.val();
        $.post("fault/category_update.php", {id: ""+id+"", name: ""+newval+""}, function(data){
        
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

</script>
