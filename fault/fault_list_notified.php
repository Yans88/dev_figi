<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_fault_request_status(FAULT_NOTIFIED);
	}

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_fault_request_by_status(FAULT_NOTIFIED);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_fault_request_by_status(FAULT_NOTIFIED, $_start, $_limit);

?>
<h3>Notified Fault Report</h3>
<table cellpadding=2 cellspacing=1 class="fault_table itemlist" >
<tr height=30 valign="top" align="center">
  <th width=25>No</th>
  <th width=120>Date of Report</th>
  <th >Reporter</th>
  <th width=120>Fault Date</th>
  <th >Category</th>
  <th>Description</th>
  <th width=50>Action</th>
</tr>

<?php
$counter = 0;
if ($total_item > 0) {
    foreach ($data as $rec) {
        $desc = substr($rec['fault_description'], 0, 35) . ' ...';
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_fault]</td>
    <td align="center">$rec[report_date]</td>
    <td>$rec[full_name]</td>
    <td align="center">$rec[fault_date]</td>
    <td>$rec[category_name]</td>
    <td>$desc</td>
    <td align="center">
    <a href="./?mod=fault&sub=fault&act=view&id=$rec[id_fault]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
    if ($i_can_update) {
        echo ' <a href="./?mod=fault&sub=fault&act=rectify&id='.$rec['id_fault'].'" title="manage"><img class="icon" src="images/process.png" alt="manage"></a> ';            
    }
    echo '</td></tr>';
  $counter++;
    } 
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=fault&sub=fault&act=list&status='.strtolower(FAULT_NOTIFIED).'&page=');
    echo  '<div class="exportdiv"><a href="./?mod=fault&act=list&status=notified&do=export" class="button">Export Data</a></div></td></tr>';

}else
	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';
?>
</table>
