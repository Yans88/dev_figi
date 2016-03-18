<?php

// status : PENDING, APPROVED, LOANED, RETURNED
function count_request_by_status($status = ''){
    $result = 0;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT count(lr.id_loan) 
				FROM loan_request lr 
				LEFT JOIN category c ON c.id_category=lr.id_category 
				WHERE category_type = 'EQUIPMENT' ";
    if (!SUPERADMIN)
        $query .= " AND lr.id_department = $dept ";

	/** +here */
	if ($status != '' && is_array($status)){
		foreach($status as $key => $val_status){
			if($key==0)
				$query .= " AND status = '$val_status' ";
			else
				$query .= " OR status = '$val_status' ";
		}
		foreach($status as $key => $val_satus){
			$query .= " OR status = '$val_satus' ";
		}
	}
	elseif ($status != '' && !is_array($status))
		$query .= " AND status = '$status' ";

    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}


function query_request_by_status($status = '', $start = 0, $limit = RECORD_PER_PAGE, $ordby = 'request_date', $orddir = 'ASC'){
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
             date_format(request_date, '%d-%b-%Y') as request_date, without_approval, purpose, 
             user.full_name as requester, category_name, quantity, remark, status, long_term, 
             approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
             return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark,
             date_format(return_date, '%d-%b-%Y') as return_date               
             FROM loan_request lr 
             LEFT JOIN user ON requester = user.id_user 
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             WHERE category_type = 'EQUIPMENT' ";
    if (!SUPERADMIN)
        $query .= " AND lr.id_department = $dept ";

	/** +here **/
	if ($status != '' && is_array($status)){
		foreach($status as $key => $val_status){
			if($key==0)
				$query .= " AND status = '$val_status' ";
			else
				$query .= " OR status = '$val_status' ";
		}
		foreach($status as $key => $val_satus){
			$query .= " OR status = '$val_satus' "; //+
		}
	}
	elseif ($status != '' && !is_array($status))
		$query .= " AND status = '$status' ";

	$query .= " ORDER BY $ordby $orddir LIMIT $start, $limit";
	$rs = mysql_query($query);
	return $rs;
}


function get_request_by_status($status = '', $start = 0, $limit = RECORD_PER_PAGE, $ordby = 'request_date', $orddir = 'ASC'){
    $result = array();
	$rs = query_request_by_status($status, $start, $limit, $ordby, $orddir);
	$i = 0;
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;    
	return $result;
}

function get_request($id = 0){
    $result = array();
    $dtf = "'%d-%b-%Y %H:%i'";    
    $query = "SELECT lr.id_loan, date_format(start_loan, $dtf) as start_loan, date_format(end_loan, $dtf) as end_loan, purpose, 
                 date_format(request_date, $dtf) as request_date, nric, contact_no, without_approval, lr.id_category, long_term,   
                 user_email, user.full_name as requester, category_name, quantity, remark, status, category.id_department, user.id_user
                 FROM loan_request lr 
                 LEFT JOIN user ON requester = user.id_user 
                 LEFT JOIN category ON lr.id_category = category.id_category 
                 WHERE lr.id_loan = '$id' ";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

/** +here **/
function get_count_partial($id = 0){
	$query = "SELECT SUM(qty_returned) AS count_returned FROM loan_return WHERE id_loan=$id";
	$rs = mysql_query($query);
	$result = mysql_fetch_array($rs);
	if($result['count_returned']==NULL) $sum=0;
	else $sum=$result['count_returned'];
	return $sum;
}


function get_request_by_item($id = 0){
    $query = "SELECT id_loan FROM loan_item WHERE id_item = '$id' LIMIT 1";
    $rs = mysql_query($query); 
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        return get_request($rec[0]);
    }
    return 0;
}

function get_request_out($id = 0){
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

function get_request_return($id = 0){
    $result = array();
    $format_date = "%d-%b-%Y %H:%i";
    $query = "SELECT full_name received_by, (SELECT returned_by FROM loan_return WHERE id_loan = '$id') returned_by_name 
				FROM loan_process lp 
				LEFT JOIN user u ON lp.received_by=u.id_user 
				WHERE id_loan = '$id' ";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_request_process($id = 0){
    $result = array();
    $format_date = '%d-%b-%Y %H:%i';
    $query = "SELECT lp.*, 
                date_format(loan_date, '$format_date') as loan_date, 
                date_format(return_date, '$format_date') as return_date,
                date_format(approval_date, '$format_date') as approval_date,
                date_format(issue_date, '$format_date') as issue_date,
                date_format(receive_date, '$format_date') as receive_date,
                date_format(acknowledge_date, '$format_date') as acknowledge_date,
                (SELECT full_name FROM user WHERE id_user = approved_by) as approved_by_name, 
                (SELECT full_name FROM user WHERE id_user = issued_by) as issued_by_name, 
                (SELECT full_name FROM user WHERE id_user = received_by) as received_by_name 
                FROM loan_process lp 
                WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $result = mysql_fetch_assoc($rs);
    }
    return $result;
}

