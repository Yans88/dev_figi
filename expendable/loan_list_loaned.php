<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;
//$_filters = isset($_GET['filter_term']) ? $_GET['filter_term'] : 'long,short';

if ($_do == 'export'){
	
	export_request_status(LOANED);
}

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_request_by_status(LOANED);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_request_by_status(LOANED, $_start, $_limit);
// echo print_r($data);

?>
<br/>
<h3 style="text-align: center">Request Already Loaned</h3>
<table cellpadding=2 cellspacing=1 class="loan_table item-list" >
<tr height=30 valign="top">
  <th width=35>No</th><th>Date of Request</th>
  <th>Requestor</th><th>Loan Start Date</th>
  <th>Loan End Date</th>
  <th>Item Loan List </th><th>Category List Name</th>
  <th width=25>Quantity</th><th>Purpose</th><th width=60>Action</th>
</tr>

<?php
$counter = 0;
if ($total_item > 0) {
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        $addition = get_item_list_by_id($rec['id_loan']);
        
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_loan]</td>
    <td align="center">$rec[request_date]</td>
    <td>$rec[requester]</td>
    <td align="center">$rec[start_loan]</td>
    <td align="center">$rec[end_loan]</td>
    <td>$addition[item_list_name]</td>
    <td>$addition[category_list_name]</td>
    <td align="center">$rec[quantity]</td>
    <td>$rec[purpose]</td>
   
    <td align="center">
    <a href="./?mod=expendable&sub=loan&act=view&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        
    echo '</td></tr>';
  $counter++;
    } 
    echo '<tr ><td colspan=10 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=expendable&sub=loan&act=list&status='.strtolower(PENDING).'&page=');

    echo  '<div class="exportdiv"><a href="./?mod=expendable&sub=loan&act=list&status=pending&do=export" class="button">Export Data</a></div></td></tr>';

}else
	echo '<tr><td colspan=10 align="Center" >Data is not available!</td></tr>';
?>
</table>