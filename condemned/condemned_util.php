<?php
$disposal_methods = array(
    1 => 'Sell as scrap',
    2 => 'Donation',
    3 => 'Auction');
    
// status : PENDING, APPROVED, LOANED, RETURNED
function count_condemned_by_status($status = ''){
    $result = 0;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT COUNT(ci.id_issue) 
				FROM condemned_issue ci 
                LEFT JOIN user ON user.id_user = ci.issued_by 
				WHERE issue_status = '$status' ";
    if (!SUPERADMIN && (USERGROUP!=GRPPRI) && (USERGROUP!=GRPDIR))
       $query .= " AND user.id_department = $dept ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_condemned_issue_by_status($status = 'PENDING', $start = 0, $limit = RECORD_PER_PAGE){
    $result = array();
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $dtf = "'%d-%b-%Y %H:%i'";    
    $query = "SELECT ci.*, date_format(issue_datetime, $dtf) as issue_datetime, 
                 issue_remark, issue_status, user.full_name as issued_by_name,
                 (SELECT COUNT(id_item) FROM condemned_item WHERE condemned_item.id_issue = ci.id_issue) quantity 
                 FROM condemned_issue ci 
                 LEFT JOIN user ON user.id_user = ci.issued_by 
                 WHERE ci.issue_status = '$status' ";
    if (!SUPERADMIN && (USERGROUP!=GRPPRI) && (USERGROUP!=GRPDIR))
        $query .= " AND user.id_department = $dept ";
	$query .= " ORDER BY ci.issue_datetime DESC LIMIT $start, $limit";
    //echo mysql_error().$query;
	$rs = mysql_query($query);
	$i = 0;
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;    
	return $result;
}

function get_condemned_issue($id = 0){
    $result = array();
    $dtf = "'%d-%b-%Y %H:%i'";    
    $query = "SELECT ci.*, date_format(issue_datetime, $dtf) as issue_datetime, 
                date_format(approval_datetime, $dtf) as approval_datetime, 
                date_format(condemn_datetime, $dtf) as condemn_datetime, 
                date_format(recommendation_datetime, $dtf) as recommendation_datetime, 
                date_format(recommendation2_datetime, $dtf) as recommendation2_datetime, 
                issue_remark, issue_status, user.full_name as issued_by_name, user.user_email, user.contact_no, user.id_department, 
                (SELECT COUNT(id_item) FROM condemned_item WHERE condemned_item.id_issue = ci.id_issue) quantity,
                (SELECT full_name FROM user u WHERE u.id_user = ci.approved_by) approved_by,  
                (SELECT full_name FROM user u WHERE u.id_user = ci.condemned_by) condemned_by, 
                (SELECT full_name FROM user u WHERE u.id_user = ci.recommended_by) recommended_by, 
                (SELECT full_name FROM user u WHERE u.id_user = ci.recommended2_by) recommended2_by 
                 FROM condemned_issue ci 
                 LEFT JOIN user ON user.id_user = ci.issued_by 
                 WHERE ci.id_issue = '$id' ";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_disposal_file($id){
    $result = array();
    if ($id > 0){
        $query = 'SELECT id_file, filename FROM disposal_file WHERE id_issue = '.$id;
        $rs = mysql_query($query);
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    }
    return $result;
}

