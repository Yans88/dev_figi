<?php

date_default_timezone_set('Asia/Singapore');

$charlist = " \t\n\r\0\x0B".DIRECTORY_SEPARATOR;
$thisdir = rtrim(dirname(__FILE__), $charlist);
$updir = substr($thisdir, 0, strrpos($thisdir, DIRECTORY_SEPARATOR)+1);
define('BASE_PATH', $updir);
//define('LOGFILE', BASE_PATH.'/alert.log');
$date = date('H:i');

error_log('return_reminder_keyloan send.....'.$date);

include BASE_PATH.'/common.php';
include BASE_PATH.'/keyloan/util.php';
//global $configuration;
//$hour = date('G');
$config = $configuration['keyloan'];
//$match_hour = ($config['return_hours']==$hour);
$config_hours = $config['return_hours'];
$config_hour = $config_hours * 60;

if ($config['enable_sms_reminder'] != 'true' && $config['enable_email_reminder'] != 'true') exit;

$rs = return_reminder_query();
if ($rs && mysql_num_rows($rs) > 0){
	//error_log('found: ' . mysql_num_rows($rs) . ' records');
    while ($rec = mysql_fetch_assoc($rs)){
       //error_log("$rec[id_loan] - $rec[diff_days]");
	   if($rec['diff_hours'] >= $config_hour){
		   send_reminder_return($rec['id_loan']);
		   error_log('return_reminder_keyloan send.....');
	   }		
    }
} 


function return_reminder_query()
{
	$lead_days = LOAN_RETURN_LEAD_DAYS;
    $dtf = '%d-%b-%Y %H:%i';
    $query  = "SELECT * FROM ( SELECT kl.id_loan,  TIMESTAMPDIFF(MINUTE, loan_start, now()) AS diff_hours
                FROM key_loan kl 
                LEFT join key_loan_item kli on kli.id_loan = kl.id_loan
                LEFT JOIN key_item ki ON ki.id_item = kli.id_item 
                WHERE status = 'On Loan') AS dt ";
    $rs = mysql_query($query);
   	error_log($query.mysql_error());
   	//error_log('reminder return sql.....');
    return $rs;
}

function send_reminder_return($id = 0){
    global $configuration;
    $config = $configuration['keyloan'];
    
    if ($config['enable_sms_reminder'] != 'true' && $config['enable_email_reminder'] != 'true') return false;
	
    $data_key_loan = get_data_keyloan($id);   
    $figi_url = FIGI_URL;
    $id_key_loan = 'KLN'.$data_key_loan['id_loan'];
    // get serial
    $serial_items = get_serial_by_loan($id);       
    $figi_home = FIGI_URL;
    
	$item_list = keyloan_item_list_as_csv($serial_items);
	
    $data['item_list'] = $item_list;
    $data['borrower'] = $data_key_loan['full_name'];
	$data['loan_date'] = $data_key_loan['loan_start'];
	
    if ($config['enable_email_reminder'] == 'true'){
        $emails = $data_key_loan['user_email'];        
        if (!empty($emails)) {
            $message = compose_message('messages/key-loan-return-reminder.msg', $data);
            $to = $emails;
            $cc = null;
            $subject = 'Reminder Return of Key Loan ('. $id_key_loan . ')';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'key loan', 'email');
            process_notification($id_msg);
        }
    }
    
    if ($config['enable_sms_reminder'] == 'true'){        
        $mobiles = $data_key_loan['contact_no'];
		$check_numb_sms = check_numb_sms($mobiles);
        if (!empty($mobiles) && $check_numb_sms){
			$to = $mobiles;
			$message = compose_message('messages/key-loan-return-reminder.sms', $data);

			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'key loan', 'sms');
			process_notification($id_msg);
		}
    }
	error_log('reminder return send.....');
}

function keyloan_item_list_as_csv($items)
{
	$data[] = array('No','Serial No');
	$no = 1;
	
	foreach ($items as $item){		
		$data[] = array($no++, $item);		
	}
	$result = convert_to_csv($data, "\t");
	return $result;
}