function get_request_rejection($id = 0){
    $result = array();
    $format_date = '%d-%b-%Y %H:%i';
    $query = "SELECT lr.*, full_name rejected_by_name, 
                date_format(reject_date, '$format_date') as reject_date 
                FROM loan_reject lr 
                LEFT JOIN user u ON u.id_user = lr.rejected_by 
                WHERE id_loan = $id";
    $rs = mysql_query($query);
    //echo mysql_error();
    if ($rs)
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function send_submit_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
    if (count($data) == 0) return false;

    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-request-submit.msg', $data);
            else
                $message = compose_message('messages/loan-request-submit.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Loan Request ('. $request_no . ') by ' . $data['requester'];
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-request-submit.sms', $data);
        else    
            $message = compose_message('messages/loan-request-submit.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_submit_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}


function send_approved_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
    if (count($data) == 0) return false;
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    $users = get_user_list();  
    $data['approver'] = $users[$data['approved_by']];
	
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0){
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-request-approved.msg', $data);
            else
                $message = compose_message('messages/loan-request-approved.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Loan Request ('. $request_no . ') by ' . $data['requester'] . ' has been Approved';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-request-approved.sms', $data);
        else
            $message = compose_message('messages/loan-request-approved.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_approved_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

/** +p elbas */
function send_unapproved_request_notification($id = 0){
error_log('send_unapproved_request_notification()');
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
	$rejection = get_request_rejection($id);
	$data = array_merge($data, $rejection);
    if (count($data) == 0) return false;
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    $users = get_user_list();  
    $data['approver'] = $users[$data['approved_by']];
	
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0){
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-request-unapproved.msg', $data);
            else
                $message = compose_message('messages/loan-request-unapproved.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Loan Request ('. $request_no . ') by ' . $data['requester'] . ' has been Un-Approved';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-request-unapproved.sms', $data);
        else
            $message = compose_message('messages/loan-request-unapproved.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_uapproved_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function send_returned_item_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
    if (count($data) == 0) return false;
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    //out
    $query = "SELECT *, date_format(loan_date, '%d-%b-%Y') as loan_date FROM loan_out WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $out = mysql_fetch_assoc($rs);
    } else {
        $out['serial_no'] = '';
        $out['loan_date'] = '';
    }
    //return
    $query = "SELECT * FROM loan_return WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $ret = mysql_fetch_assoc($rs);
    } 
    $data = array_merge($data, $ret);
    // get serial
    $items = get_item_serial_by_loan($id);
    $item_list = '';
    $total_item = count($items);
    if ( $total_item> 0)
        $item_list = implode("\r\n\t", $items);
    $figi_home = FIGI_URL;
    $data['item_list'] = $item_list;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-request-returned.msg', $data);
            else
                $message = compose_message('messages/loan-request-returned.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Loan ('. $request_no . ') has been Returned by ' . $ret['returned_by'];
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-request-returned.sms', $data);
        else
            $message = compose_message('messages/loan-request-returned.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_returned_item_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function send_loosing_item_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
    if (count($data) == 0) return false;
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    //out
    $query = "SELECT *, date_format(loan_date, '%d-%b-%Y') as loan_date FROM loan_out WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $out = mysql_fetch_assoc($rs);
    } else {
        $out['serial_no'] = '';
        $out['loan_date'] = '';
    }
    //return
    $query = "SELECT * FROM loan_return WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $ret = mysql_fetch_assoc($rs);
    } 
    // get serial
    $items = get_item_serial_by_loan($id);
    $item_list = '';
    $total_item = count($items);
    if ( $total_item> 0)
        $item_list = implode("\r\n\t", $items);
    $figi_home = FIGI_URL;
    $data['item_list'] = $item_list;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-request-lost.msg', $data);
            else
                $message = compose_message('messages/loan-request-lost.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Loan ('. $request_no . ') has been lost';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-request-lost.sms', $data);
        else
            $message = compose_message('messages/loan-request-lost.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_losing_item_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function get_item_serial_by_loan($id){
    $items = array();
    $query = "SELECT li.id_item, i.asset_no, i.serial_no 
                FROM loan_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE id_loan = $id ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec_item = mysql_fetch_assoc($rs)){
            $items[] = "$rec_item[serial_no] ($rec_item[asset_no]";
        }
    }
    return $items;
}

function get_email_tobe_notified($dept, $mod){
	$result = array();
    $grplist = GRPADM . ',' . GRPHOD;
	$query = "SELECT user_email FROM user 
                WHERE id_department = '$dept' AND id_group IN ($grplist) 
                ORDER BY user_email";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){
		while ($rec = mysql_fetch_row($rs))
			$result[] = $rec[0];
	}
	return $result;
}


function goto_view($id, $status){
    switch($status){
    case LOANED : $view_act = 'view_issue'; break;
    case RETURNED : $view_act = 'view_return'; break;
    case LOST: $view_act = 'view_lost'; break;
    case COMPLETED : $view_act = 'view_complete'; break;
    default: $view_act = 'view';
    }
    ob_clean();
    header('Location: ./?mod=loan&sub=loan&act='.$view_act.'&id=' . $id);
    ob_end_flush();
    exit;
}


/** +here **/
function get_request_items($id = 0,$_act=NULL){
    $result = array();
	if($_act=='return' || $_act=='view_issue' || $_act=='print_issue')
		$subquery = " AND li.id_item NOT IN (SELECT loan_return_item.id_item FROM loan_return_item WHERE id_loan = $id)"; //added
    $query = "SELECT li.id_item, i.asset_no, i.serial_no, status_name   
                FROM loan_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                LEFT JOIN status s ON i.id_status = s.id_status 
                WHERE id_loan = $id  $subquery";
    $rs = mysql_query($query);
   // echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}
function get_returend_items($id = 0){
    $result = array();
    $query = "SELECT li.*, i.asset_no, i.serial_no 
                FROM loan_return_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[$rec['id_item']] = $rec;
    return $result;
}

function loan_return_due_date_query($dept = 0){
    $dtf = '%d-%b-%Y';
    $query  = "SELECT lr.*, lo.*, DATE_FORMAT(lr.request_date, '$dtf') request_date, DATE_FORMAT(lo.loan_date, '$dtf') loan_date, 
                DATE_FORMAT(lo.return_date, '$dtf') return_date, c.*, u.*, lo.id_department, 
                DATE_FORMAT(lo.return_date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d') same_day,
                full_name requester  
                FROM loan_request lr 
                LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan 
                LEFT JOIN category c ON c.id_category = lr.id_category 
                LEFT JOIN user u ON lr.requester = u.id_user 
                WHERE status = 'LOANED' AND long_term != 1 AND  ";
    if (defined('LOAN_RETURN_LEAD_DAYS') && LOAN_RETURN_LEAD_DAYS > 0)
        $query .= ' UNIX_TIMESTAMP(lo.return_date) <= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.LOAN_RETURN_LEAD_DAYS.' DAY)) ';
    else
        $query .= 'UNIX_TIMESTAMP(lo.return_date) <= UNIX_TIMESTAMP()';
    if ($dept > 0) $query .= " AND (lo.id_department <> 0 AND lo.id_department = $dept)";
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    return $rs;
}

function loan_return_due_date_long_term_confirm(){
    $rs = null;
    $dtf = '%d-%b-%Y';
    global $configuration;
    /*
    $query  = "SELECT lr.*, lo.*, DATE_FORMAT(lr.request_date, '$dtf') request_date, DATE_FORMAT(lo.loan_date, '$dtf') loan_date, 
                DATE_FORMAT(lo.return_date, '$dtf') return_date, c.*, u.*, 
                DATE_FORMAT(lo.return_date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d') same_day 
                FROM loan_request lr 
                LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan 
                LEFT JOIN category c ON c.id_category = lr.id_category 
                LEFT JOIN user u ON lr.requester = u.id_user 
                WHERE status = 'ISSUED' AND long_term = 1 AND 
                ( DATE_FORMAT(lo.return_date, '%Y-%m-%d') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL ".LONG_TERM_LOAN_CONFIRM_PERIOD." MONTH), '%Y-%m-%d') )";
    */
    // get notification date for long term loan from configuration
    $jn = date('j-n');
    if (isset($configuration['loan']['long_term_notification_date']) && ($configuration['loan']['long_term_notification_date'] == $jn)){
        $query  = "SELECT lr.*, lo.*, DATE_FORMAT(lr.request_date, '$dtf') request_date, DATE_FORMAT(lo.loan_date, '$dtf') loan_date, 
                    DATE_FORMAT(lo.return_date, '$dtf') return_date, c.*, u.*, 
                    DATE_FORMAT(lo.return_date, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d') same_day 
                    FROM loan_request lr 
                    LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan 
                    LEFT JOIN category c ON c.id_category = lr.id_category 
                    LEFT JOIN user u ON lr.requester = u.id_user 
                    WHERE status = 'ISSUED' AND long_term = 1 ";
                    //AND ( DATE_FORMAT(lo.return_date, '%Y-%m-%d') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL ".LONG_TERM_LOAN_CONFIRM_PERIOD." MONTH), '%Y-%m-%d') )";
        $rs = mysql_query($query);
    }
    return $rs;
}

function send_loan_return_alert($data){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    
    $_dept = $data['id_department'];    
    $items = get_request_items($data['id_loan']);
    $acces = get_accessories_by_loan($data['id_loan']);
    $item_list = null;
    $accessories_list = null;
    foreach ($items as $rec)
        $item_list .= "\t - $rec[asset_no] ($rec[serial_no]) \r\n";
    foreach ($acces as $idacc => $accname)
        $accessories_list .= "\t - $accname\r\n";
    $id = $data['id_loan'];
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    $request_out = get_request_out($data['id_loan']);
	$data = array_merge($data, $request_out);    
    $request_ret = get_request_return($data['id_loan']);
	$data = array_merge($data, $request_ret);

    $data['item_list'] = $item_list;
    $data['accessories_list'] = $accessories_list;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array();
        $email_rec = get_notification_emails($_dept, $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-return-alert.msg', $data);
            else
                $message = compose_message('messages/loan-return-alert.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Loan Return Reminder (LR'. $data['id_loan'] . ') for '.$data['requester'];
            if ($data['same_day'] > 0)
                $subject .= ' - Late';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-return-alert.sms', $data);
        else
            $message = compose_message('messages/loan-return-alert.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_loan_return_alert(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }    
}

function send_loan_issued_alert($data){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    
    $_dept = $data['id_department'];    
    $items = get_request_items($data['id_loan']);
    $acces = get_accessories_by_loan($data['id_loan']);
    $item_list = null;
    $accessories_list = null;
    foreach ($items as $rec)
        $item_list .= "\t - $rec[asset_no] ($rec[serial_no]) \r\n";
    foreach ($acces as $idacc => $accname)
        $accessories_list .= "\t - $accname\r\n";
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    $data['item_list'] = $item_list;
    $data['accessories_list'] = $accessories_list;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($_dept, $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-request-issued.msg', $data);
            else
                $message = compose_message('messages/loan-request-issued.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Loan Issued-Out Notification(LR'. $data['id_loan'] . ')';
            if ($data['same_day'] > 0)
                $subject .= ' - Late';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-request-issued.sms', $data);
        else
            $message = compose_message('messages/loan-request-issued.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_loan_issued_alert(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }    
}

function send_loan_item_ready($data){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    
    $_dept = $data['id_department'];    
    $request_no = $transaction_prefix.$data['id_loan'];
    $figi_url = FIGI_URL;
    $data['request_no'] = $request_no;
    $data['figi_url'] = $figi_url;
    //$data['loan_date'] = $data['start_loan'] . ' to ' . $data['end_loan'];
	$request_out = get_request_out($data['id_loan']);
	$data = array_merge($data, $request_out);
        
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($_dept, $data['id_category'], 'loan');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            if ($data['long_term'] > 0)
                $message = compose_message('messages/long-term-loan-item-ready.msg', $data);
            else
                $message = compose_message('messages/loan-item-ready.msg', $data);
            $to = $data['user_email'];
            $cc = implode(',', $emails);
            $subject = 'Loan Request ('. $request_no . ') - Ready for Collection ';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        if ($data['long_term'] > 0)
            $message = compose_message('messages/long-term-loan-item-ready.sms', $data);
        else
            $message = compose_message('messages/loan-item-ready.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
        process_notification($id_msg);
        writelog('send_loan_item_ready(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }    

}

function get_accessories_by_loan($id){
	$result = array();
	$query = "SELECT lia.id_accessory, accessory_name FROM loan_item_accessories lia 
				LEFT JOIN accessories acc ON acc.id_accessory = lia.id_accessory
				WHERE id_loan = $id";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0)
		while($rec = mysql_fetch_row($rs))
			$result[$rec[0]] = $rec[1];
	return $result;
}

/** +here */
function export_request_status($status){
	if(is_array($status)){
		$statuses = array_filter($status);
		if(!empty($statuses)){
			$status=$statuses;
			foreach($status as $key => $val){
				if($key >0) $addname .= '&' . strtolower($val);
				else $addname .= strtolower($val);
			}
		}
	} 
	else{
		$addname = strtolower($status);
		$status = array($status);
	}

    $crlf = "\r\n";
	ob_clean();
    ini_set('max_execution_time', 60);
    $today = date('dMY');
	//$fname = 'figi_'.strtolower($status)."_loan-$today.csv";
	$fname = 'figi_'.$addname."_loan-$today.csv";
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=$fname");
    header("Pragma: no-cache");
    header("Expires: 0");
	$transaction_prefix = TRX_PREFIX_LOAN;

    $total = count_request_by_status($status);
    $rs = query_request_by_status($status, 0, $total);
	/*
	while($rec=mysql_fetch_assoc($rs)){
		$result[]=$rec;
	}
	*/
	//print_r($result);
	
	if ($rs) // && (mysql_num_rows($rs)>0)
	  foreach($status as $key =>$statusname){
		$available = count_request_by_status($statusname);
		switch(strtolower($statusname)){
		case 'pending':
			if($available>0){
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[remark]$crlf";
				}
			}
			break;
		case 'rejected':
			if($available>0){
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[remark]$crlf";
				}
			}
			break;
		case 'returned':
			if($available>0){
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name]$crlf";
				}
			}
			break;
		case 'loaned':
			if($available>0){
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[issue_remark]$crlf";
				}
			}
			break;
		case 'partial_in':
			if($available>0){
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[issue_remark]$crlf";
				}
			}
			break;
		case 'completed':
			if($available>0){
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name]$crlf";
				}
			}
			break;
		}
	  }
    ob_end_flush();
    exit;

}

/** +here */
//version 2nd
function export_request_status_v2($status){
	if(is_array($status)){
		$statuses = array_filter($status);
		if(!empty($statuses)){
			$status=$statuses;
			foreach($status as $key => $val){
				if($key >0) $addname .= '&' . strtolower($val);
				else $addname .= strtolower($val);
			}
		}
	} 
	else{
		$addname = strtolower($status);
		$status = array($status);
	}

    $crlf = "\r\n";
	ob_clean();
    ini_set('max_execution_time', 60);
    $today = date('dMY');
	//$fname = 'figi_'.strtolower($status)."_loan-$today.csv";
	$fname = 'figi_'.$addname."_loan-$today.csv";
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=$fname");
    header("Pragma: no-cache");
    header("Expires: 0");
	$transaction_prefix = TRX_PREFIX_LOAN;

    //$total = count_request_by_status($status);
    //$rs = query_request_by_status($status, 0, $total);

	/*
	while($rec=mysql_fetch_assoc($rs)){
		$result[]=$rec;
	}
	*/

	//if ($rs)
	  foreach($status as $key =>$statusname){
		$total = count_request_by_status($statusname);
		$rs = query_request_by_status($statusname, 0, $total);
		switch(strtolower($statusname)){
		case 'pending':
			if($total>0){
				echo 'PENDING:'.$crlf;
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[remark]$crlf";
				}
				echo $crlf;
			}
			break;
		case 'rejected':
			if($total>0){
				echo 'REJECTED:'.$crlf;
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[remark]$crlf";
				}
				echo $crlf;
			}
			break;
		case 'returned':
			if($total>0){
				echo 'RETURNED:'.$crlf;
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name]$crlf";
				}
				echo $crlf;
			}
			break;
		case 'loaned':
			if($total>0){
				echo 'LOANED:'.$crlf;
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[issue_remark]$crlf";
				}
				echo $crlf;
			}
			break;
		case 'partial_in':
			if($total>0){
				echo 'PARTIAL IN:'.$crlf;
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category,Quantity,Remarks'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name],$rec[quantity],$rec[issue_remark]$crlf";
				}
				echo $crlf;
			}
			break;
		case 'completed':
			if($total>0){
				echo 'COMPLETED:'.$crlf;
				echo 'No,Date of Request,Requestor,Loan Start Date,Loan End Date,Category'.$crlf;
				while ($rec = mysql_fetch_assoc($rs)){
					echo "$transaction_prefix$rec[id_loan],$rec[request_date],$rec[requester],$rec[start_loan],";
					echo "$rec[end_loan],$rec[category_name]$crlf";
				}
				echo $crlf;
			}
			break;
		}
	  }
    ob_end_flush();
    exit;

}