function get_disposal_info($id = 0){
    $result = array();
    $dtf = "'%d-%b-%Y %H:%i'";    
    $query = "SELECT *
                 FROM disposal_info 
                 WHERE id_issue = '$id' ";
    $rs = mysql_query($query); 
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_condemned_issue_rejection($id = 0){
    $result = array();
    $format_date = '%d-%b-%Y %H:%i:%s';
    $query = "SELECT lr.*, full_name rejector, 
                date_format(reject_date, '$format_date') as reject_date 
                FROM condemned_reject lr 
                LEFT JOIN user u ON u.id_user = lr.rejected_by 
                WHERE id_issue = $id";
    $rs = mysql_query($query);
    //echo mysql_error();
    if ($rs)
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function send_submit_request_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['condemned'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_condemned_issue($id);
    if (count($data) == 0) return false;

    $issue_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'condemned');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/condemned-request-submit.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Condemnation Request ('. $issue_no . ') by ' . $data['requester'];
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'condemned', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'condemned');
        $mobiles = array_keys($mobile_rec);
		$check_numb_sms = check_numb_sms($data['contact_no']);
        if (!empty($data['contact_no']) && $check_numb_sms)
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/condemned-request-submit.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'condemned', 'sms');
        process_notification($id_msg);
        writelog('send_submit_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}


function send_condemn_recommendation_alert(){
    global $transaction_prefix, $configuration;
    $config = $configuration['condemned'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_condemned_recommendation();
    if (count($data) == 0) return false;
    $items = array();
    foreach($data as $id => $rec){
        $items[] = $rec['asset_no'];
    }
    $data['item_list'] = @implode(', ', $items);
    
    $issue_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'condemned');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/condemned-recommendation.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Recommended Item to be Condemned';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'condemned', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'condemned');
        $mobiles = array_keys($mobile_rec);
		$check_numb_sms = check_numb_sms($data['contact_no']);
        if (!empty($data['contact_no']) && $check_numb_sms)
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/condemned-recommendation.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'condemned', 'sms');
        process_notification($id_msg);
        writelog('send_condemn_recommendation_alert(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function send_condemn_notification_to_hod($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['condemned'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_condemned_issue($id);
    if (count($data) == 0) return false;
    $items = array();
    foreach($data as $id => $rec){
        $items[] = $rec['asset_no'];
    }
    $data['item_list'] = @implode(', ', $items);
    
    $issue_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        //$email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'condemned');
        $hod = get_hod($data['id_department']);
        $email_rec[] = array('email'=>$hod['user_email'], 'name'=>$hod['full_name']);
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/condemned-notification-to-hod.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Offline Condemend Documents has been uploaded';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'condemned', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'condemned');
        $mobiles = array_keys($mobile_rec);
		$check_numb_sms = check_numb_sms($data['contact_no']);
        if (!empty($data['contact_no']) && $check_numb_sms)
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/condemned--notification-to-hod.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'condemned', 'sms');
        process_notification($id_msg);
        writelog('send_condemn_notification_to_hod(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}


function send_approved_condemned_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['condemned'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_condemned_issue($id);
    if (count($data) == 0) return false;
    
    $issue_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    $users = get_user_list();  
    $data['approver'] = $users[$data['approved_by']];
	$data['issuer'] = $data['issued_by_name'];
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], 0, 'condemned');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0){
            $items = get_item_serial_by_condemned($data['id_issue']);
            $item_list = null;
            foreach ($items as $id_item => $serial_no)
                $item_list .= $serial_no . "\r\n";
            $data['condemned_item_list'] = $item_list;
            $message = compose_message('messages/condemned-issue-approved.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Condemned Request ('. $issue_no . ') by ' . $data['issuer'] . ' has been Approved';
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'condemned', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], 0, 'condemned');
        $mobiles = array_keys($mobile_rec);
		$check_numb_sms = check_numb_sms($data['contact_no']);
        if (!empty($data['contact_no']) && $check_numb_sms)
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/condemned-issue-approved.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'condemned', 'sms');
        process_notification($id_msg);
        writelog('send_approved_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function send_rejected_condemned_notification($id = 0){
    global $transaction_prefix, $configuration;
    $config = $configuration['condemned'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_condemned_issue($id);
    if (count($data) == 0) return false;
    
    $issue_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    $users = get_user_list();  
    $data['approver'] = $users[$data['approved_by']];
	$data['issuer'] = $data['issued_by_name'];
    
    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['user_email']);
        $email_rec = get_notification_emails($data['id_department'], 0, 'condemned');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0){
            $items = get_item_serial_by_condemned($data['id_issue']);
            $item_list = null;
            foreach ($items as $id_item => $serial_no)
                $item_list .= $serial_no . "\r\n";
            $data['condemned_item_list'] = $item_list;
            $message = compose_message('messages/condemned-issue-rejected.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Item Condemned Request ('. $issue_no . ') by ' . $data['issuer'] . ' has been Rejected';
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'condemned', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], 0, 'condemned');
        $mobiles = array_keys($mobile_rec);
		$check_numb_sms = check_numb_sms($data['contact_no']);
        if (!empty($data['contact_no']) && $check_numb_sms)
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/condemned-issue-rejected.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'condemned', 'sms');
        process_notification($id_msg);
        writelog('send_rejected_condemned_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }
}

function get_item_serial_by_condemned($id){
    $items = array();
    $query = "SELECT li.id_item, i.asset_no, i.serial_no 
                FROM condemned_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE id_issue = $id ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec_item = mysql_fetch_assoc($rs)){
            $items[$rec_item['id_item']] = "$rec_item[serial_no] ($rec_item[asset_no])";
        }
    }
    return $items;
}

