<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_request_status(REJECTED);
}


$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_request_by_status(REJECTED);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_request_by_status(REJECTED, $_start, $_limit, 'request_date', 'DESC');

$counter = 0;
if ($total_item > 0) {
?>
<table class="itemlist grid loan" >
<tr >
  <th width=35>No</th><th>Date of Request</th>
  <th>Requestor</th><th>Loan Start Date</th>
  <th>Loan End Date</th><th>Category</th>
  <th width=25>Quantity</th><th>Remarks</th><th width=50>Action</th>
</tr>

<?php
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_loan]</td>
    <td width=120 align="center">$rec[request_date]</td>
    <td>$rec[requester]</td>
    <td width=120 align="center">$rec[start_loan]</td>
    <td width=120 align="center">$rec[end_loan]</td>
    <td>$rec[category_name]</td>
    <td align="center">$rec[quantity]</td>
    <td>$rec[remark]</td>
    <td align="center">
    <a href="./?mod=loan&sub=loan&act=view&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        $need_approval = $need_approval = ($rec['without_approval'] == 0);
        if (((USERGROUP == GRPHOD) || $i_can_delete) || ($i_can_update && !$need_approval)){
            echo ' <a href="./?mod=loan&sub=loan&act=approve&id='.$rec['id_loan'].'" title="approve"><img class="icon" src="images/ok.png" alt="approve"></a>';
                /*<br/>
                    <a href="./?mod=loan&sub=loan&act=unapprove&id='.$rec['id_loan'].'" 
                    onclick="return confirm(\'Are you sure you NOT approve this request?\')">not approve</a>';
                    */
        }
    echo '</td></tr>';
  $counter++;
    } 
    echo '<tr ><td colspan=9>';
	echo '<div class="pagination">';
	echo make_paging($_page, $total_page, './?mod=loan&sub=loan&act=list&status=unapproved&page=');
	echo '</div>';
    echo  '<div class="exportdiv"><a href="./?mod=loan&sub=loan&act=list&status=unapproved&do=export" class="button">Export Data</a></div>';
	echo '</td></tr></table>';
}else{

	echo '<p align="Center" ><h3>Rejected Loan Requests</h3><br><p class="error">Data is not available!</p></p>';
}
?>


<br>&nbsp;<br><br>&nbsp;<br>
