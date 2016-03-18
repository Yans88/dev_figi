<?php
@session_start();
ob_start();

//$base_url = "http://dev5m3.figi.sg";
$base_url = 'http://localhost/dev5m3';
$root_path = $_SERVER['DOCUMENT_ROOT']."/dev5m3/";
//$root_path = $_SERVER['DOCUMENT_ROOT']."/";
//$base_url = 'http://localhost/dev5m32904';
//echo $root_path;
ini_set('display_errors', false);

define('TIMEZONE', 'Asia/Singapore'); // timezone, put Asia/Singapore for Singapore
define('EMAIL_AGENT', 'websmtp'); // available websmtp,smtp
define('ALTER_TMP_PATH', '/tmp/'); // set to false to by pass approval stage
define('PHP_WIN', 'C:\\wamp\\bin\\php\\php5.3.8\\php.exe'); // set to php executable program on windows

// equipment ownership and issuance on department level
define('EQUIPMENT_OWNERSHIP', true); // true/false

//enable alternate portal here
define('ALTERNATE_PORTAL_STATUS', false); // the status is ENABLE OR DISABLE

// calendar 
define('ENABLE_CALENDAR', true);
define('MAX_UPCOMING_EVENTS', 10); // maximum number of events displayed as upcoming event
define('UPCOMING_EVENTS_PERIOD', 14); // upto days of upcoming events

// loan
define('REQUIRE_LOAN_APPROVAL', false); // set to false to by pass approval stage
//define('QUICK_LOAN_ENABLED', true); // 

//service
define('REQUIRE_SERVICE_APPROVAL', false); // set to false to by pass approval stage

// condemn
define('REQUIRE_CONDEMNED_APPROVAL', true); // set to false to by pass approval stage
define('ENABLE_SECOND_RECOMMENDATION', false);
/*
for approval condemnation, has flow type
1: D'Admin -> D'HoD -> Director -> Principle [Condemned]-> D'Admin (Disposal)
2: D'Admin -> D'HoD -> D'Admin (offline - Director -> Principle) - D'HoD [Condemned] -> D'Admin (Disposal)
*/
define('CONDEMNATION_FLOW_TYPE', 1); 

define('USE_NEW_BOOKING', true);

// item
define('AUTO_GENERATED_ASSETNO', FALSE);
define('ASSETNO_FORMAT', '{CAT-CODE}-{SN}-{YEAR}'); 
define('ASSETNO_SN_LENGTH', 6); //zero left padded serial/sequence (auto-generated) number for asset no
define('UNLOCK_LOCATION', true); // if true location will be created if not empty, otherwise import canceled if location unknown
define('TEMPORARY_IMPORT_DEFAULT', "ON"); // Use "ON" or "OFF" for this. if ON Temporary Item Import will active. otherwise Temporary Item Import will de-active
define('ASSETNO_LENGTH', 18); //zero left padded serial/sequence (auto-generated) number for asset no

// setting for sending email via smtp
global $smtpcfg;
$smtpcfg['server'] = '127.0.0.1';
$smtpcfg['port']   = 25;
$smtpcfg['user']   = 'elbas';
$smtpcfg['passwd'] = 'tsani';
$smtpcfg['error']  = '';
$smtpcfg['timeout']  = 25; // sec

// setting for sending email via websmtp -> smtp on remote
global $websmtpcfg;
//$websmtpcfg['url'] = 'http://www.leafion.com/safarin/chesterfield/websmtp.php';
$websmtpcfg['url'] = 'http://www.figi.sg/chester/websmtp.php';
$websmtpcfg['timeout'] = 25;

// db config

$db_host = 'localhost';
$db_name = 'figi_dev5m3';
$db_user = 'root';
$db_pass = '';

/*
$db_host = 'localhost';
$db_name = 'figi_dev5m3';
$db_user = 'root';
$db_pass = '';
*/

//make db connection
mysql_connect($db_host, $db_user, $db_pass) or die('can not connect to database server');
mysql_select_db($db_name) or die('can not open database "' . $db_name . '"');
mysql_query('SET time_zone = "Asia/Singapore"');

/*
available parameter:
{DEPT-ID} -> department id
{DEPT-CODE} -> department code
{CAT-ID} -> category id
{CAT-CODE} -> category code
{YEAR} -> year of purchase
{SN} -> auto-generated sequence number
*/

