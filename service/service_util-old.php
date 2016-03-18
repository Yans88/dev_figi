<?php

$service_statuses = array(
'PENDING' => 'Submitted',
'APPROVED' => 'Approved',
'ISSUED' => 'In-Progress',
'COMPLETED' => 'Completed',
'REJECTED' => 'Rejected');

// status : PENDING, APPROVED, LOANED, RETURNED
function count_service_request_by_status($status){
    $result = 0;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT count(lr.id_loan) 
				FROM loan_request lr 
				LEFT JOIN category c ON lr.id_category=c.id_category 
				WHERE category_type = 'SERVICE' ";
    if (!SUPERADMIN)
        $query .= " AND c.id_department = $dept ";
    if ($status != '')
        $query .= " AND status = '$status' ";
    $rs = mysql_query($query);
	//echo $query.mysql_error();
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function query_service_request_by_status($status, $start = 0, $limit = RECORD_PER_PAGE){
	$dept = defined('USERDEPT') ? USERDEPT : 0;
	$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y %H:%i') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
			 date_format(request_date, '%d-%b-%Y %H:%i') as request_date, 
			 user.full_name as requester, category_name, category_type, quantity, remark, status, purpose,
			 approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
			 date_format(return_date, '%d-%b-%Y') as return_date, 
			 return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark 	 
			 FROM loan_request lr 
			 LEFT JOIN user ON requester = user.id_user 
			 LEFT JOIN category ON lr.id_category = category.id_category 
			 LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
			 WHERE category_type = 'SERVICE' ";
    if (!SUPERADMIN)
        $query .= " AND category.id_department = $dept ";
	if ($status != '')
		$query .= " AND status = '$status' ";
	$query .= " ORDER BY request_date DESC LIMIT $start, $limit";
	$rs = mysql_query($query);
	//echo $query;
	return $rs;
}

function get_service_request_by_status($status, $start = 0, $limit = RECORD_PER_PAGE){
	$result = array();
	$rs = query_service_request_by_status($status, $start, $limit);
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;    
	return $result;
}

function get_service_request($id = 0){
    $result = array();
    $dtf = "'%d-%b-%Y'";
    $tmf = "'%H:%i:%s'";
    $dtf = "'%d-%b-%Y %H:%i'";
    $query  = "SELECT lr.id_loan, date_format(start_loan, $dtf) as start_loan, date_format(approval_date, $tmf) as approval_time, 
             date_format(end_loan, $dtf) as end_loan, date_format(return_date, $tmf) as return_time, lr.id_category, 
             date_format(request_date, $dtf) as request_date, date_format(request_date, $tmf) as request_time, 
             user_email, user.full_name as requester, category_name, quantity, remark, status, category.id_department, 
             approved_by, date_format(approval_date, $dtf) approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
             date_format(return_date, $tmf) as return_time, date_format(return_date, $dtf) as return_date, purpose, 
             return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark,
             (SELECT full_name FROM user u WHERE u.id_user = issued_by) issued_by_name, date_format(issue_date, $dtf) issue_date,
             (SELECT full_name FROM user u WHERE u.id_user = returned_by) returned_by_name
             FROM loan_request lr 
             LEFT JOIN user ON requester = user.id_user 
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             WHERE lr.id_loan = '$id' ";
    $rs = mysql_query($query); 
    //echo $query.mysql_error();
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_service_out($id = 0){
    $result = array();
    $format_date = "%d-%b-%Y %H:%i";
    $query = "SELECT lo.*, department_name, location_name, 
              date_format(loan_date, '$format_date') as loan_date, 
              date_format(return_date, '$format_date') as return_date 
              FROM loan_out lo 
              LEFT JOIN department d ON d.id_department = lo.id_department 
              LEFT JOIN location l ON l.id_location = lo.id_location 
              WHERE lo.id_loan = '$id' ";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_service_request_rejected($id = 0){
	$reject = array();
    $format_date = "%d-%b-%Y %H:%i";
	$query = "SELECT lr.*, user.full_name rejector_name, 
			date_format(reject_date, '$format_date') as reject_date 
			FROM loan_reject lr
			LEFT JOIN user ON user.id_user = lr.rejected_by 
			WHERE id_loan = $id";
	$rs = mysql_query($query);
  	if ($rs) $reject = mysql_fetch_assoc($rs);
  	return $reject;
}

function send_submit_service_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['service'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_service_request($id);
    if (count($data) == 0) return false;
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'],  'service');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/service-request-submit.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Service Request ('. $request_no . ') by ' . $data['requester'];
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'service', 'email');
            process_notification($id_msg);
            writelog('send_submit_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
        }
        error_log(serialize($emails));
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'service');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/service-request-submit.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'service', 'sms');
        process_notification($id_msg);
        writelog('send_submit_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function send_approved_service_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['service'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_service_request($id);
    if (count($data) == 0) return false;
  
    $users = get_user_list();  
    $approver = $users[$data['approved_by']];
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'],  'service');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/service-request-approved.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Service Request ('. $request_no . ') by ' . $data['requester'] . ' has been Approved';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'service', 'email');
            process_notification($id_msg);        
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'service');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/service-request-approved.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to,  null, $message, null, 'service', 'sms');
        process_notification($id_msg);
        writelog('send_approved_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
    
}


