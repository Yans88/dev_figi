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
	
	export_request_status(PENDING);
}

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_request_by_status(PENDING);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_request_by_status(PENDING, $_start, $_limit);

$caption = (REQUIRE_LOAN_APPROVAL) ? 'Request Pending Approval' : 'Pending Loan Request'; 
$counter = 0;
if ($total_item > 0) {

?>
<table class="itemlist grid loan" width="100%">
<thead>
<tr>
  <th width=35>No</th>
  <th width=120>Date of Request</th>
  <th>Requestor</th>
  <th width=120>Loan Start Date</th>
  <th width=120>Loan End Date</th>
  <th>Category</th>
  <th width=25>Quantity</th>
  <th>Purpose</th>
  <th width=60>Action</th>
</tr>
</thead>
<?php
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_loan]</td>
    <td align="center">$rec[request_date]</td>
    <td>$rec[requester]</td>
    <td align="center">$rec[start_loan]</td>
    <td align="center">$rec[end_loan]</td>
    <td>$rec[category_name]</td>
    <td align="center">$rec[quantity]</td>
    <td>$rec[purpose]</td>
    <td align="center">
    <a href="./?mod=loan&sub=loan&act=view&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        $need_approval = REQUIRE_LOAN_APPROVAL;
        if ($need_approval && $i_can_delete){
            echo ' <a href="./?mod=loan&sub=loan&act=approve&id='.$rec['id_loan'].'" title="approve"><img class="icon" src="images/ok.png" alt="approve"></a>
                    <a href="./?mod=loan&sub=loan&act=unapprove&id='.$rec['id_loan'].'" )" title="not approve"><img class="icon" src="images/no.png" alt="not approve"></a>';
        } else if ($i_can_update) {
            if ((!$need_approval && ($rec['status'] == PENDING)) || ($need_approval && ($rec['status'] == APPROVED))){
            
                if (!$need_approval)
                    echo '<a href="./?mod=loan&sub=loan&act=unapprove&id='.$rec['id_loan'].'" title="not approve"><img class="icon" src="images/no.png" alt="not approve"></a>';
                echo ' <a href="./?mod=loan&sub=loan&act=issue&id='.$rec['id_loan'].'" title="manage"><img class="icon" src="images/process.png" alt="manage"></a> ';            
            }
        }
    echo '</td></tr>';
  $counter++;
    } 
    echo '<tr ><td colspan=9 >';
	echo '<div class="pagination">';
    echo make_paging($_page, $total_page, './?mod=loan&sub=loan&act=list&status='.strtolower(PENDING).'&page=');
	echo '</div>';
    echo  '<div class="exportdiv"><a href="./?mod=loan&sub=loan&act=list&status=pending&do=export" class="button">Export Data</a></div>';
	echo '</td></tr></table>';

}else{

	echo '<p align="Center"><h3 >'.$caption.'</h3><p class="error" >Data is not available!</p></p>';
}
?>
<br>&nbsp;<br>
<br>&nbsp;<br>

