<?php
function get_payment($id, $use_invoice_no = false)
{
    $result = null;
	$dtf = '%d-%b-%Y';
	$query  = "SELECT *, date_format(date_of_purchase, '$dtf') date_of_purchase, 
				date_format(first_date_of_payment, '$dtf') first_date_of_payment,
				date_format(next_date_of_payment, '$dtf') next_date_of_payment,
				date_format(last_date_of_payment, '$dtf') last_date_of_payment 
				FROM payment  ";
	if ($use_invoice_no)
		$query .= " WHERE invoice_no = '$id' ";
	else 
		$query .= " WHERE id_payment = '$id' ";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	if ($rs && (mysql_num_rows($rs)>0))
		$result = mysql_fetch_assoc($rs);	
    return  $result;
}

function get_items_from_invoice($invoice_no)
{
    $items = array();
	$query = "SELECT invoice, date_format(date_of_purchase, '%d-%b-%Y') as date_of_purchase, serial_no, id_item, cost, asset_no     
			   FROM item 
			   WHERE invoice = '$invoice_no' ";
	$rs = mysql_query($query);
    if ($rs)
		while ($rec = mysql_fetch_assoc($rs)){
			$items[] = $rec['serial_no'];
        }
    return $items;
}

function send_payment_notification($id)
{
    global $transaction_prefix, $configuration;
    $config = $configuration['payment'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_payment($id);
    if (count($data) == 0) return false;
    
    $invoice_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['emails']);
        $email_rec = get_notification_emails($data['id_department'], 0, 'payment');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/payment-notification.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Payment Info ('. $invoice_no . ')';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'payment', 'email');
            process_notification($id_msg);        
        }
    }
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'payment');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/payment-notification.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'payment', 'sms');
        process_notification($id_msg);
        writelog('send_fault_report_completed_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }

}

function get_department_by_invoice($invoice_no = '')
{
    $result = 0;
    $query = "SELECT id_department 
                FROM item 
                LEFT JOIN category cat ON cat.id_category = item.id_category 
                WHERE item.invoice = '$invoice_no' ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function send_payment_alert($data)
{
    global $transaction_prefix, $configuration;
    $config = $configuration['payment'];
    
    if ($config['enable_notification'] != 'true') return false;
    $data = get_payment($id);
    if (count($data) == 0) return false;
    
    $invoice_no = $transaction_prefix.$id;
    $figi_url = FIGI_URL;

    if ($config['enable_notification_email'] == 'true'){
        $emails = array($data['emails']);
        $email_rec = get_notification_emails($data['id_department'], 0, 'payment');
        foreach ($email_rec as $rec)
            $emails[] = $rec['email'];
        if (count($emails) > 0) {
            $message = compose_message('messages/payment-schedule-alert.msg', $data);
            $to = array_shift($emails);
            $cc = implode(',', $emails);
            $subject = 'Payment Alert ('. $invoice_no . ')';
            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);
            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'payment', 'email');
            process_notification($id_msg);        
        }
    }
    if ($config['enable_notification_sms'] == 'true'){
        $message = null;
        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'payment');
        $mobiles = array_keys($mobile_rec);
        if (!empty($data['contact_no']))
            array_unshift($mobiles, $data['contact_no']);
        $to = implode(',', $mobiles);
        $message = compose_message('messages/payment-schedule-alert.sms', $data);
        //SendSMS(SMS_SENDER, $to, $message);
        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'payment', 'sms');
        process_notification($id_msg);
        writelog('send_fault_report_completed_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);
    }

}

?>