function get_condemned_recommendation(){
    $items = array();
    $id_status = CONDEMNED;
    $query = "SELECT i.id_item, i.asset_no, i.serial_no 
                FROM item i 
                LEFT JOIN category c ON i.id_category = c.id_category 
                WHERE c.category_type = 'EQUIPMENT' AND id_status != $id_status AND 
                DATE_ADD(date_of_purchase, INTERVAL condemn_period YEAR) > CURDATE()";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec_item = mysql_fetch_assoc($rs)){
            $items[$rec_item['id_item']] = $rec;
        }
    }
    return $items;
}

function get_item_by_condemned($id){
    $items = array();
    $query = "SELECT li.id_item, i.asset_no, i.serial_no, cost, 
                date_format(date_of_purchase, '%d-%b-%Y') date_of_purchase, 
                b.brand_name, i.model_no, r.reason  
                FROM condemned_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                LEFT JOIN brand b ON b.id_brand = i.id_brand 
                LEFT JOIN condemned_reason r ON r.id_reason = li.id_reason 
                WHERE id_issue = $id ";
    $rs = mysql_query($query);
    //echo mysql_error();
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec_item = mysql_fetch_assoc($rs)){
            $items[$rec_item['id_item']] = $rec_item;
        }
    }
    return $items;
}

function get_item_exception_by_condemned($id){
    $items = array();
    $query = "SELECT li.id_item, i.asset_no, i.serial_no, cost, 
                date_format(date_of_purchase, '%d-%b-%Y') date_of_purchase, 
                b.brand_name, i.model_no  
                FROM condemned_item_exception li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                LEFT JOIN brand b ON b.id_brand = i.id_brand 
                WHERE id_issue = $id ";
    $rs = mysql_query($query);
    //echo mysql_error();
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec_item = mysql_fetch_assoc($rs)){
            $items[$rec_item['id_item']] = $rec_item;
        }
    }
    return $items;
}

function get_item_by_condemned_in_table($id){
    $items = get_item_by_condemned($id);
    $item_list =<<<TABLE
    <table width="100%" cellpadding=3 cellspacing=1 class="condemned_item_list">
        <tr>
            <th align="center" width=30>No</th>
            <th align="center">Asset No</th>
            <th align="center">Serial No</th>
            <th align="center" width=70>Date of Purchase</th>
            <th align="center" width=50>Purchase Price</th>
            <th align="center">Brand</th>
            <th align="center">Model No</th>
            <th align="center">Reason</th>
        </tr>
TABLE;
    $no = 1;
    if (!empty($items))
        foreach ($items as $id => $item){
            $item_list .= '<tr><td align="right">' . ($no++) . '.&nbsp;</td><td><a href="./?mod=item&act=view&id=' . $id;
            $item_list .= '">' . $item['asset_no'] . '</a></td><td>' . $item['serial_no'] . '</td><td align="center">';
            $item_list .= $item['date_of_purchase'] . '</td><td>' . $item['cost'] . '</td><td>';
            $item_list .= $item['brand_name'] . '</td><td>' . $item['model_no'] . '</td><td>' . $item['reason'] . '</td></tr>';
        }
    else
        $item_list .= '<tr><td colspan=8>Data is not available!</td></tr>';
    
    $item_list .= '</table>';
    return $item_list;
}

function get_item_exception_by_condemned_in_table($id, $editmode = false){
    $items = get_item_by_condemned($id);
    $exceptions = get_item_exception_by_condemned($id);
    if (!$editmode && empty($exceptions))
        return "There's no exceptions!";
    $item_list =<<<TABLE
    <table width="100%" cellpadding=3 cellspacing=1 class="condemned_item_list">
        <tr>
            <th width=25>No</th>
            <th >Asset No</th>
            <th >Serial No</th>
TABLE;
    if ($editmode)
        $item_list .= '<th width=80>Exception</th>';    
        
    $item_list .= '</tr>';
    $no = 1;
    
    if (!empty($items))
        foreach ($items as $id_item => $item){
            $checked = (!empty($exceptions[$id_item]));
            $item_list .= '<tr><td align="right">' . ($no++) . '.&nbsp;</td><td><a href="./?mod=item&act=view&id=' . $id;
            $item_list .= '">' . $item['asset_no'] . '</a></td><td>' . $item['serial_no'] . '</td>';
            if ($editmode)
                $item_list .= '<td align="center"><input type="checkbox" name="exceptions[]" value="' . $item['id_item'] . '" '.$checked.'></td>';
            $item_list .= '</tr>';
        }
    else
        $item_list .= '<tr><td colspan=5>Data is not available!</td></tr>';
    
    $item_list .= '</table>';
    return $item_list;
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
    case COMPLETED : $view_act = 'view_complete'; break;
    default: $view_act = 'view';
    }
    ob_clean();
    header('Location: ./?mod=condemned&sub=condemned&act='.$view_act.'&id=' . $id);
    ob_end_flush();
    exit;
}