function send_completed_service_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['service'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_service_request($id);
    if (count($data) == 0) return false;
  
    $users = get_user_list();  
    $approver = $users[$data['approved_by']];
    
	$data['prepared_by'] = $data['issued_by_name'];
    $data['prepare_datetime'] = $data['issue_date'];
    $data['prepare_remark'] = $data['issue_remark'];

    $data['completed_by'] = $data['returned_by_name'];
    $data['complete_datetime'] = $data['return_date'];
    $data['complete_remark'] = $data['return_remark'];
    $data['request_datetime'] = $data['request_date'];
    $data['expected_service_datetime'] = $data['loan_date'];
        
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
	$data['request_no'] = $request_no;
    //error_log(serialize($data));
    //error_log(serialize($config));
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'],  'service');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        //error_log(serialize($emails));
        if (count($emails) > 0) {
            $message = compose_message('messages/service-request-completed.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Service Request ('. $request_no . ') by ' . $data['requester'] . ' has been Completed';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'service', 'email');
            process_notification($id_msg);        
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'service');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/service-request-completed.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to,  null, $message, null, 'service', 'sms');
        process_notification($id_msg);
        writelog('send_approved_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
    
}

function send_process_service_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['service'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_service_request($id);
    if (count($data) == 0) return false;
  
    //$users = get_user_list();  
    $data['prepared_by'] = $data['issued_by_name'];
    $data['prepare_datetime'] = $data['issue_date'];
    $data['prepare_remark'] = $data['issue_remark'];
    $data['request_datetime'] = $data['request_date'];
    $data['expected_service_datetime'] = $data['loan_date'];
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    $data['request_no'] = $request_no;
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'],  'service');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/service-request-prepared.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Service Request ('. $request_no . ') by ' . $data['requester'] . ' has been Prepared';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'service', 'email');
            process_notification($id_msg);        
        }
       // error_log(serialize($emails));
    }
    //error_log('lewat');
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'service');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/service-request-prepared.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to,  null, $message, null, 'service', 'sms');
        process_notification($id_msg);
        writelog('send_processs_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
    
}

