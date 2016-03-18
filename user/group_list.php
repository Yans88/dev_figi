<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_username = null;

$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = get_group_count();
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) 
    $_page = 1;
if ($_page > 0)
	$_start = ($_page-1) * $_limit;

$data = get_group_data($_start, $_limit);

?>	
<br/>
<?php
    if ($i_can_create) {
?>
<div style="text-align: left;width:400px;vertical-align:middle;">
<a href="./?mod=user&sub=group&act=edit" class="button">
	<img width=16 height=16 border=0 src="images/add.png"> Create New Group</a>
</div>
<?php
} // create 
?>
<table width="400" class="userlist" cellpadding=2 cellspacing=1>
<tr>
  <th> Group Name</th>
	<th width=100> Privileges</th>
	<th width=130> Action </th>
</tr>
<?php
if ($total_item > 0){	
    $counter = 0;
    foreach ($data as $group) {
        $group_id = $group['id_group'];
        $group_name = $group['group_name'];
        $admin_links = '';
        if (SUPERADMIN && $group_id != GRPADM){
            $admin_links = <<<LINK
<a href='?mod=user&sub=group&act=edit&id=$group_id' title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
<a href='?mod=user&sub=group&act=del&id=$group_id' 
									onclick="return confirm('Are you sure you want to delete group $group_name?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
LINK;
        }
        $class = ($counter % 2 == 0) ? 'class="alt"' : 'class="normal"';

echo <<<DATA
<tr $class>
	<td>$group_name</td>			
	<td align="center"><a href='?mod=user&sub=group&act=view&id=$group_id' title="view"><img class="icon" src="images/loupe.png" alt="view" ></a></td>
	<td align="center">
        <a href="?mod=user&sub=group&act=view&id=$group_id" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
        $admin_links
	</td>
</tr>
DATA;
    $counter++;
    } // end of while
    echo '<tr ><td colspan=4 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=user&sub=group&act=list&page=');
    echo  '</td></tr>';
} else {
    echo '<tr ><td colspan=4>Data is not available!</td></tr>';
}
echo '</table>'; // close table	
?>
