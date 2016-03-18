<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_delete  && !$i_can_update ) { // can approve
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$request = get_request($_id);
$need_approval = ($request['without_approval'] == 0);
//print_r($_POST);
if (isset($_POST['approve']) && ($_POST['approve'] == 1)){

    $approved_by = USERID;
    $approval_date = convert_date($_POST['approval_date'], 'Y-m-d H:i:s');
    $approval_remark = mysql_escape_string($_POST['remark']);
    $query = "REPLACE INTO loan_process(id_loan, approved_by, approval_date, approval_remark)
              VALUES($_id, $approved_by, '$approval_date', '$approval_remark')";
    mysql_query($query);
	//echo mysql_error().$query;
    if (mysql_affected_rows()){
        // update last request status
        if ((USERGROUP == GRPADM) && !$need_approval)
            $query = "UPDATE loan_request SET status = 'PENDING' WHERE id_loan = $_id";
        else
            $query = "UPDATE loan_request SET status = 'APPROVED' WHERE id_loan = $_id";
        mysql_query($query);
        //echo mysql_error().$query;
        
        // store signature
        $query = "REPLACE INTO loan_signature(id_loan, approve_sign)
                  VALUES ($_id, '$_POST[signature]')";
        mysql_query($query);
        
        // sending notification
        send_approved_request_notification($_id);
		
		// avoid refreshing the page
        goto_view($_id, PENDING);
        /*
		ob_clean();
		header('Location: ./?mod=loan&sub=loan&act=view&id=' . $_id);
		ob_end_flush();
		exit;
              */
    }
}

$user_info = get_user(USERID);
$approved_by = $user_info['full_name'];
$approval_date = date('j-M-Y H:i');
 
if ($request['status'] == REJECTED) 
	$caption = 'Rejected Request Approval (Re-Approving)';
else
	$caption = 'Request Pending Approval (Approving)';
    
$approval['approved_by_name'] = FULLNAME;
$approval['approval_date'] = date('d-M-Y H:i');

?>
<h4><?php echo $caption?></h4>
<table cellpadding=3 cellspacing=1 class="loanview request" >
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td>
  <form method="post">
  <input type="hidden" name="approve" value="0">
  <input type="hidden" name="signature" value="">
  <input type="hidden" name="approval_date" value="<?php echo date('Y-m-d H:i:s');?>">
<?php
/*
if ($request['without_approval'] == 0) { // request created as approval-type
	if ($request['status'] == REJECTED) { // if rejected prev, show it
		$query = "SELECT lrej.*, full_name as rejected_by 
					FROM loan_reject lrej 
					LEFT JOIN user u ON u.id_user = lrej.rejected_by 
					WHERE id_loan = $_id";
		$rs = mysql_query($query);
		if ($rs && (mysql_num_rows($rs)>0)) {
			$reject = mysql_fetch_assoc($rs);
  
  echo <<<TEXTA
  <table cellpadding=3 cellspacing=1 class="loanview approval rejected" >
    <tr align="left">
      <th align="left" width=130>Rejected by</th>
      <th align="left">$reject[rejected_by]</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time Rejection</td>
      <td  align="left">$reject[reject_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks</td>
      <td  align="left">$reject[reject_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Signature</td>
      <td align="left">
            <img src="$reject[reject_sign]" width=200 height=80>
      </td>
    </tr>
  </table>
  <br/>

TEXTA;
		}
	} // reject info
}
    */
if (($request['status'] == PENDING) || ($request['status'] == REJECTED)) {
    display_approval($approval, array(), true);
?>	

  <script>
  function approve_loan(){
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
    //document.forms[0].approval_date.value=1;
    document.forms[0].submit();
    return true;
  }
  </script>
<?php  
	} // PENDING/REJECTED
?>
  </form>
  </td></tr>
  </table>
<br/>&nbsp;<br/>
