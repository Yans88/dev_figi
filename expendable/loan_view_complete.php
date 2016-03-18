<?php

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
$loaned_items = get_request_items($_id);
$returned_items = get_returend_items($_id);
$item_list = build_returned_item_list($loaned_items, $returned_items);

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

$accessories = '<ol style="margin:0;padding-left:15px;padding-top:0 ">';
$accessories_list = get_accessories_by_loan($_id);
foreach($accessories_list as $idacc => $acc)
    $accessories .= '<li>'.$acc . '</li>';
$accessories .= '</ol>';

if ($need_approval)
    $caption = 'Acknowleged Returned Items';
else
    $caption = 'Returned Items';
    
?>
<h4><?php echo $caption?></h4>
<table  class="loanview complete" border=0 cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    $returns = array_merge($returns, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

if ($need_approval){
    display_issuance_process_approval($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process_approval($returns, $signs, true, false); 
} 
else {
    display_issuance_process($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process($returns, $signs); 
}
?>
  </td>
</tr>
<tr><td colspan=2 align="right" valign="middle">            
    <a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a> &nbsp; 
</td></tr>
</table>
<br/>&nbsp;
<script>
function  print_preview(){
  window.open("./?mod=loan&sub=loan&act=print_complete&id=<?php echo $_id?>", 'print_preview');
}
</script>
