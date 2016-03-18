<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y H:i:s');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

if (isset($_POST['acknowledge']) && ($_POST['acknowledge'] == 1)){
    
    $query = "UPDATE loan_request SET status = 'COMPLETED' WHERE id_loan=$_id";
    mysql_query($query);
    $approver_id = USERID;
    $query = "UPDATE loan_process SET 
              acknowledged_by = $approver_id, 
              acknowledge_date = now(),  
              acknowledge_remark = '$_POST[acknowledge_remark]' 
              WHERE id_loan = $_id";
    mysql_query($query);
    
    $query = "UPDATE loan_signature SET 
              acknowledge_sign = '$_POST[acknowledge_signature]' 
              WHERE id_loan = $_id";
    mysql_query($query);

    // avoid refreshing the page
    goto_view($_id, COMPLETED);
}


// get request data
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
$approved_by = $users[$process['approved_by']];
$issued_by = $users[$process['issued_by']];
$acknowledged_by = FULLNAME;

$approve_sign = '<img src="'.get_signature($_id, 'approve').'" class="signature">';
$issue_sign = '<img src="'.get_signature($_id, 'issue').'" class="signature">';
$loan_sign = '<img src="'.get_signature($_id, 'loan').'" class="signature">';
$return_sign = '<img src="'.get_signature($_id, 'return').'" class="signature">';
$receive_sign = '<img src="'.get_signature($_id, 'receive').'" class="signature">';

?>

<h4>Acknowledge Returned Item</h4>
<form method="post">
<table  class="loanview acknowledge" border=0 cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    $returns = array_merge($returns, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];
    display_issuance_process_approval($issue, $signs); 
    display_return_process_approval($returns, $signs, true, true); 
?>
  </td>
</tr>
<tr>
	<td colspan=2 align="right">
	<input type="image" onclick="return submit_return()"  src="images/submit.png">
	</td>
</tr>
</table>
<input type="hidden" name="acknowledge_signature">
<input type="hidden" name="acknowledge">
</form>
&nbsp;
<script type="text/javascript" src="js/signature.js"></script>
<script>
function submit_return(){
    var frm = document.forms[0]
    if (isCanvasEmpty){
        alert('Please sign-in to acknowledge this return!');
        return false;
    }
    var cvs = document.getElementById('imageView');
    frm.acknowledge_signature.value = cvs.toDataURL("image/png");    
    frm.acknowledge.value = 1;
    frm.submit();
    return true;
}
</script>
