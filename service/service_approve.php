<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_delete && !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
           date_format(request_date, '$format_date') as request_date,  purpose, 
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
  $rec = mysql_fetch_assoc($rs);

$need_approval = ($rec['without_approval'] == 0);
if (isset($_POST['approve']) && ($_POST['approve'] == 1)){

    $approved_by = USERID;
    $approval_date = convert_date($_POST['approval_date'], 'Y-m-d H:i:s');
    $approval_remark = $_POST['remark'];
    $query = "INSERT INTO loan_process(id_loan, approved_by, approval_date, approval_remark)
              VALUES($_id, $approved_by, '$approval_date', '$approval_remark')";
    mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_affected_rows()){
    
        // update last request status
        if (!$need_approval && (USERGRP==GRPADM))
            $query = "UPDATE loan_request SET status = 'PENDING' WHERE id_loan = $_id";
        else
            $query = "UPDATE loan_request SET status = 'APPROVED' WHERE id_loan = $_id";
        mysql_query($query);
        //echo mysql_error().$query;
        
        // store signature
        $query = "REPLACE INTO loan_signature(id_loan, approve_sign)
                  VALUES ($_id, '$_POST[signature]')";
        mysql_query($query);
        //echo mysql_error().$query;
        
        // sending notification
        send_approved_service_request_notification($_id);
		
		// avoid refreshing the page
		ob_clean();
		header('Location: ./?mod=service&sub=service&act=view&id=' . $_id);
		ob_end_flush();
		exit;
    }
}
  
if ($rec['status'] == REJECTED) 
  $caption = 'Rejected Request Approval (Re-Approving)';
else
  $caption = 'Request Pending Approval (Approving)';
  
echo '<h4 style="color: #fff">'.$caption.'</h4>';
display_service_request($rec);
/*
echo <<<TEXT

<table width="400" cellpadding=4 cellspacing=1 class="service_table" >
  <tr valign="middle" align="left">
    <th align="left" width=130>No</th>
    <th align="left">$transaction_prefix$rec[id_loan]</th>
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
    <td align="left">Requestor Remarks</td>
    <td align="left">$rec[remark]</td>    
  </tr>
</table>
<br/>

TEXT;
*/
if ($rec['status'] == REJECTED) { 
	$query = "SELECT lrej.*, full_name as rejected_by 
				FROM loan_reject lrej 
				LEFT JOIN user u ON u.id_user = lrej.rejected_by 
				WHERE id_loan = $_id";
	$rs = mysql_query($query);
	if ($rs)// && (mysql_num_rows($rs)>0))
		$reject = mysql_fetch_assoc($rs);
  display_service_rejection($reject);
  /*
  echo <<<TEXTA
  <table width="400" cellpadding=4 cellspacing=1 class="service_table" >
    <tr align="left">
      <th align="left" width=130>Rejected by</th>
      <th align="left">$reject[rejected_by]</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time Reject</td>
      <td  align="left">$reject[reject_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks by Rejector</td>
      <td  align="left">$reject[reject_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Rejector Signature</td>
      <td align="left">
            <img src="$reject[reject_sign]" width=200 height=80>
      </td>
    </tr>
  </table>
  <br/>

TEXTA;
*/

}
if (($rec['status'] == PENDING) || ($rec['status'] == REJECTED)) {
  //$users = get_user_list();  
  $approved_by = USERNAME;
  $approval_date = date('j-M-Y H:i');

  $fold_btn = '<div class="foldtoggle"><a id="btn_service_handle_form" rel="open" href="javascript:void(0)">&uarr;</a></div>';

  echo <<<TEXTA
  <form method="post">
<table width=400 cellpadding=2 cellspacing=1 class="service_table detail issuance" >
  <tr valign="top" align="left"><th align="left" colspan=2>Request Approval $fold_btn</th></tr>  
  <tbody  id="service_handle_form">  
  <input type="hidden" name="approval_date" value="$approval_date">
  <input type="hidden" name="approve" value="0">
  <input type="hidden" name="signature" value="">
  
    <tr align="left">
      <td align="left" width=130>Approved by</t>
      <td align="left">$approved_by</td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time Approve</td>
      <td align="left">$approval_date</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks by Approver</td>
      <td align="left"><textarea rows=4 cols=40 name="remark"></textarea></td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Approver Signature</td>
      <td align="left">
        <div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
      </td>
    </tr>
    </tbody>
      <tfoot>
      <tr><td colspan=2>
        <br/> 
        <div style="text-align:right; width:95%" >
        <a class="button" title="Approve Service Request" id="btn_approves"
            href="javascript:void(0)">Approve</a>
        </div>
        <br>
        </td></tr>
      </tfoot>
    </table>
	<script>
	$('#btn_service_handle_form').click(function (e){
		toggle_fold(this);
	});
	</script>
  
  <br/>
  
  </form>
  <br/>
  <script type="text/javascript" src="./js/signature.js"></script>
  <script>
  $('#btn_approves').click(function (e){
    if (document.forms[0].remark.value == ''){
        alert('Please fill in the remark!');
        return false;
    }
    if (isCanvasEmpty){
        alert('Please sign-in on signature space');
        return false;
    }
    var ok = confirm('Are you sure approve this request?');
    if (!ok)
        return false;

    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    document.forms[0].approve.value=1;
    document.forms[0].submit();
    return true;
  });
  </script>
  
TEXTA;

}
?>
