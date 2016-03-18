<?php
if (!defined('FIGIPASS')) exit;
if (!empty($_SESSION['DEPARTMENT_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['DEPARTMENT_ORDER_STATUS']);
else
    $order_status = array('department_name' => 'asc');
$order_by = 'department_name';
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;
$total_item = count_department();
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
	$_SESSION['DEPARTMENT_ORDER_STATUS'] = serialize($order_status);
	echo $buffer;
}
$row_class = ' class="sort_'.$sort_order.'"';
	
if ($i_can_create && SUPERADMIN) {
?>
<div style="text-align: left; width:200px">
<a class="button" href="./?mod=user&sub=department&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Department</a>	
</div>

<?php
} // admin non-superadmin
if ($total_item > 0){
	$sort_link = './?mod=user&sub=department&act=list&page='.$_page .'&sort=1&ordby=';
?>
<table width=300 cellpadding=2 cellspacing=1 class="userlist" >
<tr height=30>
  <th width=240 <?php echo $row_class ?>>
	<a href="<?php echo $sort_link?>department">Department</a>
  </th>
  <th width=60>Action</th>
</tr>

<?php

$counter = $_start;
$users = get_user_list(false, true);
$rs = get_departments($sort_order, $_start, $_limit);
while ($rs && ($rec = mysql_fetch_array($rs)))
{
  $counter++;
  $admin_name = '--';
  $hod_name = '--';
  
  $user_link = './?mod=user&act=view&id=';
  if (!empty($users[$rec['id_admin']]))
    $admin_name = '<a href="'. $user_link . $rec['id_admin'] .'">'.$users[$rec['id_admin']].'<a/>';
  if (!empty($users[$rec['id_hod']]))
    $hod_name = '<a href="'. $user_link . $rec['id_hod'] .'">'.$users[$rec['id_hod']].'<a/>';
    
  $_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
  echo <<<DATA
  <tr $_class>
  <td id="td$rec[id_department]">$rec[department_name]</td>
  <td align="center">
    <a href="javascript:void(0)" onclick="inlineedit($rec[id_department])" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
    <a href="javascript:void(0)" onclick="delete_it($rec[id_department])" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
  </td>
  </tr>
DATA;
}

echo '<tr ><td colspan=7 class="pagination">';
echo make_paging($_page, $total_page, './?mod=user&sub=department&act=list&page=');
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
    $('#td'+id).html('<input type="text" name="deptname" value="'+orgval+'"> '+
            '<a href="#" onclick="process_it('+id+', true)" ><img src="images/ok.png" width=16 height=16></a> '+
            '<a href="#" onclick="process_it('+id+', false)"><img src="images/no.png" width=16 height=16></a>');
}

function process_it(id, ok)
{
    var dept = $(":input[name^='deptname']");
    var newval = orgval;
    if (ok){
        newval = dept.val();
        if (newval.length>0){
            $.post("user/department_update.php", {id: ""+id+"", name: ""+newval+""}, function(data){
                if (data.length>0 && parseInt(data) > 0){
                    alert('Department name updated!');
                } else {
                    alert('Update Department name fail!');
                    newval = orgval;
                }            
            });
            $('#td'+id).text(newval);
        } else {
            alert('Department name can not be empty!');
            $('#td'+id).text(orgval);
        }
    }
    
    orgval = '';
}

function delete_it(id)
{
    var deptname = $('#td'+id).text();
    var ok = confirm('Are you sure delete department "'+deptname+'"?');
    if (ok){
        $.post("user/department_update.php", {id: ""+id+"", name: ""+deptname+"", del: "me"}, function(data){
            if (data.length>0 && parseInt(data) > 0){
                alert('Department has been deleted!');
                location.reload();
            } else {
                alert('Delete Department failed!');
            }            
        });
    }
    return false;
}
//return confirm('Are you sure you want to delete $rec[department_name]?')
</script>
