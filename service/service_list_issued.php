<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_service_request_status('ISSUED');
}

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_service_request_by_status('ISSUED');
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_service_request_by_status('ISSUED', $_start, $_limit);

?>


<br/>
<h3>Service Requests in-Progress</h3>
<table cellpadding=2 cellspacing=1 class="service_table itemlist" >
<tr height=30 valign="top" align="center">
  <th width=25>No</th>
  <th width=120>Date of Request</th>
  <th width=80>Requestor</th>
  <th width=120>Service Date</th>
  <th width=140>Category</th>
  <th>Purpose</th>
  <th>Issuance Remarks</th>
  <th width=50>Action</th>
</tr>

<?php
$counter = 0;
if ($total_item > 0) {
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
	<tr $_class>
	<td align="center">$transaction_prefix$rec[id_loan]</td>
	<td align="center">$rec[request_date]</td>
	<td>$rec[requester]</td>
	<td align="center">$rec[start_loan]</td>
	<td>$rec[category_name]</td>
	<td>$rec[purpose]</td>
	<td>$rec[issue_remark]</td>
	<td align="center">
    <a href="./?mod=service&sub=service&act=view_issue&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
	</td></tr>
DATA;
        $counter++;
    }
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=service&sub=service&act=list&status='.strtolower(ISSUED).'&page=');
    echo  '<div class="exportdiv"><a href="./?mod=service&act=list&status=completed&do=export" class="button">Export Data</a></div></td></tr>';
    
} else
	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';

?>
</table>

</table>
