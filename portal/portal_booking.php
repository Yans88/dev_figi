<?php

require './booking/booking_util.php';
$mod_url = './?mod=portal&portal=facility';
/*
if (!empty($_act)){
	require './booking/booking_'.$_act.'.php';
} else
*/
	require './booking/booking.php';


