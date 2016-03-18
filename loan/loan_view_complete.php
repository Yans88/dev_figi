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
$returned_items = get_returned_items($_id);
$accessories = get_accessories_by_loan($_id);
$item_list = loan_item_list($loaned_items, $accessories);
//$returned_item_list = build_returned_item_list($loaned_items, $returned_items, false, false, $accessories);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);  
$users = get_user_list();  

$issue['chk'] = get_checklist($_id); 
  
$issue['total_loaned_items'] = count($loaned_items);
$issue['total_returned_items'] = count($returned_items);
$acknowledged_by = !empty($process['acknowledged_by']) ? $users[$process['acknowledged_by']] : null;
$approved_by = !empty($process['approved_by']) ? $users[$process['approved_by']] : null;
$issued_by = !empty($process['issued_by']) ? $users[$process['issued_by']] : null;

$approve_sign = '<img src="'.get_signature($_id, 'approve').'" class="signature">';
$issue_sign = '<img src="'.get_signature($_id, 'issue').'" class="signature">';
$loan_sign = '<img src="'.get_signature($_id, 'loan').'" class="signature">';
$return_sign = '<img src="'.get_signature($_id, 'return').'" class="signature">';
$receive_sign = '<img src="'.get_signature($_id, 'receive').'" class="signature">';
$acknowledge_sign = '<img src="'.get_signature($_id, 'acknowledge').'" class="signature">';

$no_of_loaned_items = count($loaned_items);
$no_of_returned_items = count($returned_items);
$is_all_item_returned = $no_of_loaned_items == $no_of_returned_items;

$parent_info = get_parent_info($request['id_user']);
$issue['parent_name'] = $parent_info['father_name'];

$issue['students_loan'] = $request['students_loan'];
$issue['parent_info'] = $parent_info;

$caption = ($need_approval) ?  'Acknowleged Returned Items': 'Returned Items';
    
?>
<table width="100%" class="itemlist loan complete" border=0 cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    //$returns = array_merge($returns, $process);
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
	$process['quick_issue'] = $issue['quick_issue'];
    display_return_process($returns, $signs, false, $process); 
}
?>
  </td>
</tr>
<?php
if (!empty($issue['chk'])){ 
?>
<tr valign="top"><td><?php display_checklist($issue);?></td></tr>
<?php
} 
?>
<tr>
    <td colspan=2 valign="middle">
    <table cellpadding=2 cellspacing=1 >
        <tr>
            <td width="100%"><div class="note" id="issue_note" ><?php echo $messages['loan_issue_note']?></div></td>
        </tr>
    </table>
    </td>
</tr>
<tr><td colspan=2 align="right" valign="middle">            
<?php

	//if ( !$is_all_item_returned)
		//if ( (USERGROUP == GRPADM) && !SUPERADMIN && (USERDEPT==$request['id_department'])) {
	//if ($issue['quick_issue']==1)
		//echo '<a class="button" href="./?mod=loan&sub=quick_loan_return&id='.$_id.'">Return</a> &nbsp; ';
	//else
		//echo '<a class="button" href="./?mod=loan&sub=loan&act=return&id='.$_id.'">Return</a> &nbsp; ';
	//}
?>
 
    <a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a> &nbsp; 
</td></tr>
</table>
<br/>&nbsp;
<script>
function  print_preview(){
  window.open("./?mod=loan&sub=loan&act=print_complete&id=<?php echo $_id?>", 'print_preview');
}
</script>
