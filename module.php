<?php
/*
    module loader based on configuration
*/

if (defined('ENABLE_SMS_NOTIFICATION') && ENABLE_SMS_NOTIFICATION){
    if (file_exists(FIGI_PATH . '/smsapi.class.php')){
        define('MODULE_SMS_LOADED', true);
        require_once('httpclient.class.php');
        require_once('smsapi.class.php');
    }
}

if (defined('ENABLE_CALENDAR') && ENABLE_CALENDAR){
    if (file_exists(FIGI_PATH . '/calendar/calendar.php')){
        $config = $configuration['calendar'];
        
        define('MODULE_CALENDAR_LOADED', true);
        define('CALENDAR_TIME_START', $config['time_start']);
        define('CALENDAR_TIME_FINISH', $config['time_finish']);
    }
}

?>
