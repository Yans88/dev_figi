<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_delete && !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

$users = get_user_list();  
$rejected_by = USERID;
if (isset($_POST['unapprove']) && ($_POST['unapprove'] == 1)){
    $reject_remark = $_POST['remark'];
    $query = "INSERT INTO loan_reject(id_loan, rejected_by, reject_date, reject_remark, reject_sign)
              VALUES($_id, $rejected_by, now(), '$reject_remark', '$_POST[signature]')";
    mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_affected_rows() >0){
        // update last request status
        $query = "UPDATE loan_request SET status = 'REJECTED' WHERE id_loan = $_id";
        mysql_query($query);
    }
    send_unapproved_request_notification($_id);
    goto_view($_id, PENDING);

}
$format_date = '%d-%b-%Y %H:%i:%s';
$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
           date_format(request_date, '$format_date') as request_date, long_term,  
           user.full_name as requester, category_name, quantity, remark, status, 
		   approved_by, date_format(approval_date, '$format_date') as approval_date, approval_remark, 
           issued_by, date_format(issue_date, '$format_date') as issue_date, issue_remark, 
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
  $request = mysql_fetch_assoc($rs);

$rejected_by = $users[USERID];
$reject_date = date('j-M-Y H:i:s');

if ($request['status'] == APPROVED) 
  $caption = 'Request Approved (In-Process)';
else
  $caption = 'Request Pending Approval (Rejection)';

$long_term_tag = null;
if ($request['long_term'] == 1)
    $long_term_tag =  ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';

$rejection['rejected_by_name'] = FULLNAME;
$rejection['reject_date'] = date('d-M-Y H:i');
?>

<h4><?php echo $caption?></h4>
<table cellpadding=3 cellspacing=1 class="loanview request" >
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td>
  <form method="post">
  <input type="hidden" name="unapprove" value="0">
  <input type="hidden" name="signature" value="">
    
    <?php display_rejection($rejection, true);?>
	
  </form>
  
  <script>
  function unapprove_loan(){
    if (document.forms[0].remark.value == ''){
        alert('Please fill in the remark!');
        return false;
    }
    if (isCanvasEmpty){
        alert('Please sign-in on signature space');
        return false;
    }
    var ok = confirm('Are you sure reject this request?');
    if (!ok)
        return false;

    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    document.forms[0].unapprove.value=1;
    document.forms[0].submit();
    return true;
  }
  </script>
  
</td></tr>
</table>
<br/>&nbsp;<br/>
