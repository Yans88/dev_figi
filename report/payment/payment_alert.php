<?php

include '../util.php';
include '../common.php';
include 'payment_util.php';

if ((PAYMENT_ALERT_OPTION != 2) || (!ENABLE_NOTIFICATION))
    die("notification disabled or non-periodic alert type\n");
    
/* check and alert for next payment */
$dtf = '%d-%b-%Y';
$query  = "SELECT *, DATE_FORMAT(date_of_purchase, '$dtf') date_of_purchase , 
            DATE_FORMAT(first_date_of_payment, '$dtf') first_date_of_payment ,
            DATE_FORMAT(next_date_of_payment, '$dtf') next_date_of_payment ,
            DATE_FORMAT(last_date_of_payment, '$dtf') last_date_of_payment  
            FROM payment  
            WHERE reminder=1 AND next_date_of_payment = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL reminder_lead_time DAY), '%Y-%m-%d') ";
$rs = mysql_query($query);

if ($rs && mysql_num_rows($rs) > 0){
    while ($rec = mysql_fetch_assoc($rs)){
        // update next payment date
        switch($rec['frequency']){
        case 3: $interval = 'INTERVAL 1 YEAR'; break;
        case 2: $interval = 'INTERVAL 6 MONTH'; break;
        case 1: $interval = 'INTERVAL 1 MONTH'; break;
        default: $interval = 'INTERVAL 1 WEEK'; break;
        }
		// stop reminder if next_date_of_payment = last_date_of_payment
        $query = "UPDATE payment SET reminder = 0 
                    WHERE id_payment = $rec[id_payment] AND next_date_of_payment = last_date_of_payment";
        mysql_query($query);
		
		//log scheduler
		$query = "INSERT INTO payment_scheduler(id_payment, run_time) 
					VALUES($rec[id_payment], now())";
		mysql_query($query);
        //echo mysql_error().$query;
        // send alert
        $rec['invoice'] = $rec['invoice_no'];
        send_payment_alert($rec);
        
    }
}


?>