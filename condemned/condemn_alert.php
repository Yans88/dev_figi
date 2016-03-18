<?php

include '../util.php';
include '../common.php';
include 'condemned_util.php';

//if ((LOAN_RETURN_ALERT == FALSE) || (!ENABLE_NOTIFICATION))
//    die("notification disabled or non-periodic alert type\n");
    
send_condemn_recommendation_alert();

?>