function save_attachment($id = 0){
	/*
    if (!empty($data['deleted_images'])){
        $query = 'DELETE FROM loan_lost_attachment WHERE id_attachment IN (' . $data['deleted_images'] . ')';
        mysql_query($query);
    }
    if (isset($_FILES['fimage']) && count($_FILES['fimage']) > 0){
        for ($i = 0; $i < count($_FILES['fimage']['name']); $i++){
            $filesize = $_FILES['fimage']['size'][$i];
            $filename = $_FILES['fimage']['name'][$i];
            $filetemp = $_FILES['fimage']['tmp_name'][$i];
            $errorcode = $_FILES['fimage']['error'][$i];

            if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                $data = base64_encode(file_get_contents($filetemp));
                $filethumb = resize($filetemp, THUMB_WIDTH, THUMB_HEIGHT, tempnam('/tmp', 'thumb'));
                $thumbnail = base64_encode(file_get_contents($filethumb));
                $query  = "INSERT INTO item_image(id_item,  filename, data, thumbnail) ";
                $query .= "VALUES('$id', '$filename', '$data', '$thumbnail')";
                mysql_query($query);
                //echo mysql_error().$query;
            }
        }
    }
	*/
    // manage attachment
    if (!empty($data['deleted_attachments'])){
        $query = 'DELETE FROM loan_lost_attachment WHERE id_attachment IN (' . $data['deleted_attachments'] . ')';
        mysql_query($query);
    }

    if (isset($_FILES['fattachment']) && count($_FILES['fattachment']) > 0){
        for ($i = 0; $i < count($_FILES['fattachment']['name']); $i++){
            $filesize = $_FILES['fattachment']['size'][$i];
            $filename = $_FILES['fattachment']['name'][$i];
            $filetemp = $_FILES['fattachment']['tmp_name'][$i];
            $errorcode = $_FILES['fattachment']['error'][$i];

            if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                $data = base64_encode(file_get_contents($filetemp));
                $query  = "INSERT INTO loan_lost_attachment(id_loan, filename, data, description) ";
                $query .= "VALUES('$id', '$filename', '$data', '')";
                mysql_query($query);
                //echo mysql_error().$query;
            }
        }
    }
}