define('GENERATED_INSTANCES_PERIOD', 18); // months
define('REPEAT_NONE', 'NONE');
define('REPEAT_DAILY', 'DAILY');
define('REPEAT_WEEKLY', 'WEEKLY');
define('REPEAT_MONTHLY', 'MONTHLY');
define('REPEAT_YEARLY', 'YEARLY');

$repetitions = array(
    REPEAT_NONE => 'No repeat', 
    REPEAT_DAILY => 'Daily',
    REPEAT_WEEKLY => 'Weekly',
    REPEAT_MONTHLY => 'Monthly',
   // REPEAT_YEARLY => 'Yearly'
    );
	
$repetition_labels = array(
    'NONE' => 'No repeat', 
    'DAILY' => 'Daily',
    'WEEKLY' => 'Weekly',
    'MONTHLY' => 'Monthly',
    'YEARLY' => 'Yearly'
    );
	
$repeat_labels = array(
    REPEAT_DAILY => 'day(s)',
    REPEAT_WEEKLY => 'week(s)',
    REPEAT_MONTHLY => 'month(s)',
    //REPEAT_YEARLY => 'year(s)'
    );
    
$delete_commands = array(
    'only-me' => 1,
    'me-follow' => 2,
    'all-of-me' => 3);
    
define('DATE_FORMAT', '%e-%b-%Y');
define('DATE_FORMAT_MYSQL', '%e-%b-%Y');
define('DATE_FORMAT_PHP', 'j-M-Y');
// loan status
define('PENDING', 'PENDING');
define('APPROVED', 'APPROVED');
define('LOANED', 'LOANED');
define('RETURNED', 'RETURNED');
define('REJECTED', 'REJECTED');
define('COMPLETED', 'COMPLETED');
define('ISSUED_OUT', 'ISSUED');
define('LOOSING', 'LOST');
define('RECOMMENDED', 'RECOMMENDED');
define('RECOMMENDED2', 'RECOMMENDED2');
define('PARTIAL_IN', 'PARTIAL_IN');

define('CAN_VIEW', 	 0);
define('CAN_CREATE', 1);
define('CAN_UPDATE', 2);
define('CAN_DELETE', 3);

// default group,
define('GRPADM', 1); //administrator
define('GRPHOD', 2); //hod
define('GRPPRI', 3); //principle
define('GRPTEA', 4); //teacher -> loan only
define('GRPDIR', 5); //director -> condemn only
define('GRPTEADM', 6); //Teacher admin
define('GRPASSETADMIN', 14); //Asset Admin
define('GRPSTUDENT', 15); //Student
define('GRPASSETOWNER', 16); //Asset Owner
define('GRPSYSTEMADMIN', 18); //System Admin

// item status
define('ISSUED', 1);
define('ONLOAN', 2);
define('UNDER_SERVICE', 3);
define('STORAGE', 4);
define('CONDEMNED', 5);
define('AVAILABLE_FOR_LOAN', 6);
define('FAULTY', 7);
define('LOST', 8);
define('TO_BE_CONDEMNED', 9);
define('IN_USE', 10);

// item stock take validation
define('INVALID', 'INVALID');
define('VALID', 'VALID');

// fault status
define('FAULT_NOTIFIED', 'NOTIFIED');
define('FAULT_PROGRESS', 'PROGRESS');
define('FAULT_COMPLETED', 'COMPLETED');

// access id
define('ACCESS_VIEW',   1);
define('ACCESS_CREATE', 2);
define('ACCESS_UPDATE', 3);
define('ACCESS_DELETE', 4);

// log activity's key
define('LOG_ACCESS', 'ACCESS');
define('LOG_VIEW',   'VIEW');
define('LOG_CREATE', 'CREATE');
define('LOG_UPDATE', 'UPDATE');
define('LOG_DELETE', 'DELETE');

// transaction number prefix
define('TRX_PREFIX_LOAN', 'LR');
define('TRX_PREFIX_SERVICE', 'SR');
define('TRX_PREFIX_FACILITY', 'FCR');
define('TRX_PREFIX_FAULT', 'FCG');
define('TRX_PREFIX_MACHREC', 'MHR');
define('TRX_PREFIX_DESKCOPY', 'DCLR');
define('TRX_PREFIX_INVOICE', 'INV');
define('TRX_PREFIX_CONDEMNED', 'CDM');

define('FIGI_OS', strtoupper(php_uname('s')));
define('FIGI_PATH', dirname(__FILE__));
define('FIGI_URL', $base_url);

// payment frequency
$frequency_list = array('Weekly', 'Monthly', 'Every 6 Months', 'Anually');