function get_condemned_issue_items($id = 0){
    $result = array();
    $query = "SELECT li.id_item, i.asset_no, i.serial_no 
                FROM condemned_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE id_issue = $id";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        while ($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;
    return $result;
}

function get_condemned_signature($id = 0, $status = 'approval'){
    $result = null;
    $query = 'SELECT '.$status."_signature FROM condemned_signature WHERE id_issue = $id ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_condemned_attachmens($id = 0){
    $result = array();
    if ($id > 0){
        $query = 'SELECT * FROM condemned_attachment WHERE id_issue = ' . $id;
        $rs = mysql_query($query);
        //echo $query.mysql_error();
        if ($rs && mysql_num_rows($rs)>0){
            while ($rec = mysql_fetch_assoc($rs))
                $result[] = $rec;
        }
    }
    return $result;
}

function display_condemn_approval($request){
    $sign_approval = get_condemned_signature($request['id_issue'], 'approval');
    echo <<<APPROVAL
   <table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left">
      <th align="left" colspan=2>Condemn Approval</th>
      <th align="left" width=200></th>
    </tr>
    <tr align="left">
      <td align="left" width=130>Approved by</td>
      <td align="left">$request[approved_by]</td>
      <td rowspan=3><img class='signature' src="$sign_approval"></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Approval</td>
      <td align="left">$request[approval_datetime]</td>
      
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td align="left">$request[approval_remark]</td>    
    </tr>
  </table>
APPROVAL;
}

function display_condemn_verification($request){
    $sign_approval = get_condemned_signature($request['id_issue'], 'approval');
    echo <<<VERIFICATION
   <table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left">
      <th align="left" colspan=2>Condemn Verification</th>
      <th align="left" width=200></th>
    </tr>
    <tr align="left">
      <td align="left" width=130>Approved by</td>
      <td align="left">$request[approved_by]</td>
      <td rowspan=3><img class='signature' src="$sign_approval"></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Approval</td>
      <td align="left">$request[approval_datetime]</td>
      
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td align="left">$request[approval_remark]</td>    
    </tr>
  </table>
VERIFICATION;
}

function display_condemn_recommendation2($request){
    $sign_recommend2 = get_condemned_signature($request['id_issue'], 'recommendation2');
    $item_exception_list = get_item_exception_by_condemned_in_table($request['id_issue']);
    echo <<<RECOMMENDATION2
<table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left">
      <th align="left" colspan=2>Directors Recommendation</th>
      <th align="left" width=200></th>
    </tr>
    <tr align="left">
      <td align="left" width=130>Recommended by</td>
      <td align="left" >$request[recommended2_by]</td>
      <td rowspan=4 valign="bottom">Signature:<br><img class="signature" src="$sign_recommend2"></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Recommendation</td>
      <td align="left">$request[recommendation2_datetime]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td align="left">$request[recommendation2_remark]</td>    
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Item Exceptions</td>
      <td align="left">$item_exception_list</td>    
    </tr>
  </table>
RECOMMENDATION2;
}

function display_condemn_recommendation($request){
    $sign_recommend = get_condemned_signature($request['id_issue'], 'recommendation');
    //$item_exception_list = get_item_exception_by_condemned_in_table($request['id_issue']);
    echo <<<RECOMMENDATION
  <table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left">
      <th align="left" colspan=2>Head of Departments Recommendation</th>
      <th align="left" width=200></th>
    </tr>
    <tr align="left">
      <td align="left" width=130>Recommended by</td>
      <td align="left" >$request[recommended_by]</td>
      <td rowspan=3>Signature:<br><img class=signature src="$sign_recommend"></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Recommendation</td>
      <td align="left">$request[recommendation_datetime]</td>
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td align="left">$request[recommendation_remark]</td>    
    </tr>
  </table>
RECOMMENDATION;
}

