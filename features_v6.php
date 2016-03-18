<?php 

define('item_is_homepage', TRUE);

//existing from common
define('STOCK_TAKING', TRUE);
define('STOCK_TAKING_APP',FALSE); 	
define('STOCK_TAKING_PDA',FALSE); 	
define('PAYMENT_MODULE',FALSE); 	
define('EXPENDABLE_ITEM',TRUE);   
define('CONSUMABLE_ITEM',TRUE);   
define('APP_MODULE',FALSE);	
define('QR_CODE', TRUE);
define('ABILITY_BOOKING_REQUEST', FALSE);


// add for figi v6
define('specifications_enabled', true);
define('quick_loan_enabled', false);
define('quick_return_enabled', false);
define('alternate_import_enabled', true);
define('temporary_import_enabled', true);
define('maintenance', true);
define('mobile_cart', true);
define('deskcopy_item_enabled', true);
define('student_loan', true);
define('student_usage', false); 			//ok
define('condemned', true); 					//ok
define('consumable_item_report', false); 	//ok

//add for sms notification
define('sms_loan', true);
define('sms_student_loan', true);
define('sms_service_request', true);
define('sms_fault_reporting', true);
define('sms_facility_booking', true);
?>