function get_lost_attachments($id){
    $result = array();
    if ($id > 0){
        $query = 'SELECT id_attachment, filename FROM loan_lost_attachment WHERE id_loan = '.$id;
        $rs = mysql_query($query);
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    }
    return $result;
}

function get_lost_report($id){
    $result = null;
    $query = 'SELECT * FROM loan_lost WHERE id_loan = '.$id;
    $rs = mysql_query($query);
    if ($rec = mysql_fetch_assoc($rs))
      $result = $rec;
    return $result;
}

/** +here **/
function check_and_action_location($locat_return=null){
	if($locat_return!=null){
		$locat_return_trim = strtolower(str_replace(' ', '', $locat_return));
		
		$exec = mysql_query("SELECT id_location,location_name FROM location");
		while ($r=mysql_fetch_array($exec)){
			$locat_name[$r['id_location']]=strtolower(str_replace(' ', '', $r['location_name']));
		}

		if(in_array($locat_return_trim, $locat_name)){
			$id = array_search($locat_return_trim, $locat_name); //get key array
			$exec = mysql_query("SELECT * FROM location WHERE id_location='$id'");
			$r=mysql_fetch_array($exec);
			$data=$r;
		}
		else{
			$location = ucwords(strtolower($locat_return)); //Uppercase the first character of each word in a string
			$exec = mysql_query("INSERT INTO location(location_name, location_desc) VALUES('$location', '$location')");
			$id_location = mysql_insert_id();
			$data = array('id_location' => $id_location, 'location_name' => $location);
		}
	}
	return $data;
}

