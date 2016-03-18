<?php

include '../util.php';
include '../common.php';
include 'loan_util.php';

//if ((LOAN_RETURN_ALERT == FALSE) || (!ENABLE_NOTIFICATION))
//    die("notification disabled or non-periodic alert type\n");
    

// check for long term confirmation reminder
$rs = issuance_notification();
if ($rs && mysql_num_rows($rs) > 0){
    while ($rec = mysql_fetch_assoc($rs)){
        //print_r($rec);
        send_issuance_notification($rec);
    }
}


?>