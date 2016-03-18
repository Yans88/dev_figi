<?php

require_once('./loan/loan_view_complete.php');

return;
if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y H:i:s');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$request = get_request($_id);
$need_approval = ($request['without_approval'] == 0);
$request_items = get_request_items($_id);
$no = 1;
foreach ($request_items as $item)
  $items[] = ($no++) . ". $item[asset_no] ($item[serial_no])";
$item_list = implode("<br/>\r\n", $items);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);  
$users = get_user_list();  
  

$acknowledged_by = !empty($process['acknowledged_by']) ? $users[$process['acknowledged_by']] : null;
$approved_by = !empty($process['approved_by']) ? $users[$process['approved_by']] : null;
$issued_by = !empty($process['issued_by']) ? $users[$process['issued_by']] : null;

$approve_sign = '<img src="'.get_signature($_id, 'approve').'" class="signature">';
$issue_sign = '<img src="'.get_signature($_id, 'issue').'" class="signature">';
$loan_sign = '<img src="'.get_signature($_id, 'loan').'" class="signature">';
$return_sign = '<img src="'.get_signature($_id, 'return').'" class="signature">';
$receive_sign = '<img src="'.get_signature($_id, 'receive').'" class="signature">';
$acknowledge_sign = '<img src="'.get_signature($_id, 'acknowledge').'" class="signature">';

if ($need_approval)
    $caption = 'Acknowleged Returned Items';
else
    $caption = 'Returned Items';

$transaction_prefix = TRX_PREFIX_LOAN;
$accessories = '<ol style="margin:0;padding-left:15px;padding-top:0 ">';
$accessories_list = get_accessories_by_loan($_id);
foreach($accessories_list as $idacc => $acc)
    $accessories .= '<li>'.$acc . '</li>';
$accessories .= '</ol>';


?>
<h4><br/><?php echo $caption?></h4>
<table  class="loanview complete" border=0 cellpadding=2 cellspacing=1>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=3 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" >No</td>
        <th align="left" width=200><?php echo $transaction_prefix.$request['id_loan']?></th>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date/Time of Request</td>
        <td align="left"><?php echo $request['request_date']?></td>
      <tr valign="top">  
        <td align="left">Requestor</td>
        <td align="left"><?php echo $request['requester']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Loan/Service Dates</td>
        <td align="left"><?php echo $request['start_loan']?> - <?php echo $request['end_loan']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Category</td>
        <td align="left"><?php echo $request['category_name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Quantity</td>
        <td align="left"><?php echo $request['quantity']?></td>    
      </tr>  
      <tr valign="top">  
        <td align="left">Requestor Remarks</td>
        <td align="left"><?php echo $request['remark']?></td>    
      </tr>
    </table>
    </td>
    <td>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
      <tr valign="top" align="left">
        <th align="left" width=130>Item Serial No.</td>
        <th align="left"><div id="seriallist"><?php echo $item_list?></div></th>
      </tr>  
      <tr valign="top">  
        <td align="left">Category</td>
        <td align="left"><?php echo $request['category_name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Basic Accessories Included</td>
        <td align="left"><?php echo $accessories?></td>
      <tr valign="top">  
        <td align="left">Loan Out to</td>
        <td align="left"><?php echo $issue['name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC</td>
        <td align="left"><?php echo $issue['nric']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><?php echo $issue['contact_no']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Location</td>
        <td align="left"><?php echo $issue['location_name']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Department</td>
        <td align="left"><?php echo $issue['department_name']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date (Sign Out)</td>
        <td align="left"><?php echo $issue['loan_date']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Date (To be Returned)</td>
        <td align="left"><?php echo $issue['return_date']?></td>    
      </tr>  
    </table>

    </td>
</tr>
<tr>
  <td colspan=2>
    <table class="process" width="100%">
<?php

if ($need_approval){
?>
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200 align="left">Approved By</th>
    <th width=200 align="left">Issued By</th>
    <th width=200 align="left">Loaned By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $approved_by?></td>
    <td><?php echo $issued_by?></td>
    <td><?php echo $issue['name']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $process['approval_date']?></td>
    <td><?php echo $process['issue_date']?></td>
    <td><?php echo $process['loan_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><?php echo $process['approval_remark']?></td>
    <td><?php echo $process['issue_remark']?></td>
    <td><?php echo $process['loan_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td><?php echo $approve_sign?></td>
    <td><?php echo $issue_sign?></td>
    <td><?php echo $loan_sign?></td>
</tr>
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200 align="left">Returned By</th>
    <th width=200 align="left">Received By</th>
    <th width=200 align="left">Acknowledged By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $returns['returned_by']?></td>
    <td><?php echo $returns['received_by']?></td>
    <td><?php echo $acknowledged_by?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
    <td><?php echo $process['acknowledge_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
    <td><?php echo $process['acknowledge_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td><?php echo $return_sign?></td>
    <td><?php echo $receive_sign?></td>
    <td><?php echo $acknowledge_sign?></td>
</tr>

<?php
} 
else {
?>
<tr valign="top">
    <th >&nbsp;</th>
    <th width=200 align="center"></th>
    <th width=200 align="center">Issued By</th>
    <th width=200 align="center">Loaned By</th>
</tr>
<tr valign="top">
    <td>&nbsp;</td>
    <td>Name</td>
    <td><?php echo $issued_by?></td>
    <td><?php echo $issue['name']?></td>
</tr>
<tr valign="top" class="alt">
    <td>&nbsp;</td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['issue_date']?></td>
    <td><?php echo $process['loan_date']?></td>
</tr>
<tr valign="top">
    <td>&nbsp;</td>
    <td>Remarks</td>
    <td><?php echo $process['issue_remark']?></td>
    <td><?php echo $process['loan_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>&nbsp;</td>
    <td>Signatures</td>
    <td><?php echo $issue_sign?></td>
    <td><?php echo $loan_sign?></td>
</tr>
<tr valign="top">
    <td>&nbsp;</td>
    <th width=200 align="left"></th>
    <th width=200 align="left">Returned By</th>
    <th width=200 align="left">Received By</th>
</tr>
<tr valign="top">
    <td>&nbsp;</td>
    <td>Name</td>
    <td><?php echo $returns['returned_by']?></td>
    <td><?php echo $returns['received_by']?></td>
</tr>
<tr valign="top" class="alt">
    <td>&nbsp;</td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
</tr>
<tr valign="top">
    <td>&nbsp;</td>
    <td>Remarks</td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>&nbsp;</td>
    <td>Signatures</td>
    <td><?php echo $return_sign?></td>
    <td><?php echo $receive_sign?></td>
</tr>
<?php
}
?>
</table>
  </td>
</tr>
<!--
<tr><td colspan=2 align="right" valign="middle">            
    <a class="button" onclick="print_preview()"><img src="images/print.png"> Print Preview</a> &nbsp; 
</td></tr>
-->
</table>
&nbsp;
<script>
function  print_preview(){
  window.open("./?mod=loan&sub=loan&act=print_complete&id=<?php echo $_id?>", 'print_preview');
}
</script>
