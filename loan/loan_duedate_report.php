<?php

date_default_timezone_set('Asia/Singapore');

$charlist = " \t\n\r\0\x0B".DIRECTORY_SEPARATOR;
$thisdir = rtrim(dirname(__FILE__), $charlist);
$updir = substr($thisdir, 0, strrpos($thisdir, DIRECTORY_SEPARATOR)+1);
define('BASE_PATH', $updir);
//define('LOGFILE', BASE_PATH.'/alert.log');

include BASE_PATH.'/common.php';
include BASE_PATH.'/loan/loan_util.php';

error_log('loan due date report start.....');
if ((LOAN_RETURN_ALERT == FALSE) || (!ENABLE_NOTIFICATION)){
    error_log("notification disabled or non-periodic alert type\n");
	exit;
}

$hour = date('G');
$dow  = date('w');
$dom  = date('j');
$config = $configuration['loan'];
//error_log(serialize($config));
//error_log( "current : dow=$dow, dom=$dom, hour=$hour");
//check alert frequency
$exec = false;
$match_day = false;
$match_hour = false;
switch ($config['report_frequency_type']){
	case 'daily': $match_day = true; break;
	case 'weekly': $match_day = ($config['report_frequency_day']==$dow);
	case 'monthly': $match_day = ($config['report_frequency_day']==$dom);
}
$match_hour = ($config['report_frequency_hour']==$hour);
/*
if (!($match_hour && $match_day)) {
	error_log("seem it's not the time to run loan return due date reporting!");
	exit;
}
*/
$crlf = "\r\n";
//error_log('check due return date for short term loan .....');
// check for return due date 
$rs = loan_due_date_query();
$total_all = loan_due_date_query_for_all();
$data_total_all = mysql_fetch_array($total_all);

if ($rs && mysql_num_rows($rs) > 0){
	//error_log('found: ' . mysql_num_rows($rs) . ' records');
	$date_sent = date('d-F-Y H:i');
		
		
		$message =<<<MSG
Dear Administrator,

Following is summary of Onloan Status as of $date_sent

Total Loan Return Overdue: $data_total_all[total_requester] Users,  No. of items $data_total_all[total_quantity]
MSG;

while ($rec = mysql_fetch_assoc($rs)){
$id_loan = "<a href='http://dev5m3.figi.sg/?mod=loan&act=view_issue&id=".$rec['id_loan']."'>LR".$rec['id_loan']."</a>";
//$id_loan = "LR".$rec['id_loan'];
$message .= "
".$rec['full_name'].", Loan Id ".$id_loan." No. of items ".$rec['quantity']." Return Date ".$rec['return_date']."
";
}

$message .= <<<MSG
-sysadmin-
MSG;
	
        $subject = 'Loan Return Due Date Summary Report';
		$admin = get_admin($rec['id_department']);
		$to = $admin['user_email'];
		$from = $configuration['global']['system_email'];
$cc = $from;
		
        $id_msg = set_notification_message($from, $to, $subject, $message, $cc, 'loan', 'emailHtml');
        process_notification($id_msg);
    
		
}

//set_email_notification($rec['id_group'], $rec['id_department'], $rec['frequency'],$base_url,$from);
//else error_log('no record found');

// end of main
/*
function loan_due_date_query()
{
	$lead_days = LOAN_RETURN_LEAD_DAYS;
    $dtf = '%d-%b-%Y %H:%i';
    $query  = "
		SELECT lr.id_department, lr.id_loan,  requester, return_date, (SELECT count(quantity)) as quantity, SUM(TIMESTAMPDIFF(DAY,now(),return_date) < 0) as overdue, SUM(TIMESTAMPDIFF(DAY,now(),return_date) = 0) as due, SUM(TIMESTAMPDIFF(DAY,now(),return_date) > 0 AND TIMESTAMPDIFF(DAY,now(),return_date) < $lead_days) as predue, u.full_name
FROM loan_request lr  
LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan
LEFT JOIN user u ON lr.requester = u.id_user
WHERE status = 'LOANED' AND long_term != 1
GROUP BY requester";
    $rs = mysql_query($query);
   	//error_log($query.mysql_error());
    return $rs;
}
*/
function loan_due_date_query()
{
	$lead_days = LOAN_RETURN_LEAD_DAYS;
    $dtf = '%d-%b-%Y %H:%i';
    $query  = "
		SELECT lr.id_department, lr.id_loan,  requester, return_date, u.full_name, quantity
FROM loan_request lr  
LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan
LEFT JOIN user u ON lr.requester = u.id_user
WHERE status = 'LOANED' AND long_term != 1 AND (TIMESTAMPDIFF(DAY,now(),return_date) < 0)";
    $rs = mysql_query($query);
   	//error_log($query.mysql_error());
    return $rs;
}

function loan_due_date_query_for_all()
{
	$lead_days = LOAN_RETURN_LEAD_DAYS;
    $dtf = '%d-%b-%Y %H:%i';
    $query  = "
		SELECT SUM(a.quantity) as total_quantity, count(a.requester) as total_requester  FROM (

SELECT lr.id_department, lr.id_loan,  requester, return_date, u.full_name, quantity
FROM loan_request lr  
LEFT JOIN loan_out lo ON lr.id_loan = lo.id_loan
LEFT JOIN user u ON lr.requester = u.id_user
WHERE status = 'LOANED' AND long_term != 1 AND (TIMESTAMPDIFF(DAY,now(),return_date) < 0 )
) as a";
    $rs = mysql_query($query);
   	//error_log($query.mysql_error());
    return $rs;
}