/** +here **/
function update_quantity_request($id_loan, $qty_old, $qty_update){
	if(!empty($qty_update) && $qty_update!=null && $qty_update !=0){
		if($qty_update < $qty_old){
			mysql_query("UPDATE loan_request SET quantity=$qty_update WHERE id_loan=$id_loan");
		}
		elseif($qty_update > $qty_old){
			mysql_query("UPDATE loan_request SET quantity=$qty_update WHERE id_loan=$id_loan");
		}
		return true;
	}
	else
		return false;
}

/** +here **/
function display_request($request, $forprint = false){
    global $transaction_prefix,$_act,$count_returned_partial,$status_loan_request;
	$rest = $request['quantity'] - $count_returned_partial;
	if($_act=='return'){
		$theader='<th align="left"><label style="display:inline-block;width:30%;">Loaned</label><label style="display:inline-block;width:30%;">Returned</label><label for="return_now" style="display:inline-block;">Return Now</label></th>';
		$trow ="<td align='left'><input type='hidden' name='qty_loaned' value='$request[quantity]' /><label style='display:inline-block;width:30%;'>$request[quantity]</label>
				<label style='display:inline-block;width:30%;'>$count_returned_partial</label>
				<label style='display:inline-block;'><input style='width:70px;' type='number' min='0' id='return_now' name='return_now'></label></td>";
	}
	elseif($status_loan_request==PARTIAL_IN && ($_act=='view_issue' || $_act=='view_return' || $_act=='print_issue')){
		$theader='<th align="left"><label style="display:inline-block;width:30%;">Loaned</label><label style="display:inline-block;width:30%;">Returned</label><label for="return_now" style="display:inline-block;">Rest Item</label></th>';
		$trow ="<td align='left'><input type='hidden' name='qty_loaned' value='$request[quantity]' /><label style='display:inline-block;width:30%;'>( $request[quantity] )</label>
				<label style='display:inline-block;width:30%;'>$count_returned_partial</label>
				<label style='display:inline-block;'>$rest</label></td>";
	}
	else{
		$theader="<td align='left'>$request[quantity]</td>";
		$trow ='<td></td>';
	}
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" colspan=4>Loan Request
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_request" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
      <tbody id="loan_request">
      <tr valign="top" align="left" class="alt">
        <td align="left" width="14%" >Request No.</td>
        <td align="left" width="30%">
            <?php 
            echo $transaction_prefix.$request['id_loan'];
            if ($request['long_term'] == 1)
                echo ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';
        ?>
        </td>
        <td align="left" width="17%">Request Date/Time</td>
        <td align="left" ><?php echo $request['request_date']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Requested By</td>
        <td align="left"><?php echo $request['requester']?></td>
        <td align="left">Loan Period</td>
        <td align="left"><?php echo $request['start_loan']?> - <?php echo $request['end_loan']?></td>
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">Category</td>
        <td align="left"><?php echo $request['category_name']?></td>
        <td align="left">Quantity</td>
		<?php echo $theader; ?>
      </tr>  
      <tr valign="top">  
        <td align="left">Purpose</td>
        <td align="left" colspan=2><?php echo $request['purpose']?></td>
		<?php echo $trow; ?>
		<!--<th align="left" colspan=2><label style="display:inline-block;width:100px;">Loaned</label><label style="display:inline-block;width:80px;">Rest</label><label style="display:inline-block;width:110px;">Returned Now</label></th>-->
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">Remark</td>
        <td align="left" colspan=3><?php echo $request['remark']?></td>
      </tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_request').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}
    
function display_issuance($issue, $forprint = false, $forreturn=false){
    global $accessories, $item_list;
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
      <tr valign="top" align="left">
        <th align="left" colspan=4>Loan-Out Details
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
      <tbody id="loan_issuance">
      <tr valign="top">  
        <td align="left" width="13%">Loan Out to</td>
        <td align="left" width="30%"><?php echo $issue['name']?></td>
        <td align="left" colspan=2><strong>Projected Date to return:</strong></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC &nbsp; </td>
        <td align="left"><?php echo $issue['nric']?></td>
        <td align="right" width="16%">Sign Out &nbsp; </td>
        <td align="left"><?php echo $issue['loan_date']?></td>    
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><?php echo $issue['contact_no']?></td>    
        <td align="right">To be Returned &nbsp; </td>
        <td align="left"><?php echo $issue['return_date']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Department &nbsp; </td>
        <td align="left"><?php echo $issue['department_name']?></td>    
        <td align="right">Accessories &nbsp; </td>
        <td align="left" rowspan=2><?php echo $accessories?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Location &nbsp; </td>
        <td align="left" colspan=2><?php echo $issue['location_name']?></td>    
      </tr>
      <tr valign="top" class="alt" align="left">
        <td align="left">Item List</td>
        <td align="left" colspan=4>
            <div id="returnitemlist"><?php echo $item_list?></div>
        </td>
      </tr>  
      </tbody>
    </table>
    <script>
    $('#btn_loan_issuance').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php

}

/** +here */
function display_location($value=''){
	echo "<table width='100%' cellpadding=2 cellspacing=1>
		 <tr valign='top' align='left'><th colspan=2>Location Returned</th></tr>
		 <tr class='alt'><td width='98px'>Location</td><td><input type='text' value='$value' style='width:200px;' id='location_returned_loan' name='location_returned_loan' autocomplete='off' /></td></tr>
		 </table>";
}

function display_issuance_process($process, $signs, $forprint = false){
    //print_r($process);
    
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th rowspan=5></th>
    <th width=200></th>
    <th width=200>Issued By</th>
    <th width=200>Loaned By
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance_process" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
<tbody id="loan_issuance_process">
<tr valign="top">
    <td></td>
    <td>Name</td>
    <td><?php echo $process['issued_by_name']?></td>
    <td><?php echo $process['loaned_by_name']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['issue_date']?></td>
    <td><?php echo $process['loan_date']?></td>
</tr>
<tr valign="top">
    <td></td>
    <td>Remarks</td>
    <td><?php echo $process['issue_remark']?></td>
    <td><?php echo $process['loan_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Signatures</td>
    <td><img src="<?php echo $signs['issue_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['loan_sign']?>" class="signature"></td>
</tr>
</tbody>
</table>
    <script>
    $('#btn_loan_issuance_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_return_process($process, $signs, $forprint = false){
//print_r($process);
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th rowspan=5>&nbsp;</th>
    <th width=200></th>
    <th width=200>Returned By</th>
    <th width=200>Received By
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_return_process" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
<tbody id="loan_return_process">
<tr valign="top">
    <td></td>
   <td>Name</td>
    <td><?php echo $process['returned_by_name']?></td>
    <td><?php echo $process['received_by_name']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
</tr>
<tr valign="top">
    <td></td>
    <td>Remarks</td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Signatures</td>
    <td><img src="<?php echo $signs['return_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['receive_sign']?>" class="signature"></td>
</tr>
</tbody>
</table>
    <script>
    $('#btn_loan_return_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_issuance_process_approval($process, $signs, $forprint = false){
    //print_r($process);
    
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200>Approved By</th>
    <th width=200>Issued By</th>
    <th width=200>Loaned By
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance_process" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
<tbody id="loan_issuance_process">
<tr valign="top">
    <td>Name</td>
    <td><?php echo $process['approved_by_name']?></td>
    <td><?php echo $process['issued_by_name']?></td>
    <td><?php echo $process['name']?></td>
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
    <td><img src="<?php echo $signs['approve_sign']?>" class='signature'></td>
    <td><img src="<?php echo $signs['issue_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['loan_sign']?>"  class="signature"></td>
</tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_issuance_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_return_process_approval($process, $signs, $showack = false, $ackform = false, $forprint = false){
//print_r($process);
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200>Returned By</th>
    <th width=200>Received By</th>
    <th width=200>
<?php
    if ($showack) echo 'Acknowledged By';
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_return_process" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
<tbody id="loan_return_process">
<tr valign="top">
    <td>Name</td>
    <td><?php echo $process['returned_by_name']?></td>
    <td><?php echo $process['received_by_name']?></td>
<?php
    if ($showack)
        echo '<td>'.FULLNAME.'</td>';
?>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
<?php
    if ($showack)
        echo '<td>'.date(DATE_FORMAT_PHP).'</td>';
?>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
<?php
    if ($showack)
        if ($ackform)
            echo '<td><textarea name="acknowledge_remark" cols=22 rows=3></textarea></td>';
        else
            echo "<td>$process[acknowledge_remark]</td>";
?>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td><img src="<?php echo $signs['return_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['receive_sign']?>" class="signature"></td>
<?php
    
    if ($showack){
        if (!$ackform)
            echo '<td><img src="'.$signs['acknowledge_sign'].'" class="signature"></td>';
        else
            echo <<<TD
    <td>
              <div id="container" style="width:201px">
                  <canvas id="imageView" height=80 width=200></canvas>
                  <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                  <a href="javascript:ResetSignature()" class="button clearsign" title="Clear signature space">X</a>
                  </div>
              </div>
    </td>
TD;
}
?>
</tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_return_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_rejection($process, $form = false, $forprint = false){

?>
<table width="100%" cellpadding=2 cellspacing=1 class="rejection">
<tr valign="top">
    <th colspan=3 align="left">Request Rejection
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_rejection" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
<tbody id="loan_rejection">
<tr valign="top">
    <td width="14%">Rejected By</td>
    <td><?php echo $process['rejected_by_name']?></td>
    <td width="31%" rowspan=3 >Signature:<br/>
<?php
        if (!$form)
            echo '<img src="'.$process['reject_sign'].'" class="signature">';
        else
            echo <<<TD
              <div id="container" style="width:201px">
                  <canvas id="imageView" height=80 width=200></canvas>
                  <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                  <a href="javascript:ResetSignature()" class="button clearsign" title="Clear signature space">X</a>
                  </div>
              </div>
              <br/>
              <div style="text-align: left">
                <a class="button" onclick="return unapprove_loan()" href="javascript:void(0)">Reject the Request</a>
              </div>
              <script type="text/javascript" src="./js/signature.js"></script>
TD;
?>
    </td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time of Rejection</td>
    <td><?php echo $process['reject_date']?></td>
</tr>
<tr valign="top">
    <td>Remark</td>
    <td>
<?php
        if ($form)
            echo '<textarea name="remark" cols=28 rows=4 style="height: 75px; width: 350px"></textarea>';
        else
            echo $process['reject_remark'];
?>
    </td>
</tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_rejection').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_approval($process, $signs, $form = false, $forprint = false){

?>
<table width="100%" cellpadding=2 cellspacing=1 class="approval">
<tr valign="top">
    <th colspan=4 align="left">Request Approval
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_approval" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
<tbody id="loan_approval">
<tr valign="top">
    <td width="14%">Approved By</td>
    <td width="30%"><?php echo $process['approved_by_name']?></td>
    <td width="31%" rowspan=3 >Signature:<br/>
<?php
        if (!$form)
            echo '<img src="'.$signs['approve_sign'].'" class="signature">';
        else
            echo <<<TD
              <div id="container" style="width:201px">
                  <canvas id="imageView" height=80 width=200></canvas>
                  <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                  <a href="javascript:ResetSignature()" class="button clearsign" title="Clear signature space">X</a>
                  </div>
              </div>
              <br/>
              <div style="text-align: left">
                <a class="button" onclick="return approve_loan()" href="javascript:void(0)">Approve the Request</a>
              </div>
              <script type="text/javascript" src="./js/signature.js"></script>
TD;
?>
    </td>
</tr>
<tr valign="top" class="alt">
    <td>Approval Date/Time</td>
    <td><?php echo $process['approval_date']?></td>
</tr>
<tr valign="top">
    <td>Remark</td>
    <td>
<?php
        if ($form)
            echo '<textarea name="remark" cols=28 rows=4 style="height: 75px; width: 350px"></textarea>';
        else
            echo $process['approval_remark'];
?>
    </td>
</tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_approval').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function build_returned_item_list($loaned_items, $returned_items, $nolink = false, $prolink = false){
    $item_list  = '<table width="100%" cellpadding=2 cellspacing=1 id="loanitemlist">';
    $item_list .= '<tr><th width=30>No</th><th>Asset No</th><th>Serial No</th><th width=140> Status</th></tr>';
    $no = 1;
    foreach ($loaned_items as $item){
        $id = $item['id_item'];
        $link = './?mod=item&act=view&id=' . $id;
        $asset_no = ($nolink) ? $item['asset_no'] : "<a href='$link'>$item[asset_no]</a>";
        $serial_no = ($nolink) ? $item['serial_no'] : "<a href='$link'>$item[serial_no]</a>";
        if (isset($returned_items[$id])) {
            $rec = $returned_items[$id];
            $process_link = null;
            if ($prolink){
                if ($rec['status'] == 'FAULTY'){
                    if ($rec['process'] == 'NONE')
                        $process_link = '<a href="./?mod=machrec&act=create&id='.$rec['id_item'].'">create machine record</a>';
                    else if ($rec['process'] == 'DONE')
                        $process_link = '<a href="./?mod=machrec&act=view&id='.$rec['referer'].'">view machine record</a>.';
                } else if ($rec['status'] == 'LOST'){
                    if ($rec['process'] == 'NONE')
                        $process_link = '<a href="./?mod=item&act=add&loan='.$rec['id_loan'].'&id='.$rec['id_item'].'">add document</a>';
                    else if ($rec['process'] == 'DONE')
                        $process_link = '<a href="./?mod=item&act=view&loan='.$rec['id_loan'].'&id='.$rec['referer'].'">view document</a>.';
                }
            }
            $item_list .= "<tr><td align='right'>$no.</td><td>$asset_no</td><td>$serial_no</td><td>$item[status_name]</td></tr>";
        } else
            $item_list .= "<tr><td align='right'>$no.</td><td>$asset_no</td><td>$serial_no</td><td>?</td></tr>";
        $no++;
    }
    $item_list .= "</table>\r\n";
    return $item_list;
}

/** +here */
function build_returned_partial_item_list($returned_items, $nolink = false, $prolink = false){
    $item_list  = '<table width="100%" cellpadding=2 cellspacing=1 id="loanitemlist">';
    $item_list .= '<tr><th width=30>No</th><th>Asset No</th><th>Serial No</th><th width=140> Status</th></tr>';
    $no = 1;
    foreach ($returned_items as $item){
        $id = $item['id_item'];
        $link = './?mod=item&act=view&id=' . $id;
        $asset_no = ($nolink) ? $item['asset_no'] : "<a href='$link'>$item[asset_no]</a>";
        $serial_no = ($nolink) ? $item['serial_no'] : "<a href='$link'>$item[serial_no]</a>";
        if (isset($returned_items[$id])) {
            $rec = $returned_items[$id];
            $process_link = null;
            if ($prolink){
                if ($rec['status'] == 'FAULTY'){
                    if ($rec['process'] == 'NONE')
                        $process_link = '<a href="./?mod=machrec&act=create&id='.$rec['id_item'].'">create machine record</a>';
                    else if ($rec['process'] == 'DONE')
                        $process_link = '<a href="./?mod=machrec&act=view&id='.$rec['referer'].'">view machine record</a>.';
                } else if ($rec['status'] == 'LOST'){
                    if ($rec['process'] == 'NONE')
                        $process_link = '<a href="./?mod=item&act=add&loan='.$rec['id_loan'].'&id='.$rec['id_item'].'">add document</a>';
                    else if ($rec['process'] == 'DONE')
                        $process_link = '<a href="./?mod=item&act=view&loan='.$rec['id_loan'].'&id='.$rec['referer'].'">view document</a>.';
                }
            }
            $item_list .= "<tr><td align='right'>$no.</td><td>$asset_no</td><td>$serial_no</td><td>$item[status]</td></tr>";
        } else
            $item_list .= "<tr><td align='right'>$no.</td><td>$asset_no</td><td>$serial_no</td><td>?</td></tr>";
        $no++;
    }
    $item_list .= "</table>\r\n";
    return $item_list;
}

function build_accessories_list($id_loan)
{
    $accessories = null;
    $accessories_list = get_accessories_by_loan($id_loan);
    if (!empty($accessories_list)){
        $accessories = '<ol style="margin:0;padding-left:15px;padding-top:0 ">';
        foreach($accessories_list as $idacc => $acc)
            $accessories .= '<li>'.$acc . '</li>';
        $accessories .= '</ol>';
    } else
        $accessories .= 'n/a';
    return $accessories;
}

function display_losing_report($process, $forprint = false){
    if (empty($process)) return null;
    $id_loan = $process['id_loan'];
    $attachment_list = null;
    $attachments = get_lost_attachments($id_loan);
    if (count($attachments) > 0){
      $attachment_list = '<ol class="attachments" style="padding: 0 0 0 10px; margin-top: 1px">';
      foreach ($attachments as $attachment){
          $href = './?mod=loan&act=get_lost_attachment&name=' .urlencode($attachment['filename']);
          //$attachment_list .= '<li id="att'.$attachment['id_attachment'].'"><a href="javascript:void(0)" onclick="delete_attacment('.$attachment['id_attachment'].')"><img src="images/delete.png"></a> <a href="'.$href.'" rel="lightbox" >';
          $attachment_list .= '<li id="att'.$attachment['id_attachment'].'"><a href="'.$href.'" rel="lightbox" >';
          $attachment_list .= $attachment['filename'].'</a></li>';
      }
      $attachment_list .= '</ol>';
    } else
        $attachment_list = '-- document is not available! --';
    if (!empty($attachment_list)){
        $attachment_list .= '<script type="text/javascript" src="./js/slimbox2.js"></script>';
        $attachment_list .= '<link rel="stylesheet" href="'.STYLE_PATH.'slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" />';
    }
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th colspan=4>Item Lost Report
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_losing_report" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
<tbody id="loan_losing_report">
<tr valign="top">
    <td>Reported By</td>
    <td width=200><?php echo $process['reported_by']?></td>
    <td width=200 align="right">Signature</td>
    <td width=200 rowspan=3 ><img src="<?php echo $process['report_sign']?>" class='signature'></td>
</tr>
<tr valign="top" class="alt">
    <td>Report Date/Time</td>
    <td><?php echo $process['report_date']?></td>
</tr>
<tr valign="top">
    <td>Description</td>
    <td><?php echo $process['report_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Attached Documents</td>
    <td colspan=3><?php echo $attachment_list?></td>
</tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_losing_report').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}


?>
