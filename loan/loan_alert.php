<?php
$charlist = " \t\n\r\0\x0B".DIRECTORY_SEPARATOR;
$thisdir = rtrim(dirname(__FILE__), $charlist);
$updir = substr($thisdir, 0, strrpos($thisdir, DIRECTORY_SEPARATOR)+1);
define('BASE_PATH', $updir);
define('LOGFILE', BASE_PATH.'/alert.log');

function ngelog($msg){
	if (defined('LOGFILE') && LOGFILE!=''){
		$fp = fopen(LOGFILE, 'a+');
		fputs($fp, $msg."\r\n");
		fclose($fp);
	}
}

ngelog('loan alert start.....');

include BASE_PATH.'/util.php';
include BASE_PATH.'/common.php';
include BASE_PATH.'/loan/loan_util.php';

//ngelog('check if alert activated .....');
if ((LOAN_RETURN_ALERT == FALSE) || (!ENABLE_NOTIFICATION)){
	
	//ngelog('loan alert disabled.');
    die("notification disabled or non-periodic alert type\n");
}

//ngelog('check due return date for short term loan .....');
// check for return due date 
$rs = loan_return_due_date_query();
if ($rs && mysql_num_rows($rs) > 0){
	ngelog('found: ' . mysql_num_rows($rs) . ' records');
    while ($rec = mysql_fetch_assoc($rs)){
        //print_r($rec);
		//ngelog(implode('|',$rec));
        send_loan_return_alert($rec);
    }
} else
	ngelog('no record found');

ngelog('check due return date for long term loan .....');
// check for long term confirmation reminder
$rs = loan_return_due_date_long_term_confirm();
if ($rs && mysql_num_rows($rs) > 0){
	ngelog('found: ' . mysql_num_rows($rs) . ' records');
    while ($rec = mysql_fetch_assoc($rs)){
        //print_r($rec);
		//ngelog(implode('|',$rec));
        send_loan_return_alert($rec);
    }
} else
	ngelog('no record found');


?>
