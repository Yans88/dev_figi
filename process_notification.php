<?php

$id = @$argv[1];
ini_set('display_errors', 'off');

if (empty($id)) 
    die('Error: missing notification id');
error_log('process_notification.php run...');
include 'common.php';
error_log('include commond.php is good');
$msg = get_notification_message($id);
error_log(serialize($msg));
if (is_array($msg)){
    if ($msg['msg_type'] == 'email')
		SendEmail($msg['msg_from'], $msg['msg_to'], $msg['msg_subject'], $msg['msg_content'], $msg['msg_cc']);
	else if ($msg['msg_type'] == 'emailHtml')
        SendEmailHtml($msg['msg_from'], $msg['msg_to'], $msg['msg_subject'], $msg['msg_content'], $msg['msg_cc']);
    else if ($msg['msg_type'] == 'sms')
        SendSMS($msg['msg_from'], $msg['msg_to'], $msg['msg_content']);

    $query = "UPDATE notification_message SET msg_status = 1, process_time = now() 
                WHERE id_notification = $id";
    mysql_query($query);
    error_log($query);
}

?>