<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_delete && !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

$rejected_by = USERID;
if (isset($_POST['unapprove']) && ($_POST['unapprove'] == 1)){
    $reject_remark = mysql_real_escape_string($_POST['remark']);
    $query = "INSERT INTO loan_reject(id_loan, rejected_by, reject_date, reject_remark, reject_sign)
              VALUES($_id, $rejected_by, now(), '$reject_remark', '$_POST[signature]')";
    mysql_query($query);
    error_log(mysql_error().$query);
    if (mysql_affected_rows()>0){
        // update last request status
        $query = "UPDATE loan_request SET status = 'REJECTED' WHERE id_loan = $_id";
        mysql_query($query);
    }
	
	ob_clean();
	header('Location: ./?mod=service&sub=service&act=view&id=' . $_id);
	ob_end_flush();
	exit;

}
$format_date = '%d-%b-%Y %H:%i:%s';
$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
           date_format(request_date, '$format_date') as request_date,  
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
  
if ($rec['status'] == APPROVED) 
  $caption = 'Request Approved (In-Process)';
else
  $caption = 'Request Pending Approval (Rejection)';
  
echo <<<TEXT
<h4 style="color: #fff">$caption</h4>
<table cellpadding=4 cellspacing=1 class="service_table form" >
  <tralign="left">
    <th align="left" width=130>No</td>
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
    <td align="left">Service Date</td>
    <td align="left">$rec[start_loan]</td>
  </tr>  
  <tr valign="top">  
    <td align="left">Category</td>
    <td align="left">$rec[category_name]</td>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Remarks</td>
    <td align="left">$rec[remark]</td>    
  </tr>
</table>
<br/>

TEXT;


if ($rec['status'] == PENDING) {
  //$users = get_user_list();  
  $rejected_by = USERNAME;
  $reject_date = date('j-M-Y H:i:s');
  echo <<<TEXTA
  <form method="post">
  <input type="hidden" name="unapprove" value="0">
  <input type="hidden" name="signature" value="">
  <table cellpadding=4 cellspacing=1 class="service_table form" >
    <tr align="left">
      <th align="left" width=130>Rejected by</th>
      <th align="left">$rejected_by</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Rejection</td>
      <td align="left">$reject_date</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks</td>
      <td align="left"><textarea rows=3 cols=60 name="remark"></textarea></td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Signature</td>
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
  </table><br/>
  <div style="width:400px; text-align: right">
    <a class="button" title="Back to Service Request list" id="btn_back"
        href="javascript:void(0)">Cancel</a>
    <a class="button" title="Reject Service Request" id="btn_reject"
        href="javascript:void(0)">Reject</a>
  </div>
  </form>
  <br/>
  <script type="text/javascript" src="./js/signature.js"></script>
  <script>
  $('#btn_back').click(function (e){
  	location.href='.?mod=service';
  });
  $('#btn_reject').click(function (e){
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
  });
  </script>
  
TEXTA;

}
?>