function export_service_request_status($status){
    $crlf = "\r\n";
    ob_clean();
    ini_set('max_execution_time', 60);
    $today = date('dMY');
    $fname = 'figi_'.strtolower($status)."_service-$today.csv";
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=$fname");
    header("Pragma: no-cache");
    header("Expires: 0");
    $transaction_prefix = TRX_PREFIX_SERVICE;
    $total = count_service_request_by_status($status);
    $rs = query_service_request_by_status($status, 0, $total);
    if ($rs) // && (mysql_num_rows($rs)>0)
        switch(strtolower($status)){
        case 'pending':
            echo 'No,Date of Request,Requestor,Service Date,Category,Remarks'.$crlf;
                while ($rec = mysql_fetch_assoc($rs)){
                    echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],";
                    echo "$rec[start_loan],$rec[category_name],$rec[remark]$crlf";
                }
            break;
        case 'rejected':
            echo 'No,Date of Request,Requestor,Service Date,Category,Remarks'.$crlf;
                while ($rec = mysql_fetch_assoc($rs)){
                    echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],";
                    echo "$rec[start_loan],$rec[category_name],$rec[remark]$crlf";
                }
            break;
        case 'returned':
            echo 'No,Date of Request,Requestor,Service Date,Category,Remarks'.$crlf;
                while ($rec = mysql_fetch_assoc($rs)){
                    echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],";
                    echo "$rec[start_loan],$rec[category_name],$rec[remark]$crlf";
                }
            break;
        case 'loaned':
            echo 'No,Date of Request,Requestor,Service Date,Category,Remarks'.$crlf;
                while ($rec = mysql_fetch_assoc($rs)){
                    echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],";
                    echo "$rec[start_loan],$rec[category_name],$rec[remark]$crlf";
                }
            break;
        case 'completed':
            echo 'No,Date of Request,Requestor,Service Date,Category,Remarks'.$crlf;
                while ($rec = mysql_fetch_assoc($rs)){
                    echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],";
                    echo "$rec[start_loan],$rec[category_name],$rec[issue_remark]$crlf";
                }
            break;
 		}
    ob_end_flush();
    exit;
}
function get_attachment_service($id){
	$query = "SELECT * FROM service_request_attachment WHERE id_attach = $id";
	$rs = mysql_query($query);
	$data = mysql_fetch_assoc($rs);
	return $data;
}
function get_attachment_list($id){
	$query = "SELECT id_attach,filename FROM service_request_attachment WHERE id_loan = $id";
	$rs = mysql_query($query);
	$data = array();
	while($rec = mysql_fetch_assoc($rs)){
		$data[] = $rec;
	}
	$str = "<ul style='padding-left: 21px;margin: 0px;'>";
	
	foreach($data as $row){
		$str .= "<li><a href='./?mod=service&sub=service&act=get_files&id=$row[id_attach]'>$row[filename]</a></li>"; 
	}
	$str .= "</ul>";
	return $str;
}
function display_service_request($rec, $forprint = false){
	
    global $transaction_prefix;
	$fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_service_request" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;
	$rec['attachment_list'] = get_attachment_list($rec['id_loan']);
	
    echo <<<REQUEST
<table cellpadding=3 cellspacing=0 class="service_table detail request" >
  <tr valign="top" align="left">
    <th align="left" colspan=2>Service Request $fold_btn</th>
	
  </tr>  
  <tbody id="service_request">
  <tr valign="top" align="left">
    <td align="left" width=150>Request No.</td>
    <td align="left">$transaction_prefix$rec[id_loan]</td>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Date/Time of Request</td>
    <td align="left">$rec[request_date]</td>
  <tr valign="top">  
    <td align="left">Requestor</td>
    <td align="left">$rec[requester]</td>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Expected Service Date</td>
    <td align="left">$rec[start_loan]</td>
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
  <tr valign="top" class="alt">  
    <td align="left">File Attachment</td>
    <td align="left">$rec[attachment_list]</td>    
  </tr>
  $rec[extra_data]
  </tbody>
</table>
<script>
$('#btn_service_request').click(function (e){
	toggle_fold(this);
});
</script>
REQUEST;
}