if ( !function_exists('sys_get_temp_dir') )
{
    function sys_get_temp_dir()
    {
        if ( !empty($_ENV['TMP']) )
            return realpath( $_ENV['TMP'] );
        else if ( !empty($_ENV['TMPDIR']) )
            return realpath( $_ENV['TMPDIR'] );
        else if ( !empty($_ENV['TEMP']) )
            return realpath( $_ENV['TEMP'] );
        else {
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file )
            {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            } else
                return FALSE;
        }   
    }
}


date_default_timezone_set(TIMEZONE);

$portals = array('loan', 'service', 'fault', 'facility', 'student_usage', 'alternate' );

$safe_mode = ini_get('safe_mode');
if (empty($safe_mode))
    define('TMPDIR', sys_get_temp_dir());
else
    define('TMPDIR', ALTER_TMP_PATH);

global $configuration;
if (!function_exists('load_configuration'))
    require_once('util.php');
$configuration = load_configuration();
include 'encryption.php';
global $encrypt;
$encryption = new Encryption();

define('LOG_PATH', TMPDIR . '/figi.log');

// define constant from configuration table as necessary
define('ENABLE_REQUEST_LEADTIME', $configuration['loan']['request_leadtime'] > 0); 
define('LOAN_RETURN_ALERT',  $configuration['loan']['return_alert'] == 'true'); 
define('LOAN_RETURN_LEAD_DAYS',  $configuration['loan']['return_alert_lead_days']); 
define('LONG_TERM_LOAN_CONFIRM_PERIOD',  $configuration['loan']['long_term_confirm_period']); 
define('PAYMENT_ALERT_OPTION',  $configuration['payment']['payment_alert_option']); // available: 1 -> first & last, 2 -> every period (monthly/weekly)
//define('RETURN_REMINDER',  $configuration['keyloan']['return_hours']); 
define('ITEM_EMAIL_NOTIFICATION',  $configuration['item']['email_notification']); // STOCK TAKE NOTIFICATION

//define('ENABLE_REQUEST_LEADTIME', $configuration['loan']['request_leadtime']); 

define('ENABLE_NOTIFICATION',  $configuration['global']['enable_notification'] == 'true'); 
define('ENABLE_SMS_NOTIFICATION', $configuration['global']['enable_notification_sms'] == 'true'); 
define('ENABLE_EMAIL_NOTIFICATION', $configuration['global']['enable_notification_email'] == 'true'); 
define('SYSTEM_EMAIL',  $configuration['global']['system_email']);
define('SMS_SENDER', $configuration['global']['sms_sender']);
define('RECORD_PER_PAGE',  $configuration['global']['number_of_record_per_page']); // change the number of records shown per page/display
define('PORTAL_RECORD_PER_PAGE',  $configuration['global']['number_of_record_per_page_portal']); // change the number of records shown per page/display for portal

define('FACILITY_DAYS_TO_DISPLAY', $configuration['facility']['number_of_days_to_display']);

define('NRIC_LENGTH', $configuration['deskcopy']['length_of_nric']);
define('ISBN_LENGTH', $configuration['deskcopy']['length_of_isbn']);
define('SERIAL_LENGTH', $configuration['deskcopy']['length_of_serial']);

define('EXPENDABLE_LENGTH', $configuration['expendable']['expendable_length']);

define('CONSUMABLE_NEED_SIGNATURE', $configuration['consumable']['require_signature'] == 'true');
define('MAINTENANCE_REQUIRE_SIGNATURE', $configuration['machrec']['require_signature'] == 'true'); // set to false to by pass approval stage

define('THUMB_WIDTH', $configuration['item']['thumb_width']);
define('THUMB_HEIGHT', $configuration['item']['thumb_height']);
define('BARCODE_WIDTH', 440);//$configuration['item']['barcode_width']);
define('BARCODE_HEIGHT', 100);//$configuration['item']['barcode_height']);

define('STYLE_PATH', 'style/' . $configuration['global']['style'] . '/');
define('BACKUP_PATH', $configuration['global']['backup_path'] . '/');

define('LANGUAGE', 'en');

include 'module.php';
include 'features_v6.php';
include 'langs/label-'.LANGUAGE.'.php';
include 'langs/message-'.LANGUAGE.'.php';

$month_names = array('January', 'February', 'March', 'April', 'May', 'June', 'July',
				'August', 'September', 'October', 'November', 'December');
$short_month_names = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul',
				'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$day_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'); 
$short_day_names = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'); 


/////////// Chesterfield Features ///////////

?>