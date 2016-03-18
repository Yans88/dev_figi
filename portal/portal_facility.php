<?php
define('FROM_PORTAL', 1);

if (defined('USE_NEW_BOOKING') && USE_NEW_BOOKING){
	require 'portal_booking.php';
} else  {
	require 'old_portal_facility.php';
}

