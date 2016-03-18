<?php

require './service/service_util.php';
//echo '<div class="servicebox">';
require './service/service_view.php';
//echo '</div>';
return;

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$transaction_prefix = TRX_PREFIX_SERVICE;
$users = get_user_list();  

$format_date = '%d-%b-%Y %H:%i:%s';
$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
           date_format(request_date, '$format_date') as request_date, category_type, lr.id_category, 
           user.full_name as requester, category_name, quantity, remark, status, without_approval, 
		   approved_by, date_format(approval_date, '$format_date') as approval_date, approval_remark, 
           issued_by, date_format(issue_date, '$format_date') as issue_date, issue_remark, purpose, 
           returned_by, date_format(return_date, '$format_date') as return_date, return_remark, 
           received_by, date_format(receive_date, '$format_date') as receive_date, receive_remark, 
           acknowledged_by, date_format(acknowledge_date, '$format_date') as acknowledge_date, acknowledge_remark 	 
           FROM loan_request lr 
           LEFT JOIN user ON requester = user.id_user 
           LEFT JOIN category ON lr.id_category = category.id_category 
		   LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan 
           WHERE lr.id_loan = $_id 
           ORDER BY request_date DESC ";

$rs = mysql_query($query);
if (mysql_num_rows($rs))
  $rec = mysql_fetch_assoc($rs);
  
$id_page = get_page_id_by_name('service');
$field_list = get_extra_field_list($rec['id_category'], $id_page);
$field_data = get_extra_data_list($rec['id_category'], $id_page);
$extra_data = null;
$no = 0;
foreach ($field_list as $field){
    $class_name = ($no++ % 2 != 0) ? 'alt' : 'normal';
    $extra_data =<<<ROW
<tr class='$class_name'>
    <td>$field[field_name]</td>
    <td>{$field_data[$field['id_field']]}</td>
</tr>
ROW;
}

$quantity = ($rec['category_type'] == 'SERVICE') ? 'none' : $rec['quantity'];

if ($rec['status'] == APPROVED) 
  $caption = 'Request Approved (In-Process)';
elseif ($rec['status'] == REJECTED) {
  $caption = 'Request Rejected (View)';
  $query = "SELECT lr.*, user.full_name rejector_name FROM loan_reject lr
			LEFT JOIN user ON user.id_user = lr.rejected_by 
			WHERE id_loan = $_id";
  $rs = mysql_query($query);
  if ($rs) $reject = mysql_fetch_assoc($rs);
} else
  $caption = 'Request Pending Approval (View)';
  
$status = '&nbsp; &nbsp; [ <span class="status-pending">' . $rec['status'] . '</span> ]';

echo <<<TEXT
<h4><br/>View Service Request</h4>
<table id="itemedit">
<tr><td>
<table width="400" cellpadding=4 cellspacing=1 class="service_table" >
  <tr valign="top" align="left">
    <th align="left" width=130>No</td>
    <th align="left">$transaction_prefix$rec[id_loan] $status</th>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Date/Time of Request</td>
    <td align="left">$rec[request_date]</td>
  <tr valign="top">  
    <td align="left">Requestor</td>
    <td align="left">$rec[requester]</td>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Loan/Service Dates</td>
    <td align="left">$rec[start_loan] - $rec[end_loan]</td>
  </tr>  
  <tr valign="top">  
    <td align="left">Category</td>
    <td align="left">$rec[category_name]</td>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Purpose</td>
    <td align="left">$rec[purpose]</td>    
  </tr>
  <tr valign="top">  
    <td align="left">Remarks</td>
    <td align="left">$rec[remark]</td>    
  </tr>
  $extra_data
</table>
<br/>
TEXT;

if ($rec['without_approval'] == 1){
    if ($rec['status'] == APPROVED) {
        $users = get_user_list();
        $signature = get_signature($_id, 'approve');

        $approved_by = $users[$rec['approved_by']];
        echo <<<TEXTA
  <table width="400" cellpadding=4 cellspacing=1 class="service_table" >
    <tr align="left">
      <th align="left" width=130>Approved by</th>
      <th align="left">$approved_by</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time Approve</td>
      <td  align="left">$rec[approval_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks by Approver</td>
      <td  align="left">$rec[approval_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Approver Signature</td>
      <td align="left">
            <img class='signature' src="$signature">
      </td>
    </tr>
  </table>
  <br/>

TEXTA;

} elseif ($rec['status'] == REJECTED) {
  $signature = $reject['reject_sign'];  
  $rejected_by = $reject['rejector_name'];
  echo <<<TEXTB
  <table width="400" cellpadding=4 cellspacing=1 class="service_table" >
    <tr align="left">
      <th align="left" width=130>Rejected by</th>
      <th align="left">$rejected_by</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time Rejection</td>
      <td  align="left">$reject[reject_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks by Rejector</td>
      <td  align="left">$reject[reject_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Rejector Signature</td>
      <td align="left">
            <img class='signature' src="$signature">
      </td>
    </tr>
  </table>
  <br/>

TEXTB;

    }
} 
if ($rec['status'] == 'COMPLETED'){
    $issue_sign = '<img class="signature"  src="'.get_signature($rec['id_loan'], 'issue').'" width=200 height=80>';
    $completed_by = $users[$rec['issued_by']];
    echo <<<TEXTC
    <table width="400" cellpadding=2 cellspacing=1 class="service_table cellform" >
      <tr valign="top" align="left">
        <th align="left" width=150>Completed by</td>
        <th align="left">$completed_by</th>
      </tr>  
      <tr valign="top">  
        <td align="left">Date of Service Done</td>
        <td align="left">$rec[end_loan]</td>    
      </tr>
      <tr valign="top" class="alt" >  
        <td align="left">Remark</td><td align="left">$rec[issue_remark]</td>    
      </tr>
      <tr valign="top">  
        <td align="left">Signature</td>
        <td align="left">$issue_sign</td>    
      </tr>
    </table>
TEXTC;

}

?>
</td></tr>
<tr><td align="center">
<a href="./?mod=portal&sub=history&portal=service">Back to Request History</a>
</td></tr>
</table>
<br/>&nbsp;
