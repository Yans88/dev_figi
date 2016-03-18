<?php

$id_user = USERID;
$transaction_prefix = TRX_PREFIX_SERVICE;

function count_service_request_by_status_by_user($status, $user){
    $result = 0;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT count(lr.id_loan) 
				FROM loan_request lr 
				LEFT JOIN category c ON lr.id_category=c.id_category 
				WHERE category_type = 'SERVICE' AND requester = '$user' ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_service_request_by_status_by_user($status, $user, $start = 0, $limit = RECORD_PER_PAGE){
	$result = array();
	$dept = defined('USERDEPT') ? USERDEPT : 0;
	$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y %H:%i') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
			 date_format(request_date, '%d-%b-%Y %H:%i') as request_date, 
			 user.full_name as requester, category_name, quantity, remark, status, 
			 approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
			 date_format(return_date, '%d-%b-%Y') as return_date, purpose, 
			 return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark 	 
			 FROM loan_request lr 
			 LEFT JOIN user ON requester = user.id_user 
			 LEFT JOIN category ON lr.id_category = category.id_category 
			 LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
			 WHERE category_type = 'SERVICE' AND requester = '$user'  
             ORDER BY request_date DESC LIMIT $start, $limit ";
	$rs = mysql_query($query);
	$i = 0;
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;    
	return $result;
}

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_status = isset($_GET['status']) ? $_GET['status'] : PENDING;

$_limit = PORTAL_RECORD_PER_PAGE;
$_start = 0;
$total_item = count_service_request_by_status_by_user($_status, $id_user);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

$data = get_service_request_by_status_by_user($_status, $id_user, $_start, $_limit);

?>
<h4><br/>Service Request Record</h4>

<?php
$counter = 0;
if ($total_item > 0) {
    echo <<<TEXT
<table cellpadding=2 cellspacing=1 class="service_table" >
<tr height=30 valign="top" align="center">
  <th width=25>No</th><th width=110>Date of Request</th>
  <th width=110>Service Date</th>
  <th >Category</th><th>Purpose</th>
  <th >Status</th><th width=20>Action</th>
</tr>
TEXT;
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_loan]</td>
    <td align="center">$rec[request_date]</td>
    <td align="center">$rec[start_loan]</td>
    <td>$rec[category_name]</td>
    <td>$rec[purpose]</td>
    <td align="center">$rec[status]</td>
    <td align="center">
    <a href="./?mod=portal&sub=history&portal=service&act=view&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
    </td></tr>
DATA;
        $counter++;
    } 
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=service&act=list&status='.strtolower(PENDING).'&page=');
    echo  '</td></tr></table>';

} else
	echo '<h4 class="error" >Data is not available!</h4>';
?>
&nbsp;
<br/>
