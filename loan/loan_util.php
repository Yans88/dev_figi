<?php

// status : PENDING, APPROVED, LOANED, RETURNED
function get_request_item($_id){
	$query = "SELECT asset_no , serial_no, item.id_item idi FROM loan_item LEFT JOIN item ON item.id_item = loan_item.id_item WHERE id_loan = $_id";
	$rs = mysql_query($query);
	$data = array();
	while($rec = mysql_fetch_assoc($rs)){
		$data[] = $rec['asset_no'].'|'.$rec['serial_no'].'|'.$rec['idi'].'|0';
	}
	return $data;
}
function get_request_category($_id){
	$query = "SELECT lrc.id_category lid, category_name FROM loan_request_category lrc LEFT JOIN category ON category.id_category= lrc.id_category WHERE id_loan = $_id";
	$rs = mysql_query($query);
	$data = array();
	while($rec = mysql_fetch_assoc($rs)){
		$data[] = $rec;
	}
	return $data;
}
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
	//echo $query;
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}


function query_request_by_status($status = '', $start = 0, $limit = RECORD_PER_PAGE, $ordby = 'request_date', $orddir = 'ASC', $id_item = null, $_searchby=null, $student_loan=null){
    $dept = defined('USERDEPT') ? USERDEPT : 0;
	$dtfmt = '%d-%b-%Y %H:%i';
	/**
    $query  = "SELECT lr.id_loan, date_format(start_loan, '$dtfmt') as start_loan, lr.students_loan,
			 date_format(end_loan, '$dtfmt') as end_loan, request_date as rd,
             date_format(request_date, '$dtfmt') as request_date, without_approval, purpose, 
             user.full_name as requester, 
			 category_name,
			 quantity, remark, status, long_term, 
             approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
             return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark,
             lp.return_date as red,date_format(lp.return_date, '$dtfmt') as return_date,              
			 loan_remark, lo.quick_issue
             FROM loan_request lr 
             LEFT JOIN user ON requester = user.id_user 
			 LEFT JOIN loan_item li ON li.id_loan = lr.id_loan
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             LEFT JOIN loan_out lo ON lo.id_loan = lr.id_loan  
             WHERE category_type = 'EQUIPMENT' ";
			 **/
	$query  = "SELECT lr.id_loan, date_format(start_loan, '$dtfmt') as start_loan, 
			 date_format(end_loan, '$dtfmt') as end_loan, request_date as rd,
             date_format(request_date, '$dtfmt') as request_date, without_approval, purpose, 
             user.full_name as requester, 
			 category_name,
			 quantity, remark, status, long_term, 
             approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
             return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark,
             lp.return_date as red,date_format(lp.return_date, '$dtfmt') as return_date,              
			 loan_remark, lo.quick_issue
             FROM loan_request lr 
             LEFT JOIN user ON requester = user.id_user 
             LEFT JOIN category ON lr.id_category = category.id_category 
             LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
             LEFT JOIN loan_out lo ON lo.id_loan = lr.id_loan  
             WHERE category_type = 'EQUIPMENT' ";
	if(!empty($id_item)){
		if($_searchby == 'issued_to'){
			$query .= " AND user.nric = '$id_item' ";
		}else
			$query .= " AND li.id_item = $id_item ";			
	}
	
	if(!empty($student_loan)){
		$query .= " AND lr.students_loan = $student_loan ";	
	}
	
    if (!SUPERADMIN)
        $query .= " AND lr.id_department = $dept ";

	/** +here **/
	if ($status != '' && is_array($status)){
        $wheres = array();
		foreach($status as $key => $val_status){
            $wheres[] = " '$val_status' ";
		}
        if (count($wheres)>0)
            $query .=  ' AND status IN ('.implode(', ', $wheres).')';
            
	}
	elseif ($status != '' && !is_array($status))
		$query .= " AND status = '$status' ";

	$query .= " ORDER BY $ordby $orddir LIMIT $start, $limit";
	$rs = mysql_query($query);
	//error_log(mysql_error().$query);
	return $rs;
}


function get_request_by_status($status = '', $start = 0, $limit = RECORD_PER_PAGE, $ordby = 'request_date', $orddir = 'ASC', $id_item = null, $_searchby=null,$studentLoan = null){
    $result = array();
	if (in_array($ordby, array('request_date','loan_date','end_loan'))) $ordby = 'lr.' .$ordby;
	else if ($ordby == 'return_date') $ordby = 'lp.'.$ordby;
	$rs = query_request_by_status($status, $start, $limit, $ordby, $orddir, $id_item, $_searchby, $studentLoan);
	$i = 0;
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;    
	return $result;
}

