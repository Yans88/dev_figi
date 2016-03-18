<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

if (isset($_POST['return']) && ($_POST['return'] == 1)){
    // store loan-out
    $loan_date = convert_date($_POST['loan_date'], 'Y-m-d');
    $return_date = convert_date($_POST['date_to_be_returned'], 'Y-m-d');
    $query = "INSERT INTO loan_return(id_loan, returned_by, received_by) 
              VALUES ($_id, '$_POST[returned_by]', '$_POST[received_by]')";
    mysql_query($query);
    //echo mysql_error().$query;
    
    $query = "UPDATE loan_request SET status = 'RETURNED' WHERE id_loan=$_id";
    mysql_query($query);
    $admin_id = USERID;
    $query = "UPDATE loan_process SET 
              received_by = $admin_id, 
              receive_date = now(), 
              receive_remark = '$_POST[receive_remark]', 
              returned_by = 0, 
              return_date = now(),  
              return_remark = '$_POST[return_remark]' 
              WHERE id_loan = $_id";
    mysql_query($query);
    
    $query = "UPDATE loan_signature SET 
              receive_sign = '$_POST[receive_signature]', 
              return_sign = '$_POST[return_signature]' 
              WHERE id_loan = $_id";
    mysql_query($query);
	// update item's status to Available For Loan
	$query = "SELECT serial_no FROM loan_out WHERE id_loan = $_id";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs))){
		$rec = mysql_fetch_assoc($rs);
		$query = "UPDATE item SET 
				  id_status = '".AVAILABLE_FOR_LOAN."' WHERE serial_no = '$rec[serial_no]'";
		mysql_query($query);
    }
    // sending notification
    send_returned_item_notification($_id);
    // avoid refreshing the page
    ob_clean();
    header('Location: ./?mod=loan&sub=loan&act=view_return&id=' . $_id);
    ob_end_flush();
    exit;
}


// get request data

$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
           date_format(request_date, '$format_date') as request_date,  
           user.full_name as requester, category_name, quantity, remark, status, 
		   approved_by, date_format(approval_date, '$format_date_only') as approval_date, approval_remark, 
           issued_by, date_format(issue_date, '$format_date_only') as issue_date, issue_remark, 
           loaned_by, date_format(loan_date, '$format_date_only') as loan_date, loan_remark 
           FROM loan_request lr 
           LEFT JOIN user ON requester = user.id_user 
           LEFT JOIN category ON lr.id_category = category.id_category 
		   LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan 
           WHERE lr.id_loan = ".$_id ." 
           ORDER BY request_date DESC ";

$rs = mysql_query($query);
//echo mysql_error().$query;
if (mysql_num_rows($rs)>0)
  $rec = mysql_fetch_assoc($rs);
  
$users = get_user_list();  
$approved_by = $users[$rec['approved_by']];
$approve_sign = get_signature($_id, 'approve');
$admin_name = USERNAME;

$issue_sign = '<img src="'.get_signature($_id, 'issue').'" width=200 height=80>';
$loan_sign = '<img src="'.get_signature($_id, 'loan').'" width=200 height=80>';
$query = "SELECT li.*, date_format(loan_date, '$format_date_only') as loan_date, 
          date_format(return_date, '$format_date_only') as return_date, department_name 
          FROM loan_out li 
          LEFT JOIN department d ON d.id_department = li.id_department 
          WHERE id_loan = $_id";
$rs = mysql_query($query);
//echo mysql_error().$query;
if (mysql_num_rows($rs)>0){
    $issue = mysql_fetch_assoc($rs);
}

$caption = 'Return Item';
echo <<<TEXT

