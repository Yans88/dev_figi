<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_issuance(USERDEPT);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_issuance(USERDEPT, $_start, $_limit);

?>
<h4>Departmental Item Issuance History</h4>
<div style="width: 800px; text-align: left; ">
<a class="button" href="./?mod=item&act=issue">Create Item Issuance</a>
<a class="button" href="./?mod=item&act=issue_history">Issuance History</a>
<!--a class="button" href="./?mod=item&act=issue_return">Return Item</a-->
</div>

<div id="item_issuance" style="width: 800px">
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=20 valign="top">
  <th width=25 rowspan=2>No</th>
  <th width=80 rowspan=2>Date of Issue</th>
  <th width=140 rowspan=2>Category</th>
  <th width=280 colspan=2>Issued-Out To</th>
  <th width=70 rowspan=2>Status</th>
  <th width=50 rowspan=2>Action</th>
</tr>
<tr height=20 valign="top">
  <th >Department</th><th>Category</th>
</tr>

<?php
$counter = 0;
if ($total_item > 0) {  
    $categoryies = get_category_list('EQUIPMENT', USERDEPT);
    foreach ($data as $rec) {
        $src_category = $categoryies[$rec['src_category']];
        $_class = ($counter++ % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
	<tr $_class>
	<td align="right">$counter</td>
	<td align="left">$rec[issue_date]</td>
	<td align="left">$src_category</td>
	<td align="left">$rec[department_name]</td>
	<td align="left">$rec[category_name]</td>
	<td align="left">$rec[status]</td>
	<td align="center">
    <a href="./?mod=item&act=view_issue&id=$rec[id_issue]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        if ($i_can_update && $rec['status'] != 'RETURNED') {
            echo ' <a href="./?mod=item&act=issue_return&id='.$rec['id_issue'].'" title="return" ><img class="icon" src="images/undo.png" alt="return"></a> ';
        }
        echo '</td></tr>';
        $counter++;
    }
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=item&sub=item&act=issue_history&status='.strtolower(LOANED).'&page=');
    //echo  '<div class="exportdiv"><a href="./?mod=item&sub=item&act=list&status=itemed&do=export" class="button">Export Data</a></div>';
    echo '</td></tr>';
    
} else
	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';

?>
</table>
</div>