<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_request_status(APPROVED);
	}


$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_request_by_status(APPROVED);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_request_by_status(APPROVED, $_start, $_limit);
$counter = 0;
if ($total_item > 0){
?>
<h3>Approved Request (In-Process)</h3>
<table cellpadding=2 cellspacing=1 class="loan_table item-list" >
<tr height=30 valign="top">
  <th width=35>No</th>
  <th width=120>Date of Request</th>
  <th>Requestor</th>
  <th width=120>Loan Start Date</th>
  <th width=120>Loan End Date</th>
  <th>Category</th>
  <th width=25>Quantity</th>
  <th>Remarks HOD</th>
  <th width=40>Action</th>
</tr>

<?php
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
    <tr $_class>
    <td align="center">$transaction_prefix$rec[id_loan]</td>
    <td align="center">$rec[request_date]</td>
    <td>$rec[requester]</td>
    <td align="center">$rec[start_loan]</td>
    <td align="center">$rec[end_loan]</td>
    <td>$rec[category_name]</td>
    <td align="center">$rec[quantity]</td>
    <td>$rec[approval_remark]</td>
    <td align="center">
    <a href="./?mod=loan&sub=loan&act=view&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        if ($i_can_update) {
            echo ' <a href="./?mod=loan&sub=loan&act=issue&id='.$rec['id_loan'].'" title="manage"><img class="icon" src="images/process.png" alt="manage"></a> ';            
        }
        echo '</td></tr>';
    
        $counter++;
    } 
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=loan&sub=loan&act=list&status='.strtolower(APPROVED).'&page=');
    echo  '<div class="exportdiv"><a href="./?mod=loan&sub=loan&act=list&status=approved&do=export" class="button">Export Data</a></div></td></tr>';
	echo '</table>';

}else
	echo '<p align="Center" ><h3>Approved Request (In-Process)</h3><br><p class="error">Data is not available!</p></p>';

?>