function display_condemn_issue($request){
    $sign_recommend = get_condemned_signature($request['id_issue'], 'issue');
echo <<<ISSUE
<table cellpadding=3 cellspacing=1 class="condemnview" id="condemn_issue">
  <tr valign="top" align="left">
    <th align="left" colspan=2>Condemn Preparation</td>
    <th align="left" width=200></th>
  </tr>  
  <tr valign="top" align="left">
    <td align="left" width=130>Prepared By</td>
    <td align="left">$request[issued_by_name]</td>
    <td rowspan=3>Signature:<br><img class=signature src="$sign_recommend"></td>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Date/Time of Issuance</td>
    <td align="left">$request[issue_datetime]</td>
    
  </tr>  
  <tr valign="top" class="normal">  
    <td align="left">Remarks</td>
    <td align="left">$request[issue_remark]</td>    
  </tr>
  <tr valign="top" align="left">
        <th align="left" colspan=3>Item Asset / Serial No. to be condemned</td>
  </tr>  
  <tr valign="top" align="left">
        <td align="left" colspan=3>            
            $request[item_list]
        </td>
  </tr>  
</table>
ISSUE;
}

function display_condemn_rejection($request){
    $sign_rejection = get_condemned_signature($request['id_issue'], 'approval');
echo <<<REJECT
  <table cellpadding=3 cellspacing=1 class="condemnview rejected" >
    <tr align="left">
      <th align="left" width=130>Rejected By</th>
      <th align="left">$request[approved_by]</th>
      <th align="left" width=200>Signature</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time Rejection</td>
      <td align="left">$request[approval_datetime]</td>
      <td align="left" rowspan=2><img class=signature src="$sign_rejection"></td>
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td  align="left">$request[approval_remark]</td>    
    </tr>
  </table>
REJECT;
}

function display_condemn_condemnation($request, $disposal){
    global $disposal_methods;
    
    $sign_condemn = get_condemned_signature($request['id_issue'], 'condemn');
    $disposal_method = $disposal_methods[$disposal['disposal_method']];
echo <<<DISPOSAL
<table cellpadding=3 cellspacing=1 class="condemnview approval condemn"  width="100%">
<tr align="left">
  <th align="left" colspan=3>Condemnation</th>
</tr>
<tr align="left" class="alt">
  <td align="left" width=130>Disposal</td>
  <td align="left" colspan=2>
    <table class="disposal">
    <tr>
        <td>Method</td>
        <td class="data">: $disposal_method</td>
        <td>Date</td>
        <td class="data">: $disposal[disposal_date]</td>
    </tr>
    <tr>
        <td>Reference no.</td>
        <td class="data">: $disposal[disposal_reference]</td>
        <td>Cost</td>
        <td class="data">: $disposal[disposal_cost]</td>
    </tr>
    </table>
    </td>
</tr>
<tr align="left">
  <td align="left">Name of Vendor</td>
  <td align="left" colspan=2>$disposal[vendor_name]</td>
</tr>
<tr align="left" valign="top" class="alt">
  <td align="left">Address of Vendor</td>
  <td align="left" colspan=2>$disposal[vendor_address]</td>
</tr>
<tr align="left">
  <td align="left">Contact Person</td>
  <td align="left" colspan=2>
  Name: <div class="data">$disposal[contact_person]</div>
  Number: $disposal[contact_number]
  </td>
</tr>
<tr align="left" class="alt">
  <td align="left" width=130>Attachments</td>
  <td align="left" colspan=2>$disposal[attachment_list]</td>
</tr>
<tr align="left">
  <td align="left">Condemned by</td>
  <td align="left" colspan=2>$request[condemned_by]</td>
</tr>
<tr valign="top" class="alt">  
  <td align="left">Date/Time of Condemnation</td>
  <td align="left">$request[condemn_datetime]</td>
  <td align="left" width=200 rowspan=2>Signature: <br><img class="signature" src="$sign_condemn"></td>
</tr>
<tr valign="top"> 
  <td align="left">Remarks </td>
  <td align="left">$request[condemn_remark]</td>    
</tr>
</table>   
DISPOSAL;
}


function build_condemn_attachment_list($id = 0){
    $attachment_list = '-- attachment is not available --';
    $attachments = get_condemned_attachmens($id);
    if (count($attachments)>0){
        $attachment_list = '<ol class="attachments">';
        foreach($attachments as $rec){                
            $link = './?mod=condemned&act=get_attachment&id=' . $rec['id_attach'];
            $attachment_list .= "<li><a href='$link' target='condemn-attachment'>{$rec[filename]}</a></li>";
        }        
        $attachment_list .= '</ol>';
    }
    return $attachment_list;
}

function get_reason_list(){
    $result = array();
    $query = "SELECT * FROM  condemned_reason";
    $rs = mysql_query($query);
    if ($rs)
        while($rec = mysql_fetch_assoc($rs))
            $result[$rec['id_reason']] = $rec['reason'];
            
    return $result;
}

?>