function display_service_rejection($rec, $forprint = false){
	$fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_service_rejection" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;

    echo <<<REJECT
<table cellpadding=3 cellspacing=1 class="service_table detail rejection" >
  <tr valign="top" align="left">
    <th align="left" colspan=2>Service Rejection $fold_btn</th>
  </tr>  
  <tbody id="service_rejection">
    <tr align="left">
      <td align="left" width=150>Rejected by</td>
      <td align="left">$rec[rejected_by]</td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Rejection</td>
      <td  align="left">$rec[reject_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks</td>
      <td  align="left">$rec[reject_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Signature</td>
      <td align="left"><img class='signature' src="$rec[signature]"></td>
	</tr>
  </tbody>
</table>
<script>
$('#btn_service_rejection').click(function (e){
	toggle_fold(this);
});
</script>
REJECT;
}

function display_service_approval($rec, $forprint = false){
	$fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_service_approval" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;

    echo <<<APPROVAL
<table cellpadding=3 cellspacing=1 class="service_table detail approval" >
  <tr valign="top" align="left">
    <th align="left" colspan=2>Service Approval $fold_btn</th>
  </tr>  
  <tbody id="service_approval">
    <tr align="left">
      <td align="left" width=130>Approved by</td>
      <td align="left">$rec[approved_by]</td>
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
      <td align="left"><img class='signature' src="$rec[signature]"></td>
    </tr>
  </tbody>
</table>
<script>
$('#btn_service_approval').click(function (e){
	toggle_fold(this);
});
</script>
APPROVAL;
}

function display_service_issuance($rec, $forprint = false){
	$fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_service_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;

    echo <<<ISSUANCE
<table cellpadding=3 cellspacing=1 class="service_table detail issuance" >
  <tr valign="top" align="left">
    <th align="left" colspan=2>Service Preparation In-Progress $fold_btn</th>
  </tr>  
  <tbody id="service_issuance">
    <tr align="left">
      <td align="left" width=150>Prepared by</td>
      <td align="left">$rec[issued_by_name]</td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Preparation</td>
      <td  align="left">$rec[issue_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks </td>
      <td  align="left">$rec[issue_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Signature</td>
      <td align="left"><img class='signature' src="$rec[signature]"></td>
    </tr>
  </tbody>
</table>
<script>
$('#btn_service_issuance').click(function (e){
	toggle_fold(this);
});
</script>
ISSUANCE;
}


function display_service_completion($rec, $forprint = false){
	$fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_service_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;

    echo <<<ISSUANCE
<table cellpadding=3 cellspacing=1 class="service_table detail issuance" >
  <tr valign="top" align="left">
    <th align="left" colspan=2>Service Completion $fold_btn</th>
  </tr>  
  <tbody id="service_completion">
    <tr align="left">
      <td align="left" width=150>Completed by</td>
      <td align="left">$rec[returned_by]</td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Completion</td>
      <td  align="left">$rec[return_date]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remarks </td>
      <td  align="left">$rec[return_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Signature</td>
      <td align="left"><img class='signature' src="$rec[signature]"></td>
    </tr>
  </tbody>
</table>
<script>
$('#btn_service_completion').click(function (e){
	toggle_fold(this);
});
</script>
ISSUANCE;
}


function get_extra_form($id_category, $id_page){
    $field_list = get_extra_field_list($id_category, $id_page);
    $field_data = get_extra_data_list($id_category, $id_page);
    $extra_data = null;
    $no = 0;
    foreach ($field_list as $field){
        $class_name = ($no++ % 2 != 0) ? 'alt' : 'normal';
        if (strtoupper($field['field_type']) == 'BOOLEAN')
            $value = ($field_data[$field['id_field']] == '1') ? 'Yes' : 'No';
        else
            $value = $field_data[$field['id_field']];
        $extra_data .=<<<ROW
    <tr class='$class_name'>
        <td>$field[field_name]</td>
        <td>$value</td>
    </tr>
ROW;
    }
    return $extra_data;
}

?>
