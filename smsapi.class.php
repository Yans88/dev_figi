<?php

define('SMSAPI_USERNAME', 'user');
define('SMSAPI_PASSWORD', 'pass');
define('SMSAPI_SENDER', 'FiGi');
define('SMSAPI_URL', 'http://localhost/~elbas/smsgw/send.php');

define('SMSAPI_INSUFICIENT_CREDITS', -1);
define('SMSAPI_NETWORK_NOTCOVERED', -2);
define('SMSAPI_INVALID_USER_OR_PASS', -3);
define('SMSAPI_MISSING_DESTINATION', -4);
define('SMSAPI_MISSING_MESSAGE', -5);
define('SMSAPI_MISSING_SENDER', -6);

define('AUTH_FAILED', -1); // Invalid username and/or password
define('XML_ERROR', -2); // Incorrect XML format
define('NOT_ENOUGH_CREDITS', -3); // Not enough credits in user account
define('NO_RECIPIENTS', -4); // No good recipients
define('GENERAL_ERROR', -5); // Error in processing your request
define('SEND_OK',  0); // > 0



class SMSAPI 
{
    protected $_destinations = array();
    protected $_message;
    protected $_username;
    protected $_password;
    protected $_sender;
    protected $_url;
    
    function set_username($name) { $this->_username = $name; }
    function set_password($pass) { $this->_password = $pass; }    
    function set_sender($sender) { $this->_sender = $sender; }
    function set_url($url) { $this->_url = $url; }
    function set_message($message) { $this->_message = $message; }       
    function set_destinations($destinations) {
        if (is_array($destinations))
            $this->_destinations = $destinations;
        else {
            $arr = explode(',', $destinations);
            $this->_destinations = array_merge($this->_destinations, $arr);
        }
    }
    function add_destination($destination) { $this->_destinations[] = $destination; }
    function clear_destinations() { $this->_destinations = array(); }
    function make_message_id($no) { return time() . $no; }
    function __destruct() { }
    function __construct()  
    { 
        $this->set_username(SMSAPI_USERNAME);
        $this->set_password(SMSAPI_PASSWORD);
        $this->set_url(SMSAPI_URL);
    }
    
    function send($to, $msg, $from)
    {
        $this->set_message($msg);
        $this->set_sender($from);
        $this->set_destinations($to);
        
        return $this->get();
    }
    
    function get()
    {
        $fields['id']  = $this->_username;
        $fields['pw']  = $this->_password;
        $fields['frm'] = $this->_sender;
        $fields['dst'] = array_shift($this->_destinations);
        $fields['msg'] = $this->_message;          

        $http = new HttpClient();
        $response = $http->get($this->_url, $fields);
        return $response;
    }
      
    function post()
    {
        $recipients = null;
        $no = 0;
        foreach ($this->_destinations as $dst){
           $msgid = $this->make_message_id($no++);
           $recipients .= '<gsm messageId="'.$msgid.'">'.$dst."</gsm>\n";
        }
        //  build XML-formatted data
        $xmlString =<<<XML
<SMS>
<authentification>
<username>{$this->_username}</username>
<password>{$this->_password}</password>
</authentification>
<message>
<sender>{$this->_sender}</sender>
<text>{$this->_message}</text>
</message>
<recipients>
{$recipients}
</recipients>
</SMS>
XML;

        // previously formatted XML data becomes value of "XML" POST variable
        $data = "XML=" . urlencode($xmlString);
        $http = new HttpClient();
        $response = $http->post(self::url_post, $data);
        libxml_use_internal_errors(true);
        if (!simplexml_load_string($response))
            return false;
        $xml = new SimpleXMLElement($response);
        return $xml->status;
    }
    
     
}


?>
