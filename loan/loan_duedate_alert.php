<?php

date_default_timezone_set('Asia/Singapore');

$charlist = " \t\n\r\0\x0B".DIRECTORY_SEPARATOR;
$thisdir = rtrim(dirname(__FILE__), $charlist);
$updir = substr($thisdir, 0, strrpos($thisdir, DIRECTORY_SEPARATOR)+1);
define('BASE_PATH', $updir);
//define('LOGFILE', BASE_PATH.'/alert.log');

include BASE_PATH.'/common.php';
include BASE_PATH.'/loan/loan_util.php';

//error_log('loan return alert start.....');
if ((LOAN_RETURN_ALERT == FALSE) || (!ENABLE_NOTIFICATION)){
    error_log("notification disabled or non-periodic alert type\n");
	exit;
}

$hour = date('G');
$config = $configuration['loan'];
$match_hour = ($config['return_alert_hour']==$hour);

if (!($match_hour)) {
	//error_log("seem it's not the time to run loan return due date notification!");
	exit;
}
//error_log('check due return date for short term loan .....');
$rs = loan_due_date_query();
if ($rs && mysql_num_rows($rs) > 0){
	error_log('found: ' . mysql_num_rows($rs) . ' records');
    while ($rec = mysql_fetch_assoc($rs)){
       //error_log("$rec[id_loan] - $rec[diff_days]");
	   send_due_date_alert($rec['id_loan'], $rec['diff_days']);
		
    }
} 
//else error_log('no record found');

function loan_due_date_query()
{
	$lead_days = LOAN_RETURN_LEAD_DAYS;
    $dtf = '%d-%b-%Y %H:%i';
    $query  = "SELECT * FROM 
				( SELECT lr.id_loan,  TIMESTAMPDIFF(DAY, now(), return_date) AS diff_days
                FROM loan_request lr 
                LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan 
                WHERE status = 'LOANED' AND long_term != 1) AS dt 
				WHERE diff_days <= $lead_days ";
    $rs = mysql_query($query);
   	//error_log($query.mysql_error());
    return $rs;
}

function send_due_date_alert($id_loan, $diff_days)
{
    global $transaction_prefix, $configuration;
    $config = $configuration['loan'];
    
    if ($config['enable_notification'] != 'true') return false;
    $id = $id_loan;
	$data = get_request($id);
    $_dept = $data['id_department'];    
    $items = get_request_items($id);
    $returned_items = get_returned_items($id);
    
	$id = $data['id_loan'];
    $figi_url = FIGI_URL;
    $request_out = get_request_out($id);
    $request_process = get_request_process($id);
	$data = array_merge($data, $request_process, $request_out);  
    $request_ret = get_request_return($id);
	$data = array_merge($data, $request_ret);
    $data['request_no'] = $transaction_prefix.$id;
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
			//error_log("to: $to; cc: $cc; subject: $subject; $message");
            
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_notification_sms'] == 'true' && $diff_days==0){ // if sms notification is active and due date occured
        if (!empty($data['contact_no'])){
            $to = $data['contact_no'];
			$message = compose_message('messages/loan-alert-on-due-date.sms', $data);
			//error_log("to: $to; $message");
			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'loan', 'sms');
			process_notification($id_msg);
		}
    }
	
}