<h4 style="color: #fff">$caption</h4>
<form method="post">
<table  class="itemlist" border=0 cellpadding=2 cellspacing=1>
<tr valign="top">
    <td colspan=2>
    <table width="100%" cellpadding=3 cellspacing=1 class="itemlist" >
      <tr valign="top" align="left">
        <th align="left" >No</td>
        <th align="left" width=200>$transaction_prefix$rec[id_loan]</th>
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
        <td align="left">Quantity</td>
        <td align="left">$rec[quantity]</td>    
      </tr>  
      <tr valign="top">  
        <td align="left">Requestor Remarks</td>
        <td align="left">$rec[remark]</td>    
      </tr>
    </table>
    </td>
    <td colspan=2>
    <table width="100%" cellpadding=2 cellspacing=1 class="itemlist" >
      <tr valign="top" align="left">
        <th align="left" width=250>Item Serial No.</td>
        <th align="left" width=250>$issue[serial_no]</th>
      </tr>  
      <tr valign="top">  
        <td align="left">Category</td>
        <td align="left">$rec[category_name]</td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Basic Accessories Included</td>
        <td align="left">$issue[basic_accessories]</td>
      <tr valign="top">  
        <td align="left">Loan Out to</td>
        <td align="left">$issue[name]</td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC</td>
        <td align="left">$issue[nric]</td>
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left">$issue[contact_no]</td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Location</td>
        <td align="left">$issue[location]</td>    
      </tr>
      <tr valign="top">  
        <td align="left">Department</td>
        <td align="left">$issue[department_name]</td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date (Sign Out)</td>
        <td align="left">$issue[loan_date]</td>    
      </tr>
      <tr valign="top">  
        <td align="left">Date (To be Returned)</td>
        <td align="left">$issue[return_date]</td>    
      </tr>  
    </table>

    </td>
</tr>
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200 align="center">Approved By</th>
    <th width=200 align="center">Issued By</th>
    <th width=200 align="center">Loaned By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td>$approved_by</td>
    <td>$admin_name</td>
    <td>$issue[name]</td>
</tr>
<tr valign="top">
    <td>Date/Time Signature</td>
    <td>$rec[approval_date]</td>
    <td>$rec[issue_date]</td>
    <td>$rec[loan_date]</td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td>$rec[approval_remark]</td>
    <td>$rec[issue_remark]</td>
    <td>$rec[loan_remark]</td>
</tr>
<tr valign="top">
    <td>Signatures</td>
    <td><img class='signature' src="$approve_sign"></td>
    <td>$issue_sign</td>
    <td>$loan_sign</td>
</tr>
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200 align="center">Returned By</th>
    <th width=200 align="center">Received By</th>
    <td align="center" valign="middle" rowspan=5>
    <input type=image height=50 src="images/submit.png" onclick="return submit_return()">
    </td>

</tr>
<tr valign="top">
    <td>Name</td>
    <td><input type=text name=returned_by size=30></td>
    <td><input type=text name=received_by size=30></td>
</tr>
<tr valign="top">
    <td>Date/Time Signature</td>
    <td>$today</td>
    <td>$today</td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><textarea name="return_remark" cols=22 rows=3></textarea></td>
    <td><textarea name="receive_remark" cols=22 rows=3></textarea></td>
</tr>
<tr valign="top">
    <td>Signatures</td>
    <td>
        <div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
    <td>
       <div id="signature-pad2" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView2" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
</tr>
<tr>
</table>
<input type=hidden name="receive_signature">
<input type=hidden name="return_signature">
<input type=hidden name="return">

</form>
<script type="text/javascript" src="./js/signature.js"></script>
<script type="text/javascript" src="./js/signature2.js"></script>
<script>
function submit_return(){
    var frm = document.forms[0]
    if (frm.returned_by.value == ''){
        alert('Please fill in who retrun the item!');
        return false;
    }
    if (frm.received_by.value == ''){
        alert('Please fill in who receive the item!');
        return false;
    }
    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var cvs = document.getElementById('imageView');
    frm.return_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.receive_signature.value = cvs.toDataURL("image/png");    
    frm.return.value = 1;
    //frm.submit();
    return true;
}
</script>

TEXT;

?>