function get_request($id = 0){
    $result = array();
    $dtf = "'%d-%b-%Y %H:%i'";
    $dtf1 = "'%d-%b-%Y'";    
    $query = "SELECT lr.id_loan, date_format(start_loan, $dtf) as start_loan, date_format(end_loan, $dtf) as end_loan, purpose, lr.students_loan,				
                 date_format(request_date, $dtf) as request_date, user.nric, contact_no, without_approval, lr.id_category, long_term,   
                 user_email, user.full_name as requester, category_name, quantity, remark, status, lr.id_department, user.id_user,
				 department_name
                 FROM loan_request lr 
                 LEFT JOIN user ON requester = user.id_user 
                 LEFT JOIN category ON lr.id_category = category.id_category 
                 LEFT JOIN department ON lr.id_department = department.id_department 
                 LEFT JOIN students ON students.nric = user.nric
                 LEFT JOIN student_info ON student_info.id_student = students.id_student
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

//12052015 by hansen for checklist detail in point 23
function get_checklist($id=0){
	$result = null;
	$query = "select checklist from loan_out where id_loan = '$id'";
	$res = mysql_query($query); 
    //echo mysql_error().$query;
    if ($res && (mysql_num_rows($res)>0)){
        $row = mysql_fetch_row($res);
		$result  = $row[0];
	}
    return $result;
}

function get_checklist_title($id){
	$result = array();	
	$query = "select * from loan_out_checklist where id_check in ($id)";
	$res = mysql_query($query); 
	error_log(mysql_error().$query);
	 if ($res) 
        while ($row = mysql_fetch_assoc($res)){
			$result[$row['id_check']] = $row;
			//error_log(serialize($row));
			}
    return $result;
}

function get_checklist_title_item($id){
	$result = array();	
	$query = "select * from loan_out_checklist_item where id_check in ($id) order by id_check asc";
	$res = mysql_query($query); 
    //error_log(mysql_error().$query);
	if ($res) 		
        while ($row = mysql_fetch_assoc($res)){
			$result[$row['id_check']][] = $row;
			//error_log(serialize($row));
			}
    return $result;
}
// end of 12052015 by hansen for checklist detail in point 23

function get_request_return($id = 0){
    $result = array();
    $format_date = "%d-%b-%Y %H:%i";
	/*
    $query = "SELECT full_name received_by_name, returned_by, 
 	            date_format(return_date, '$format_date') as return_date, return_remark,  
 	            date_format(receive_date, '$format_date') as receive_date, receive_remark  
				FROM loan_process lp 
				LEFT JOIN user u ON lp.received_by=u.id_user 
				WHERE id_loan = '$id' ";
     */
	 $query = "SELECT lr.*, returned_by AS returned_by_name, received_by AS received_by_name,
                return_date as return_date_ori, 
	 			date_format(return_date, '$format_date') as return_date,
	 			date_format(return_date, '$format_date') as receive_date 
				FROM loan_return lr WHERE id_loan = '$id'";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs) 
        while ($row = mysql_fetch_assoc($rs)){
			$result[] = $row;
			//error_log(serialize($row));
			}
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
                (SELECT full_name FROM user WHERE id_user = received_by) as received_by_name, 
                (SELECT returned_by FROM loan_return WHERE id_loan = $id AND return_date=lp.return_date) as returned_by_name 
                FROM loan_process lp 
                WHERE id_loan = $id";
    $rs = mysql_query($query);
	//echo mysql_error().$query;
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
	$studentLoan = $data['students_loan'];
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
    
	if(sms_loan || ($studentLoan > 0 && sms_student_loan)){
		if ($config['enable_notification_sms'] == 'true'){
			$message = null;
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
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
}


function send_approved_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
    if (count($data) == 0) return false;
    $studentLoan = $data['students_loan'];
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
    if(sms_loan || ($studentLoan > 0 && sms_student_loan)){
		if ($config['enable_notification_sms'] == 'true'){
			$message = null;
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
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
}

/** +p elbas */
function send_unapproved_request_notification($id = 0){
	//error_log('send_unapproved_request_notification()');
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
	$studentLoan = $data['students_loan'];
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
            $subject = 'Item Loan Request ('. $request_no . ') by ' . $data['requester'] . ' has been Rejected';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    if(sms_loan || ($studentLoan > 0 && sms_student_loan)){
		if ($config['enable_notification_sms'] == 'true'){
			$message = null;
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
				array_unshift($mobiles, $data['contact_no']);
			$to = implode(',', $mobiles);
			if ($data['long_term'] > 0)
				$message = compose_message('messages/long-term-loan-request-unapproved.sms', $data);
			else
				$message = compose_message('messages/loan-request-unapproved.sms', $data);
			//SendSMS(SMS_SENDER, $to, $message);
			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
			process_notification($id_msg);
			//writelog('send_uapproved_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
		}
	}    
}

function send_returned_item_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
	$studentLoan = $data['students_loan'];
    if (count($data) == 0) return false;
    
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    // get serial
    $items = get_request_items($id);
       
    $figi_home = FIGI_URL;
    $accessories = get_accessories_by_loan($id);
	$item_list = loan_item_list_as_csv($items, $accessories);
    $data['item_list'] = $item_list;
    $data['is_quick_loan'] = 'No';
	if (isset($request_out['quick_issue']) && ($request_out['quick_issue']==1)) 
		$data['is_quick_loan'] = 'Yes'; 
    
    $returned_items = get_returned_items($id);
    $data['returned_item_list'] = returned_item_list_as_csv($returned_items );
    $request_out = get_request_out($id);
    $request_process = get_request_process($id);
	//$data = array_merge($data, $request_process, $request_out);    
	$data += $request_process + $request_out; 
    $request_ret = get_request_return($id);
	$data += $request_ret;
	$data['loaned_by_name'] = $request_out['name'];
	
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
            $subject = 'Item Loan ('. $request_no . ') has been Returned by ' . $data['returned_by_name'];
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
	if(sms_loan || ($studentLoan > 0 && sms_student_loan)){
		if ($config['enable_notification_sms'] == 'true'){
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
				array_unshift($mobiles, $data['contact_no']);
			$to = implode(',', $mobiles);
			if ($data['long_term'] > 0)
				$message = compose_message('messages/long-term-loan-request-returned.sms', $data);
			else
				$message = compose_message('messages/loan-request-returned.sms', $data);
			//SendSMS(SMS_SENDER, $to, $message);
			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
			process_notification($id_msg);
		   // writelog('send_returned_item_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
		}
	}    
}

function send_loosing_item_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_request($id);
	$studentLoan = $data['students_loan'];
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
    
	if(sms_loan || ($studentLoan > 0 && sms_student_loan)){
		if ($config['enable_notification_sms'] == 'true'){
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
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
	else $subquery = '';
    $query = "SELECT li.id_item, i.asset_no, i.serial_no, status_name, category_name, brand_name, model_no, i.id_status, l.location_name, lo.id_location
                FROM loan_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                LEFT JOIN status s ON i.id_status = s.id_status 
                LEFT JOIN brand b ON i.id_brand = b.id_brand 
                LEFT JOIN category c ON i.id_category = c.id_category 	
				LEFT JOIN loan_out lo ON li.id_loan = lo.id_loan
				LEFT JOIN location l ON lo.id_location = l.id_location
                WHERE li.id_loan = $id  $subquery";
    $rs = mysql_query($query);
   //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}

function get_returned_items($id = 0){
    $result = array();
    $query = "SELECT li.*, i.asset_no, i.serial_no, li.status status_name, category_name, brand_name, model_no, i.id_status  
                FROM loan_return_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                LEFT JOIN brand b ON i.id_brand = b.id_brand 
                LEFT JOIN category c ON i.id_category = c.id_category 
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
    $id = $data['id_loan'];
    $_dept = $data['id_department'];    
    $items = get_request_items($id);
    $returned_items = get_returned_items($id);
    
	$id = $data['id_loan'];
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    $request_out = get_request_out($id);
    $request_process = get_request_process($id);
	$data = array_merge($data, $request_process, $request_out);  
    $request_ret = get_request_return($id);
	$data = array_merge($data, $request_ret);
	$data['loaned_by_name'] = $request_out['name'];

	$accessories = get_accessories_by_loan($id);
	$item_list = loan_item_list_as_csv($items, $accessories);
    $data['item_list'] = $item_list;
    $data['returned_item_list'] = returned_item_list_as_csv($returned_items );
    $data['is_quick_loan'] = 'No';
	if (isset($request_out['quick_issue']) && ($request_out['quick_issue']==1)) 
		$data['is_quick_loan'] = 'Yes'; 
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
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
			//error_log("to: $to; cc: $cc; subject: $subject; $message");
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    if(sms_loan){
		if ($config['enable_notification_sms'] == 'true'){
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
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
}

function send_loan_issued_alert($data){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    //error_log('send_loan_issued_alert - request: '.serialize($data));
    if ($config['enable_notification'] != 'true') return false;
    $id = $data['id_loan'];
	$_group = $data['id_group']; // group
    $_dept = $data['id_department'];    
    $items = get_request_items($id);
	
	$accessories = get_accessories_by_loan($id);
	$item_list = loan_item_list_as_csv($items, $accessories);
    $request_out = get_request_out($id);
    $request_process = get_request_process($id);
	$data = array_merge($data, $request_process, $request_out);  
	$data['loaned_by_name'] = $request_out['name'];

    //error_log('send_loan_issued_alert - request_out: '.serialize($request_out));
    //error_log('send_loan_issued_alert - request_process: '.serialize($request_process));
    $request_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
	$data['figi_url'] = $figi_url;
	$data['request_no'] = $request_no;
    $data['item_list'] = $item_list;
    $data['is_quick_loan'] = 'No';
    //error_log('send_loan_issued_alert - data merged: '.serialize($data));

	if (isset($request_out['quick_issue']) && ($request_out['quick_issue']==1)) {
		$data['is_quick_loan'] = 'Yes'; 
		$data['loan_remark'] = 'Quick Loan';
	}
    
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
            if (isset($data['same_day'] ) && $data['same_day'] > 0)
                $subject .= ' - Late';
			//error_log("to: $to; cc: $cc; subject: $subject; $message");
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
	if(sms_loan){
		 if ($config['enable_notification_sms'] == 'true'){
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
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
	
   
	
		if($_group == 15 && sms_student_loan) //if GROUP = STUDENT
		{
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['father_mobile_number']);
			if (!empty($data['father_mobile_number']) && $check_numb_sms)
				array_unshift($mobiles, $data['father_mobile_number']);
			$to = implode(',', $mobiles);
			
			$message = compose_message('messages/loan-request-notify-parents.sms', $data);
			
			//SendSMS(SMS_SENDER, $to, $message);
			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
			process_notification($id_msg);
			writelog('send_loan_issued_alert(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
		}
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
    
	if(sms_loan){
		 if ($config['enable_notification_sms'] == 'true'){
			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'loan');
			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)
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
}

function get_accessories_by_loan($id){
	$result = array();
	$query = "SELECT lia.id_item, lia.id_accessory, accessory_name FROM loan_item_accessories lia 
				LEFT JOIN accessories acc ON acc.id_accessory = lia.id_accessory
				WHERE id_loan = $id";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0)
		while($rec = mysql_fetch_row($rs))
			$result[$rec[0]][$rec[1]] = $rec[2];
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
function get_loan_request($id_user){
	$result = array();
	$format_date = '%d-%b-%Y %H:%i';
	$query = "SELECT lr.*,
				date_format(lr.request_date, '$format_date') as loan_date, 
				date_format(lr.start_loan, '$format_date') as start_date, 
				date_format(lr.end_loan, '$format_date') as end_date
				FROM loan_request lr
				WHERE requester = $id_user AND quantity > 0 AND status = 'LOANED'";
	$rs = mysql_query($query);
	if($rs && mysql_num_rows($rs)>0){
		while($rec = mysql_fetch_assoc($rs)){
			$result[] = $rec;
		}
		
		return $result;
	}
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
?>
    <table width="100%" class="itemlist loan issue" >
	<thead>
      <tr >
        <th class="left" colspan=4>Loan Request
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_request" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
	</thead>
      <tbody id="loan_request">
      <tr  class="alt">
        <td align="left" width=100 >Request No.</td>
        <td align="left" width=460>
            <?php 
            echo $transaction_prefix.$request['id_loan'];
            if ($request['long_term'] == 1)
                echo ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';
        ?>
        </td>
        <td align="left" width=140>Request Date/Time</td>
        <td align="left" width=240><?php echo $request['request_date']?></td>
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
        <td align="left"><?php echo $request['quantity']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Purpose</td>
        <td align="left" colspan=3><?php echo $request['purpose']?></td>
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">Remark</td>
        <td align="left" colspan=3><?php echo $request['remark']?></td>
      </tr>
      </tbody>
    </table>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_request').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}
    
function display_issuance($issue, $forprint = false, $forreturn=false){
    global $accessories, $item_list;
	$ql = null;
	$students_loan = $issue['students_loan'];
	$parent_info = $issue['parent_info'];
	if(empty($issue['parent_name'])){
		$issue['parent_name'] = 'NA ';
	}
	if(empty($parent_info['father_mobile_number'])){
		$parent_info['father_mobile_number'] = 'NA ';
	}
	if(empty($parent_info['father_email_address'])){
		$parent_info['father_email_address'] = 'NA ';
	}
	if ($issue['quick_issue']) $ql = ' [ Quick Issue ]';
?>
    <table width="100%" class="itemlist loan issue" >
	  <thead>
      <tr >
        <th align="left" colspan=8>Loan-Out Details &nbsp; 
<?php
	echo $ql;
	if (!$forprint){ 
?>
            <div class="foldtoggle"><a id="btn_loan_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
	  </thead>
      <tbody id="loan_issuance">
      <tr valign="top">  
        <td align="left" width=100>Loan Out to</td>
		<?php
			$width = 460;
			if($students_loan > 0){
				$width = 250;
			}
		?>
		
        <td align="left" width=<?php echo $width;?>><?php echo $issue['name']?></td>
		<?php
			if($students_loan > 0){
				echo '<td align="center" colspan=2 width=200><b>Parent Info</b></td>';
			}
		?>
        <td align="left" colspan=2><strong>Projected Date to return:</strong></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC &nbsp; </td>
        <td align="left"><?php echo $issue['nric']?></td>
		<?php
			$colspan = 4;
			if($students_loan > 0){
				echo '<td align="left"  width=95>Name</td> <td align="left">'.$issue['parent_name'].'</td>';
				$colspan = 0;
			}?>
        <td align="right" width=140>Sign Out &nbsp; </td>
        <td align="left"><?php echo $issue['loan_date']?></td>    
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><?php echo $issue['contact_no']?></td>    
		<?php
			if($students_loan > 0){
				echo '<td align="left" width=95>Email</td> <td align="left">'.$parent_info['father_email_address'].'</td>';
			}
		?>
        <td align="right">To be Returned &nbsp; </td>
        <td align="left"><?php echo $issue['return_date']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Department &nbsp; </td>
        <td align="left"><?php echo $issue['department_name']?></td>  
		<?php
			if($students_loan > 0){
				$col = 0;
				echo '<td align="left" width=95>Phone number</td> <td align="left" colspan="4">'.$parent_info['father_mobile_number'].'</td>';
			}
		?>		
	  	<td colspan=6></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Location &nbsp; </td>
        <td align="left" colspan=4><?php echo $issue['location_name']?></td>    
      </tr>
      <tr valign="top" class="alt" align="left">
        <td align="left" colspan=3>Item List</td>       
        <td align="right" colspan=2>No of Loaned Items&nbsp; </td>
        <td align="left"><?php echo $issue['total_loaned_items']?></td>
      </tr>      
      <tr valign="top" class="alt" align="left">
        <td align="left" colspan=6>
            <div id="loaneditemlist"><?php echo $item_list?></div>
        </td>
      </tr>  
      </tbody>
    </table>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_issuance').click(function (e){
        toggle_fold(this);
    });
    </script>

<?php
	} // for print,hide jquery/js
}

function display_checklist($issue, $forprint = false, $forreturn=false){
 global $accessories, $item_list;
?>
    <table width="100%" class="itemlist loan issue" >
	  <thead>
      <tr >
        <th >Loan-Out Checklist
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_checklist" rel="open" href="javascript:void(0) ">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
	  </thead>
      <tbody id="loan_checklist">
       <tr > <td >
	   <table width="100%" class="checklist grid" id="checklist">
		<?php		
		
		$checks = array();
		$rows = explode(',', $issue['checklist']);		
		foreach($rows as $row){			
			if (!empty($row)){
				@list($id_check, $checked) = explode('-',$row);				
				$checked = ucwords($checked);	
				$checks[$id_check] = $checked;	
			}
		}			

		$titles = get_checklist_items($issue['id_category']);
		$i = 0;
		$no = 1;
		foreach ($titles as $id_title => $title){
			if ($title['is_enabled']!=1) continue;
			$i++;
			$clss = $i % 2 == 0 ? 'normal' : 'alt';
			echo '<tr class="title '.$clss.'"><td width=20>'.$no.'</td>';
			echo '<td colspan=2>'.$title['title'].'</td>';
			if (!empty($title['items'])) echo "<td></td>";
			else echo "<td width=40 class='center'>$checks[$id_title]</td>";
			echo '</tr>';
			if (!empty($title['items'])) {
				$items = $title['items'];
				foreach($items as $id_check_item => $title_item){	
					if ($title_item['is_enabled']!=1) continue;
					$i++;
					$clss = $i % 2 == 0 ? 'normal' : 'alt';
					echo '<tr class="title '.$clss.'"><td></td>';
					echo '<td width=10></td><td>'.$title_item['title'].'</td>';
					echo "<td width=40  class='center'>$checks[$id_check_item]</td></tr>";
				}
			}
			$no++;
		}

		?>
		</td>
      </tr>  	  
	  </table>
		</td>
      </tr>  	  
      </tbody>
	  </table>
	<br>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_checklist').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}
//End of add 13052015 by hansen for point 23
/** +here */
function display_location($value=''){
	echo "<table width='100%' cellpadding=2 cellspacing=1>
		 <tr valign='top' align='left'><th colspan=2>Location Returned</th></tr>
		 <tr class='alt'><td width='98px'>Location</td><td><input type='text' value='$value' style='width:200px;' id='location_returned_loan' name='location_returned_loan' autocomplete='off' /></td></tr>
		 </table>";
}

function display_issuance_process($process, $signs, $forprint = false){
    //print_r($process);
	$studentLoan = $process['students_loan'];
   
?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist process" >
<thead>
<tr >
	<?php
	$th = '';
	if($studentLoan > 0){
		$th = '</th>
    <th width=206>Parent';
	}?>
    <th rowspan=5></th>
    <th width=200></th>
    <th width=206>Issued By</th>
	<th width=206>Loaned By
    
<?php 
if($studentLoan > 0){
	echo $th;
}
if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance_process" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
</thead>
<tbody id="loan_issuance_process">
<tr valign="top">
    <td></td>
    <td>Name</td>
    <td><?php echo $process['issued_by_name']?></td>
    <td><?php echo $process['loaned_by_name']?></td>
	<?php if($studentLoan > 0){
		echo '<td>'.$process['parent_name'].'</td>';
	}?>
	
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['issue_date']?></td>
    <td><?php echo $process['loan_date']?></td>
	<?php $date=date_create($process['parent_remark_date']);
		  $parent_date = date_format($date, 'd-M-Y H:i');?>
	<?php if($studentLoan > 0){
		echo '<td>'.$parent_date.'</td>';
	}?>
</tr>
<tr valign="top">
    <td></td>
    <td>Remarks</td>
    <td><?php echo $process['issue_remark']?></td>
    <td><?php echo $process['loan_remark']?></td>
	<?php if($studentLoan > 0){
		echo '<td>'.$process['parent_remark'].'</td>';
	}?>
</tr>
<?php if ($process['quick_issue']!=1){ ?>
<tr valign="top" class="alt">
    <td></td>
    <td>Signatures</td>
    <td><img src="<?php echo $signs['issue_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['loan_sign']?>" class="signature"></td>
    
	<?php if($studentLoan > 0){
		echo '<td><img src="'.$signs['parent_loan_sign'].'" class="signature"></td>';
	}?>
</tr>
<?php } ?>
</tbody>
</table>
<div class="clear space5-top"></div>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_issuance_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}

function display_return_process($returns, $signs, $forprint = false, $process){
    global $returned_item_list;
	if (empty($returns)) return;
?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist process" >
<thead>
<tr >
    <th >Loan Return</th>
    <th width=200></th>
    <th width=206>Returned By</th>
    <th width=206>Received By
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_return_process" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
</thead>
<tbody id="loan_return_process">
<?php 
foreach($returns as $rec){ 

?>

<tr valign="top">
    <td></td>
   <td>Name</td>
    <td><?php echo $rec['returned_by_name']?></td>
    <td><?php echo $rec['received_by_name']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Date/Time Signature</td>
    <td><?php echo $rec['return_date']?></td>
    <td><?php echo $rec['receive_date']?></td>
</tr>
<tr valign="top">
    <td></td>
    <td>Remarks</td>
    <td><?php echo $rec['return_remark']?></td>
    <td><?php echo $rec['receive_remark']?></td>
</tr>
<?php 

if ($process['quick_issue']==0){ 
?>
<tr valign="top" class="alt">
    <td></td>
    <td>Signatures</td>
    <td><img src="<?php echo $signs['return_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['receive_sign']?>" class="signature"></td>
</tr>
<?php 
	} // quick_issue 
?>
<tr valign="top" class="alt" align="left">
    <td align="left" colspan=4>
        <div id="returnitemlist"><?php 
        $the_list = get_items_per_return($rec['id_loan'], $rec['return_no']);
        //print_r($the_list);
        echo build_returned_item_list(array_values($the_list), array());
        ?></div>
    </td>
</tr>  
<tr valign="top" class="alt">
<td colspan=4 style="border-bottom: 1px solid #000; height: 5px;"></td>
</tr>

<?php
} // returns
?>
</tbody>
</table>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_return_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}

function display_issuance_process_approval($process, $signs, $forprint = false){
    //print_r($process);
    
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<thead>
<tr >
    <th>&nbsp;</th>
    <th width=200>Approved By</th>
    <th width=206>Issued By</th>
    <th width=206>Loaned By
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance_process" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
</thead>
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
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_issuance_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}

function display_return_process_approval($process, $signs, $showack = false, $ackform = false, $forprint = false){
//print_r($process);
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<thead>
<tr>
    <th>&nbsp;</th>
    <th width=200>Returned By</th>
    <th width=206>Received By</th>
    <th width=206>
<?php
    if ($showack) echo 'Acknowledged By';
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_return_process" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
</thead>
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
             <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
    </td>
TD;
}
?>
</tr>
      </tbody>
    </table>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_return_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}

function display_rejection($process, $form = false, $forprint = false){

?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist rejection">
<thead>
<tr >
    <th colspan=3 align="left">Request Rejection
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_rejection" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
</thead>
<tbody id="loan_rejection">
<tr valign="top">
    <td width=140>Rejected By</td>
    <td colspan=3><?php echo $process['rejected_by_name']?></td>
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
            echo '<textarea name="remark" cols=24 rows=4 style="height: 75px; width: 350px"></textarea>';
        else
            echo $process['reject_remark'];
?>
    </td>
</tr>
<tr valign="top" class="alt">
    <td style="">Signature</td>
	<td>
<?php
        if (!$form)
            echo '<img src="'.$process['reject_sign'].'" class="signature">';
        else
            echo <<<TD
        <div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
             <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
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
      </tbody>
	  <!--
	  <tr><td colspan=4 style="border-top: 1px solid #000">
              <div style="text-align: right; ">
                <a class="button" onclick="return unapprove_loan()" href="javascript:void(0)">Reject the Request</a>
              </div>
	  </td></tr>
	  -->
    </table>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_rejection').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}

function display_approval($process, $signs, $form = false, $forprint = false){

?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist approval">
<thead>
<tr>
    <th colspan=4 align="left">Request Approval
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_approval" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
</thead>
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
             <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
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
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_approval').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}

function build_returned_item_list($loaned_items, $returned_items, $nolink = false, $prolink = false, $accs = array()){
    $item_list  = '<table width="100%" cellpadding=2 cellspacing=1  class="itemlist grid returned">';
    $item_list .= '<tr><th width=30>No</th><th>Asset No</th><th>Serial No</th><th>Category</th><th>Brand</th><th>Model No</th><th width=140> Status</th></tr>';
    $no = 1;
    foreach ($loaned_items as $item){
        $id = $item['id_item'];
		$accessories = null;
		if (isset($accs[$id])) {
			$accessories = implode(', ', array_values($accs[$id]));
		}
	    $link = './?mod=item&act=view&id=' . $id;
        $asset_no = ($nolink) ? $item['asset_no'] : "<a href='$link'>$item[asset_no]</a>";
        $serial_no = ($nolink) ? $item['serial_no'] : "<a href='$link'>$item[serial_no]</a>";
		$cn = ($no % 2 == 0) ? 'alt' : '';
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
            $item_list .= "<tr class='returned_item $cn'><td align='right'>$no.</td><td>$asset_no</td><td>$serial_no</td><td>$item[category_name]</td>";
			$item_list .= "<td>$item[brand_name]</td><td>$item[model_no]</td><td>$item[status_name]</td></tr>";
        } else {
            $item_list .= "<tr class='$cn'><td align='right'>$no.</td><td>$asset_no</td><td>$serial_no</td><td>$item[category_name]</td>";
			$item_list .= "<td>$item[brand_name]</td><td>$item[model_no]</td><td>$item[status_name]</td></tr>";
		}
        $no++;
    }
    $item_list .= "</table>\r\n";
    return $item_list;
}

/** +here */
function build_returned_partial_item_list($returned_items, $nolink = false, $prolink = false){
    $item_list  = '<table width="100%" cellpadding=2 cellspacing=1 class="itemlist returned partial">';
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
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist lost process">
<thead>
<tr >
    <th colspan=4>Item Lost Report
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_losing_report" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
</thead>
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


function loan_item_list($items, $accs = array())
{
	$result =  '<table width="100%" class="itemlist grid loaned">';
	$result .= '<tr><th>No</th><th>Asset No</th><th>Serial No</th><th>Category</th><th>Brand</th><th>Model No</th><th>Accessories</th></tr>';
	$no = 1;
	foreach ($items as $item){
		$accessories = '-';
		if (isset($accs[$item['id_item']])) {
			$accessories	= implode(', ', array_values($accs[$item['id_item']]));
		}
		$cn = ($item['id_status'] == 2) ? 'onloan' : null; //2 - onloan 
		$cn .= ($no % 2 == 0) ? ' alt' : ' normal';
		$row = '<tr class="'.$cn.'"><td>'.($no++).'.</td><td><a href="./?mod=item&act=view&id='.$item['id_item'].'">'.$item['asset_no'].'</a></td>';
		$row .= '<td>'.$item['serial_no'].'</td><td>'.$item['category_name'].'</td><td>'.$item['brand_name'];
		$row .= '</td><td>'.$item['model_no'].'</td><td>'.$accessories.'</td></tr>';
		$result .= $row;
	}
	$result .= '<tr><td colspan=7><cite class="field-note">*) red color text denote items are not returned yet.</cite></td></tr>';
	$result .= '</table>';
	if (count($items)==0) $result = 'empty';
	return $result;
}

function count_returned_item($id_loan = 0)
{
	$result = 0;
        $query = "SELECT * FROM loan_return_item WHERE id_loan = $id_loan";
	$rs = mysql_query($query);
	if ($rs)
		$result = mysql_num_rows($rs);
	return $result;
}

function loan_item_list_as_csv($items, $accs = array())
{
	$data[] = array('No','Asset No','Serial No','Category','Brand','Model No','Accessories');
	$no = 1;
	foreach ($items as $item){
		$accessories = null;
		if (isset($accs[$item['id_item']])) {
			$accessories	= implode(', ', array_values($accs[$item['id_item']]));
		}
		$data[] = array($no++, $item['asset_no'],$item['serial_no'],$item['category_name'],$item['brand_name'],$item['model_no'],$accessories);
	}
	$result = convert_to_csv($data, "\t");
	return $result;
}

function returned_item_list_as_csv($items)
{
	$data[] = array('No','Asset No','Serial No','Category','Brand','Model No','Status');
	$no = 1;
	foreach ($items as $item){
		$data[] = array($no++, $item['asset_no'],$item['serial_no'],$item['category_name'],$item['brand_name'],$item['model_no'],$item['status_name']);
	}
	$result = convert_to_csv($data, "\t");
	return $result;
}

function update_status_returned_items($id_loan, $return_date, $items, $return_no, $status, $location, $defect = null)
{
    if (count($items)>0){
        $query = "UPDATE item SET status_update = '$return_date', status_defect = '$defect', 
                  id_status = '$status', issued_to = 1, issued_date = '$return_date', id_location = '$location'
                  WHERE id_item in (" . implode(',', $items) . ")";
        mysql_query($query);
	//	error_log(mysql_error().$query);
        $item_status = 0;
        switch($status){
            case AVAILABLE_FOR_LOAN: $item_status = 'AVAILABLE'; break;
            case STORAGE: $item_status = 'STORAGE'; break;
            case FAULTY: $item_status = 'FAULTY'; break;
            case LOST: $item_status = 'LOST'; break;
        }
        foreach($items as $id){
            $query = "INSERT INTO loan_return_item(id_loan,id_item,status,process,return_date)
                        VALUE ($id_loan, $id, '$item_status', 'NONE', '$return_no', '$location')";
            mysql_query($query);
            //error_log(mysql_error().$query);
        }
    }
}

function build_item_list_for_return($items, $returned = array())
{
    global $statuses;
	$location_list = get_location_list();
	if (count($location_list) == 0){
		$location_list[0] = '--- no location available! ---';
	}else{
		$location_list = array(0 => '* select location')+$location_list;
	}
	$statuses = array(0 => '* select status')+$statuses;
    $no = 1;
	$option_locations = build_combo('all_location',$location_list,null, null, 'class ="all_location" disabled');
	$option = build_combo("items_status[$id_item]", $statuses, isset($returned[$id_item])? 6:-1, null, ' class="all_statusopt" disabled');
    $item_list  = '<table width="100%" cellpadding=2 cellspacing=1 class="itemlist grid return">';
	$item_list .= '<tr><th></th><th></th><th></th><th></th><th></th><th>'.$option_locations.'</th>
				  <th><input type="checkbox" id="chk_all_location"></th><th width=140>'.$option.'</th><th><input type="checkbox" id="chk_all_status"></th></tr>';
    $item_list .= '<tr><th>No</th><th>Asset No</th><th>Serial No</th><th>Brand</th><th>Model No</th><th>Location</th><th></th><th width=140>Returned Item Status</th><th></th></tr>';
    foreach ($items as $item){
        $id_item =  $item['id_item'];
        $classname = ($no % 2 == 0) ? 'alt' : 'normal';
        if (isset($returned[$id_item])) {
continue;
            $classname .=' partial_in';
            $option = build_combo("item_status[$id_item]", $statuses, isset($returned[$id_item])? 6:-1, null, ' disabled');
        } else
        $option = build_combo("item_status[$id_item]", $statuses, isset($returned[$id_item])? 6:-1, null, ' class="statusopt my_stats_'.$id_item.'" disabled');
        
		$option_location = build_combo("item_location[$id_item]", $location_list, $item['id_location'], null, ' class="my_location my_loc_'.$id_item.'" disabled');
		
        $item_list .= "<tr id='row-$item[asset_no]' class='$classname sn-$item[serial_no]'><td align='right' width=30>$no.</td><td>$item[asset_no]</td><td>$item[serial_no]</td>";
        $item_list .= "<td>$item[brand_name]</td><td>$item[model_no]</td><td align='center'>$option_location</td>
		<td align='center'><input type='checkbox' id='chk_loc_$id_item' class='chk_my_loc'></td><td>$option</td><td align='center'>
		<input type='checkbox' id='chk_stat_$id_item' class='chk_my_status'></td></tr>";
        $no++;
    }
    $item_list .= "</table>\r\n";
    echo $item_list;
}


function get_items_per_return($id, $return_no)
{
    $result = array();
    $query = "SELECT li.*, i.asset_no, i.serial_no, li.status status_name, category_name, brand_name, model_no 
                FROM loan_return_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                LEFT JOIN brand b ON i.id_brand = b.id_brand 
                LEFT JOIN category c ON i.id_category = c.id_category 
                WHERE id_loan = $id AND return_no='$return_no'";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[$rec['id_item']] = $rec;
    return $result;
}

function getCategory($id_loan){
	$result = array();
	$query = "Select * from loan_request where id_loan=".$id_loan;
	//echo $query;
	$rs = mysql_query($query);	
	while ($rec = mysql_fetch_assoc($rs)){
            $result[] = $rec;
    }
    return $result;
}


// add for cheklist base on point 23 11/05/2015
function getCheklist($id_cat){
	$result = array();
	$query = "Select * from loan_out_checklist where id_category=".$id_cat;
	$rs = mysql_query($query);	
	while ($rec = mysql_fetch_assoc($rs)){
            $result[] = $rec;
    }
    return $result;
}


//add by hansen 18052015 for bug walk in
function get_id_item($id_loan){
	$result = array();
	$query = "SELECT id_item from loan_item WHERE id_loan = '$id_loan'";	
    $rs = mysql_query($query);
	//echo $query;
	//echo mysql_error().$query;
	while ($rec = mysql_fetch_assoc($rs)){
            $result[] = $rec;
    }	
    return $result;
}

function get_item_walkin($id_item){
	$result = array();
	$query = "SELECT asset_no, serial_no, id_item,item.id_brand, category_name, brand_name, model_no, loan_period, item.id_category
                FROM item 
                LEFT JOIN category ON category.id_category = item.id_category 
                LEFT JOIN brand ON brand.id_brand = item.id_brand 
                WHERE  id_item = '$id_item'";	
    $rs = mysql_query($query);
    
    while ($rec = mysql_fetch_assoc($rs)){
            $result[] = $rec;
    }
    return $result;
}
// End of bug in walk in

/*
 * get checklist (option) item for a category
 * returns array of items with inside the children
 */
function get_checklist_items($_cat = 0, $enabled_only = true)
{
	$items = array();
	$query = "SELECT * FROM loan_out_checklist WHERE id_category = '$_cat'";
	if ($enabled_only) $query  .= " AND is_enabled=1 ";
	$query .= " ORDER BY id_parent, id_check ";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)>0){
		while ($rec = mysql_fetch_assoc($rs)){
			if ($rec['id_parent']==0){ // root/parent
				$rec['items'] = array();
				$items[$rec['id_check']] = $rec; 
			} else { // child
				$items[$rec['id_parent']]['items'][$rec['id_check']] = $rec; 
			}
		}
	}
	return $items;
}

function get_specification_loan_request($asset_no, $nric){
	$query = "SELECT li.id_loan, li.id_item, i.asset_no, status, lr.requester, u.full_name, u.nric 
			FROM loan_item li 
			LEFT JOIN item i ON i.id_item = li.id_item 
			LEFT JOIN loan_request lr ON lr.id_loan = li.id_loan 
			LEFT JOIN category ON lr.id_category = category.id_category
			LEFT JOIN user u ON u.id_user = lr.requester 
			WHERE category.category_type = 'EQUIPMENT' AND status IN ( 'LOANED', 'PARTIAL_IN') AND i.asset_no = '".$asset_no."' AND u.nric='".$nric."'";
			
	$mysql_query = mysql_query($query) or die(mysql_error());
	$row = mysql_num_rows($mysql_query);
	$data = mysql_fetch_array($mysql_query);
	if($row > 0){
		return $data['id_loan'];
	} else {
		return 0;
	}
}


function insert_condition($id_loan,$admin_id, $return_date, $condition1=null, $condition2=null, $condition3=null, $reason=null){

	$condition = array($condition1, $condition2, $condition3);
	$x = count($condition);
	if($x > 0){
		
		
		foreach($condition as $value){
			if(!empty($value)){
			
				if($value == "Other With Remark Box"){
					$query = "INSERT INTO loan_check_condition_item (id_loan, report_by, report_dates, conditions, the_reason) ";
					$query .= " VALUES ($id_loan, $admin_id, '".$return_date."', '".$value."', '".$reason."')";
				} else {
					$query = "INSERT INTO loan_check_condition_item (id_loan, report_by, report_dates, conditions, the_reason) ";
					$query .= " VALUES ($id_loan, $admin_id, '".$return_date."', '".$value."', '')";

				}
				//mysql_query($query);
				error_log($query);
			}
		}
	}
	
	$full_name = get_user_by_id($admin_id);
	$email_asset_owner = get_all_asset_owner_email();
	foreach($email_asset_owner as $data){
		error_log("Email ===== ".$data);
		if($data > 0){
			
			$message = "
			Dear ".$full_name."<br /><br />
			
			";
			$no = 1;
			foreach($condition as $value){
			if(! empty($value)){
				if($value == "Other With Remark Box"){
					$message .= $no.". ".$value.", ".$reason."<br />";
				} else {
					$message .= $no.". ".$value."<br />";
				}
				$no++;
			  }
			}
			$message .="
			Returned By: ".$full_name."<br />
			Return Date/Time: ".$return_date."<br />
			
			
			Thank You.";
			
			$subject = "Item Loan (LR".$id_loan.") has been returned by ".$full_name;
			$to = array_shift($data);
			$cc = implode(',', $data);
			
			error_log("to: $to; cc: $cc; subject: $subject; $message");
			
			//$id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'emailHtml');
			//process_notification($id_msg);
		}
	}
}

function get_all_asset_owner_email(){
	$query = "SELECT user_email FROM user WHERE id_group=16";
	$mysql_query = mysql_query($query);
	while($data = mysql_fetch_array($mysql_query)){
		$array[] = $data;
		return $array;
	}
}

function get_user_by_id($id){
	$query = "SELECT * FROM user WHERE id_user=".$id;
	$mysql_query = mysql_query($query);
	$data =mysql_fetch_array($mysql_query);
	
	return $data['full_name'];
}

function check_draft($nric){
	$id_loan = 0;
	$query = "select id_loan from loan_out_as_draft where nric =".$nric;
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0){
		$result = mysql_fetch_assoc($rs);
		$id_loan = $result['id_loan'];
		
	}
	//echo mysql_error().$query;
	return $id_loan;
}


function check_status_item($asset){
	$id_status = '';
	$data = array();
	foreach($asset as $asset_no){
		$query = "select asset_no, category_name, brand_name, model_no, id_status, id_item from item 
				  LEFT JOIN category ON category.id_category = item.id_category 
                  LEFT JOIN brand ON brand.id_brand = item.id_brand where asset_no = '".$asset_no."'";
		$rs = mysql_query($query);		
		$rec = mysql_fetch_assoc($rs);
		$id_status[$asset_no] = $rec['id_status'];
		if($id_status[$asset_no] == 2){
			$data['id_item'][$asset_no] = $rec['id_item'];
			$data['asset_no'][$asset_no] = $rec['asset_no'];
			$data['category'][$asset_no] = $rec['category_name'];
			$data['brand'][$asset_no] = $rec['brand_name'];
			$data['model_no'][$asset_no] = $rec['model_no'];
		}
	}	
	return $data;
}

function get_parent_info($id){
	$data = '';
	//$id=17;
	$query = "select * from student_info where id_student=".$id;
	$mysql_query = mysql_query($query);	
	if(!empty($mysql_query)){
		$data =mysql_fetch_array($mysql_query);
	}	
	return $data;
}

function get_class_info($id){
	$data = '';
	//$id=17;
	$query = "select class from student_classes where id_student=".$id;
	$query .= " order by year DESC limit 1";
	
	$mysql_query = mysql_query($query);	
	if(!empty($mysql_query)){
		$data =mysql_fetch_array($mysql_query);
	}	
	return $data['class'];
}

function get_aup($id){
	$data = '';
	//$id=17;
	$query = "select * from loan_out_aup where id_loan=".$id;
	$mysql_query = mysql_query($query);	
	if(!empty($mysql_query)){
		$data =mysql_fetch_array($mysql_query);
	}	
	return $data;
}