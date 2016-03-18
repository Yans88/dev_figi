<?php

date_default_timezone_set('Asia/Singapore');

define('ALERT_LEADING_DAYS', 2);
define('ALERT_TRAILING_DAYS', 12);


$charlist = " \t\n\r\0\x0B".DIRECTORY_SEPARATOR;
$thisdir = rtrim(dirname(__FILE__), $charlist);
$updir = substr($thisdir, 0, strrpos($thisdir, DIRECTORY_SEPARATOR)+1);
define('BASE_PATH', $updir);
define('LOGFILE', BASE_PATH.'/alert.log');

include BASE_PATH.'/common.php';
include BASE_PATH.'/loan/loan_util.php';

function ngelog($msg){
	if (defined('LOGFILE') && LOGFILE!=''){
		$fp = fopen(LOGFILE, 'a+');
		fputs($fp, $msg."\r\n");
		fclose($fp);
	}
}

ngelog('loan alert start.....');

//ngelog('check if alert activated .....');
if ((LOAN_RETURN_ALERT == FALSE) || (!ENABLE_NOTIFICATION)){
	
	//ngelog('loan alert disabled.');
    die("notification disabled or non-periodic alert type\n");
}

//ngelog('check due return date for short term loan .....');
// check for return due date 
$rs = loan_due_date_query();
if ($rs && mysql_num_rows($rs) > 0){
	ngelog('found: ' . mysql_num_rows($rs) . ' records');
    while ($rec = mysql_fetch_assoc($rs)){
        send_due_date_alert($rec['id_loan'], $rec['diff_days']);
    }
} else
	ngelog('no record found');

function loan_due_date_query($dept = 0)
{
	$lead_days = ALERT_LEADING_DAYS;
	$tail_days = ALERT_TRAILING_DAYS;
    $dtf = '%d-%b-%Y %H:%i';
    $query  = "SELECT * FROM 
				( SELECT lr.id_loan,  TIMESTAMPDIFF(DAY, now(), return_date) AS diff_days
                FROM loan_request lr 
                LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan 
                WHERE status = 'LOANED' AND long_term != 1) AS dt 
				WHERE diff_days >= $lead_days OR abs(diff_days)<=$tail_days";
    $rs = mysql_query($query);
   	//echo $query.mysql_error()."\r\n";
    return $rs;
}

function send_due_date_alert($id_loan, $diff_days){
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $id = $id_loan;
	$data = get_request($id);
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
	$data['diff_days'] = $diff_days;

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
			if ($diff_days == 0) { // same day
				$msgfn = 'loan-alert-on-due-date.msg';
				$subject = 'Loan Return Reminder (LR'. $data['id_loan'] . ') for '.$data['requester']. ', today is the due date!';
			} else if ($diff_days > 0) { // lead/before day
				$msgfn = 'loan-alert-before-due-date.msg';
				$subject = 'Loan Due Date Return Reminder (LR'. $data['id_loan'] . ') for '.$data['requester'].', will meet due date in '.$diff_days.' day(s)!';
			} else { // trail/passed day
				$msgfn = 'loan-alert-after-due-date.msg';
				$subject = 'Loan Due Date Return Reminder (LR'. $data['id_loan'] . ') for '.$data['requester'].', has been reached due date!';
			}
            $message = compose_message('messages/'.$msgfn, $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
			error_log("to: $to; cc: $cc; subject: $subject; $message");
            
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    /*
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
	*/
}
