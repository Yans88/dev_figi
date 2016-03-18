<?php

function build_combo($name, $data, $selected=-1, $eventchange = null, $others=null){
	$combo = '<select name="'.$name.'" id="'.$name.'" '.$others;
	if ($eventchange != null)
    	$combo .= ' onchange="'.$eventchange.'"';
  	$combo .= ' >';
	$combo .= build_option($data, $selected);
	$combo .= '</select>';
	return $combo;
}

function build_option($data, $selected=-1, $optgroup = false){
	$combo = null;
	//$combo ='<option value="">-- Select --</option>';
	foreach($data as $k => $v){
        if (is_array($selected)) $status = isset($selected[$k]) ? 'selected' : null;
        else $status = ($k == $selected) ? 'selected' : null;
        $combo .='<option value="'.$k.'" '.$status.'>'.$v.'</option>';
	}
	return $combo;
}

function build_checkboxes($name, $data, $selected){
  $result = null;
  ksort($data);
  foreach($data as $k => $v){
    
    $result .= '<input type="checkbox" ' . ((in_array($k, $selected))?'checked':'') . ' name="'. $name .'['.$k.']" > '. $v .' <br />';
  }
  return $result;
}

function date_add_day($dt, $days){
	$tm = $dt;// strtotime($dt);
	return mktime(date('H', $tm), date('i', $tm), date('s', $tm), 
					date('n', $tm), date('j', $tm)+$days, date('Y', $tm));	
}

function date_add_sec($dt, $sec){
	$tm = $dt;// strtotime($dt);
	return mktime(date('H', $tm), date('i', $tm), date('s', $tm)+$sec, 
					date('n', $tm), date('j', $tm), date('Y', $tm));	
}

function date_add_months($tm, $mon){
	return mktime(date('H', $tm), date('i', $tm), date('s', $tm), 
					date('n', $tm)+$mon, date('j', $tm), date('Y', $tm));	
}

function time_add_days($tm, $days){
	return mktime(date('H', $tm), date('i', $tm), date('s', $tm),
                    date('n', $tm), date('j', $tm)+$days, date('Y', $tm));
}

function convert_date($date, $format = 'Ymd'){
  //$adate = date_parse_from_format(DATE_FORMAT, $date);
  //$tm = maketime(0, 0, 0, $adate['month'], $adate['day'], $adate['year']);
  $tm = strtotime($date);
  return date($format, $tm);
}

function convert_uk_date($dttmstr, $outfmt)
{
    // format 22/9/2011 00:00:00
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dttmstr, $matches)){
        $year = $matches[3];
        $mon = $matches[2];
        $day = $matches[1];
        if (preg_match('/ (\d{1,2}):(\d{1,2}):(\d{1,2})$/', $dttmstr, $matches)){
            $hour = $matches[1];
            $min = $matches[2];
            $sec = $matches[3];
        } else {
            $hour = date('H');
            $min = date('i');
            $sec = date('s');
        }
        $tm = mktime($hour, $min, $sec, $mon, $day, $year);
        return date($outfmt, $tm);
    }
    return date($outfmt);
}

function get_month_index($month_name, $is_long = false)
{
	global $month_names, $short_month_names;
    for($i=0; $i<count($month_names); $i++){
        $month = ($is_long) ? $month_names[$i] : $short_month_names[$i];
        if (strtolower($month) == strtolower($month_name))
            return $i+1;
    }
    return 0;
}

function time_dMY($str)
{
    if (preg_match('/(\d\d)-(\w{3})-(\d{4})/', $str, $matches)){
        return mktime(0, 0, 0, $matches[2], get_month_index($matches[1]), $matches[3]);
    }
    return 0;
}

function time_Ymd($str)
{
    if (preg_match('/(\d{4})-(\d\d)-(\d\d)/', $str, $matches)){
        return mktime(0, 0, 0, $matches[3], get_month_index($matches[2]), $matches[1]);
    }
    return 0;
}


function get_timestamp($dtstr) {
    list($dt, $tm) = explode(' ', $dtstr);
    $adate = explode('-', $dtstr);
    
}

function make_paging($page, $total_page, $link){
	$result = '';
	if ($page > 1){
	  $result .= '<a href="'.$link.'1" title="Go to First Page"><img border=0 width=16 src="images/first.png" ></a> '; 
	  $result .= '<a href="'.$link.($page-1). '" title="Go to Previous Page"><img border=0 width=16 src="images/back.png" ></a> &nbsp; '; 
	} else {
		$result .= '<img border=0 width=16 src="images/nofirst.png" ></a> ';
		$result .= '<img border=0 width=16 src="images/noback.png" ></a> &nbsp; ';
	}
	if ($page < $total_page){
	  $result .= ' &nbsp;<a href="'.$link.($page+1). '" title="Go to Next Page"><img border=0 width=16 src="images/forward.png"></a> '; 
	  $result .= ' &nbsp;<a href="'.$link.($total_page). '" title="Go to Last Page"><img border=0 width=16 src="images/last.png"></a>';
	} else {
		$result .= ' &nbsp;<img border=0 width=16 src="images/noforward.png" ></a> ';
		$result .= ' &nbsp;<img border=0 width=16 src="images/nolast.png" ></a>';
	}
	return $result;
}

function get_signatures($lid = 0){
  $result = array();
  $query = "SELECT * FROM loan_signature WHERE id_loan = $lid ";
  $rs = mysql_query($query);
  if ($rs && mysql_num_rows($rs)>0)
      $result = mysql_fetch_assoc($rs);
  return $result;
}

function get_signature($lid = 0, $status = 'approve'){

	$query = "SELECT ".$status."_sign FROM loan_signature WHERE id_loan = $lid ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_row($rs);
		return $rec[0];
	}
	return false;
}

function set_configuration($section, $name, $value){	
    $query = "REPLACE INTO configuration(`section`, `name`, `value`) VALUES('$section', '$name', '$value')";
	mysql_query($query);
	return (mysql_affected_rows()>0);
}

function set_configurations($section, $pairs){	
    if (is_array($pairs)){
        $values = array();
        foreach($pairs as $k => $v)
            $values[] = "('$section', '$k', '$v')";
        if (count($values)>0){
            $query = "REPLACE INTO configuration(`section`, `name`, `value`) VALUES " . implode(', ', $values);
            mysql_query($query);
            return (mysql_affected_rows()>0);
        }
    } 
    return false;
}

function get_configuration($section, $name){	
	$result = null;
	$query = "SELECT `value` FROM configuration WHERE section = '$section' AND name = '$name' ";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){      
        $rec = mysql_fetch_row($rs);
		$result = $rec[0];        
	}
	return $result;
}

function load_configuration($section = null){
	$result = array();
	$query = "SELECT * FROM configuration ";
	if ($section != null)
        $query = " WHERE section = '$section' ";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){      
        while ($rec = mysql_fetch_assoc($rs))
            $result[$rec['section']][$rec['name']] = $rec['value'];
	}
	return $result;
}

function parse_email_config($config = null){
	$result = array();
    if ($config != null) {
        $recs = explode(',', $config);
        foreach ($recs as $rec){
            $cols = explode('|', $rec);
			$cols[0] = trim($cols[0]);
            if (!empty($cols[0]))
                $result[] = trim($cols[0]);
        }
    }
	return $result;
}

function get_portal_names(){
    $result = array();
    if ($handle = opendir('./portal/')) {
        $i = 0;
        while (false !== ($file = readdir($handle))) {
            if (substr($file, 0, 6) == 'portal')
                $result[$i++] = $file;
        }
        closedir($handle);
    }
    return $result;
}

function SendEmail($from, $to, $subject, $message, $cc = ''){
	if (defined('EMAIL_AGENT'))
		if (EMAIL_AGENT == 'smtp')
			smtp_SendEmail($from, $to, $subject, $message, $cc);
		else if (EMAIL_AGENT == 'websmtp')
			websmtp_SendEmail($from, $to, $subject, $message, $cc);
}

function SendEmailHtml($from, $to, $subject, $message, $cc = ''){
	websmtp_SendEmailHtml($from, $to, $subject, $message, $cc);
}

function SendSMS($from, $to, $message)
{
    if (defined('ENABLE_SMS_NOTIFICATION') && ENABLE_SMS_NOTIFICATION && defined('MODULE_SMS_LOADED')){
        $sms = new SMSAPI();
        $sms->send($to, $message, $from);
    }
}

function smtp_SendEmail($from, $to, $subject, $message, $cc = ''){
	global $smtpcfg;
	require_once('class.smtp.php');
	$crlf 		 = "\r\n";
	$mail_data   = 'From: '.$from.$crlf;
	$mail_data   .= 'To: '. $to. $crlf;
	$mail_data   .= 'Subject: '.$subject.$crlf;
    $cc_rcpts = array();
	if ($cc <> '') {
		$mail_data .= 'Cc: '.$cc.$crlf;
        $cc_rcpts = explode(',', $cc);
        //print_r($cc_rcpts);
    }
	$mail_data .= $crlf.$message;
    //print_r($smtpcfg);
	$smtp = new SMTP;
    //$smtp->do_debug = true;
	if ($smtp->Connect($smtpcfg['server'], $smtpcfg['port'], $smtpcfg['timeout']))
	  if ($smtp->Hello()) //'indoaccess.co.id'
		if ($smtp->Authenticate($smtpcfg['user'], $smtpcfg['passwd']))
		  if ($smtp->Mail($from))
			if ($smtp->Recipient($to)){
              foreach( $cc_rcpts as $addr)
                $smtp->Recipient(trim($addr));            
			  if ($smtp->Data($mail_data))
				if ($smtp->Quit()) return true;
            }
	if ($smtp->error <> null)
	  $smtpcfg['error'] = $smtp->error;
	if ($smtp->Connected()) 
	  $smtp->Close();
	return false;
}

function httprequest($request_url, $type='get', $data=array()) {
    $url   = parse_url($request_url);
    $host  = $url['host'];
    $path  = $url['path'];
    $query = (!empty($url['query'])) ? $url['query'] : null;
    $path .= $query ? '?'. $query : '';

    $parameter = $sep = '';
    if (!empty($data)) {
        foreach ($data as $key => $value){
            $parameter .= $sep . urlencode($key) .'='. urlencode($value);
            $sep = '&';
        }
    }
    $user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'MyAgent';
    if (strtolower($type) == 'get') {
        $path .= $parameter;
        $out  = "GET {$path} HTTP/1.1\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $user_agent\r\n";
        $out .= "Host: {$host}\r\n";
        $out .= "Connection: Close\r\n\r\n";
    } else {
        $out  = "POST {$path} HTTP/1.1\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";		
        $out .= "User-Agent: $user_agent\r\n";
        $out .= "Host: {$host}\r\n";
        $out .= 'Content-Length: '.strlen($parameter)."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $parameter;
    }
	$logstr = null;
    $fp = fsockopen($host, 80, $errno, $errstr, 30);
    if ($fp) {
        fwrite($fp, $out);
        while ($str = fgets($fp)) $logstr.=$str;
        fclose($fp);
    }
   error_log( "httprequest : $errno $errstr $logstr $type");
 }
 
function websmtp_SendEmail($from, $to, $subject, $message, $cc = ''){
	global $websmtpcfg;
    $data['to'] = $to;
    if ($cc != '') $data['cc'] = $cc;
    $data['from'] = $from;
    $data['subj'] = $subject;
    $data['msg'] = $message;
	httprequest($websmtpcfg['url'], 'post', $data);
	error_log('websmtp_SendEmail ::'.serialize($data));
}

function websmtp_SendEmailHtml($from, $to, $subject, $message, $cc = ''){
	global $websmtpcfg;
    $data['to'] = $to;
    if ($cc != '') $data['cc'] = $cc;
    $data['from'] = $from;
    $data['subj'] = $subject;
    $data['msg'] = $message;
	$data['header'] = "yes";
	httprequest($websmtpcfg['url'], 'post', $data);
	error_log('websmtp_SendEmail ::'.serialize($data));
}

function websmtp_SendEmail_X($from, $to, $subject, $message, $cc = ''){
	global $websmtpcfg;
	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_URL, $websmtpcfg['url']);
	curl_setopt($curlHandle, CURLOPT_POST, 1);
	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, "to=".$to."&cc=".$cc ."&from=".$from."&subj=".$subject."&msg=".$message);
	curl_setopt($curlHandle, CURLOPT_HEADER, 0);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_TIMEOUT, $websmtpcfg['timeout']);
	$hasil = curl_exec($curlHandle);
	curl_close($curlHandle);	
}

function get_notification_emails($dept, $cat, $mod){
	$result = array();
	$query = "SELECT email, name FROM notification_email
				WHERE id_department = '$dept' AND module = '$mod' AND id_category = '$cat'";            
	$rs = mysql_query($query);
	//error_log($query.mysql_error());
	if ($rs)
		while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;
	return $result;
}

function save_notification_emails($dept, $cat, $mod, $emails){
	$result = 0;
	$values = array();
	foreach ($emails as $rec)
		$values[] = "($dept,$cat,'$mod','$rec[email]','$rec[name]')";
	// clear prev emails
	$query = "DELETE FROM notification_email WHERE id_department = '$dept' AND module = '$mod' AND id_category = '$cat' ";
	mysql_query($query);
	//writelog('emails: '.count(emails));
	if (count($values) > 0){
		$query = "INSERT INTO notification_email(id_department, id_category, module, email, name) ";
		$query .= ' VALUES ' . implode(',', $values);		
		mysql_query($query);
		$result = mysql_affected_rows();
		//writelog('save_notification_emails: ' .$result);
	}
	return $result;
}

function get_notification_mobiles($dept, $cat, $mod){
	$result = array();
	$query = "SELECT mobile, name FROM notification_mobile
				WHERE id_department = '$dept' AND module = '$mod' AND id_category = '$cat'";            
	$rs = mysql_query($query);
    
	if ($rs)
		while ($rec = mysql_fetch_assoc($rs))
			if(check_numb_sms($rec['mobile'])){
				$result[] = $rec;
			}			
	return $result;
}

function save_notification_mobiles($dept, $cat, $mod, $mobiles){
	$result = 0;
	$values = array();
	foreach ($mobiles as $rec)
		$values[] = "($dept,$cat,'$mod','$rec[email]','$rec[name]')";
	// clear prev mobiles
	$query = "DELETE FROM notification_mobile WHERE id_department = '$dept' AND module = '$mod' AND id_category = '$cat' ";
	mysql_query($query);
	
	if (count($values) > 0){
		$query = "INSERT INTO notification_mobile(id_department, id_category, module, mobile, name) ";
		$query .= ' VALUES ' . implode(',', $values);		
		mysql_query($query);
		$result = mysql_affected_rows();
	}
	return $result;
}

function process_notification_immediate($id)
{
$msg = get_notification_message($id);
//error_log(serialize($msg));
if (is_array($msg)){
    if ($msg['msg_type'] == 'email')
        SendEmail($msg['msg_from'], $msg['msg_to'], $msg['msg_subject'], $msg['msg_content'], $msg['msg_cc']);
	else if ($msg['msg_type'] == 'emailHtml')
        SendEmailHtml($msg['msg_from'], $msg['msg_to'], $msg['msg_subject'], $msg['msg_content'], $msg['msg_cc']);
    else if ($msg['msg_type'] == 'sms')
        SendSMS($msg['msg_from'], $msg['msg_to'], $msg['msg_content']);

    $query = "UPDATE notification_message SET msg_status = 1, process_time = now() 
                WHERE id_notification = $id";
    mysql_query($query);
    //error_log($query);
}
}

function process_notification($id)
{
	process_notification_immediate($id);
	return true;
    //error_log("process_notification()");
    if ($id > 0){
        if (preg_match('/WINDOWS/i', FIGI_OS)){
            error_log('Server OS: Windows');
            $script = FIGI_PATH . '\process_notification.php';
            //echo $script;
            $WshShell = new COM("WScript.Shell");
            $oExec = $WshShell->Run(PHP_WIN . " -f " . $script . " $id", 0, false);
            $cmd = PHP_WIN . " -f " . $script . " $id";
            $result = shell_exec($cmd);
        } else { // assume as *nix family
           // error_log('Server OS: '.FIGI_OS);
            $cmd_log_path = '/home/figi/tmp/figi.proc';
            $script = FIGI_PATH . '/process_notification.php';
            $phpbin = `which php`;
            $cmd = "$phpbin $script $id >> $cmd_log_path 2>&1";
            $result = shell_exec($cmd);
        }
           // error_log('cmd: ' .$cmd);
            //error_log('result: '.$result);
    }    
}

function process_notification_new($id)
{
    //echo "TEST $id TOST";
    if ($id > 0){
        $script = FIGI_PATH . '/process_notification.php';
        if (FIGI_OS == 'WINDOWS'){
            $WshShell = new COM("WScript.Shell");
            $oExec = $WshShell->Run(PHP_WIN . " -f " . $script . " $id", 0, false);
        } else { // assume as *nix family
            $cmd = "`which php` $script $id >> /tmp/figi.proc &";
            $result = shell_exec($cmd);
        }
    }    
}

function run_windows_shell($cmd)
{
     shell_exec('start /b dir /s /b ' . $cmd);
}

function run_linux_shell($cmd)
{
     shell_exec($cmd . ' &');
}

function get_notification_message($id)
{
    $result = null;
    $query = "SELECT * from notification_message WHERE id_notification = $id";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function set_notification_message($from, $to, $subject, $message, $cc, $module, $type = 'email')
{
    $result = 0;
    $message = mysql_real_escape_string($message);
    $subject = mysql_real_escape_string($subject);
	$account_sms = get_config_sms();
	if(empty($account_sms) && $type="sms") return false;
	
    $query = "INSERT INTO notification_message (msg_from, msg_to, msg_cc, msg_subject, msg_content, module, msg_type)
                VALUES('$from', '$to', '$cc', '$subject', '$message', '$module', '$type')";
    mysql_query($query);
    writelog( mysql_error().$query);
    if (mysql_affected_rows()>0)
        $result = mysql_insert_id();
    error_log('set_notification_message: ' .$result);
    return $result;
}

function set_reminder_key_loan($from, $to, $subject, $message, $cc, $module, $type = 'email')
{
    $result = 0;
    $message = mysql_real_escape_string($message);
    $subject = mysql_real_escape_string($subject);
    $query = "INSERT INTO notification_message (msg_from, msg_to, msg_cc, msg_subject, msg_content, module, msg_type)
                VALUES('$from', '$to', '$cc', '$subject', '$message', '$module', '$type')";
    mysql_query($query);
    writelog( mysql_error().$query);
    if (mysql_affected_rows()>0)
        $result = mysql_insert_id();
    error_log('set_notification_message: ' .$result);
    return $result;
}

function get_extra_field_list($id_category = 0, $id_page = 0)
{
    $result = array();
    $query = "SELECT * FROM extra_form_field 
                WHERE id_category = '$id_category' AND id_page = '$id_page'
                ORDER BY order_no ASC";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_assoc($rs))
        $result[] = $rec;
    return $result;
}

function get_extra_field($id = 0)
{
    $result = array();
    $query = "SELECT * FROM extra_form_field WHERE id_field = '$id'";
    $rs = mysql_query($query);
    if ($rec = mysql_fetch_assoc($rs))
        $result = $rec;
    return $result;
}

function save_extra_field($id, $name, $type, $size = 0, $desc, $cat = 0, $page = 0)
{
    $result = 0;
    $query = "REPLACE INTO extra_form_field(id_field, field_name, field_type, field_size, field_desc, id_category, id_page) 
                VALUES($id, '$name', '$type', '$size', '$desc', '$cat', '$page')";
    mysql_query($query);
    $result = mysql_affected_rows();
    if ($result > 0){
        if ($result == 1)
            $id = mysql_insert_id();
            
			// re-order the order_no
			$query = "SELECT id_field FROM extra_form_field WHERE id_category = $cat ORDER BY order_no ASC";
			$rs = mysql_query($query);
			$extra_form_field = array();
			while ($row = mysql_fetch_row($rs))
				$extra_form_field[] = $row[0];
			if (count($extra_form_field)>0){
				$order_no = 1;
				foreach($extra_form_field as $id_field){
					$query = "UPDATE extra_form_field SET order_no = $order_no WHERE id_field = $id_field";
					mysql_query($query);
					$order_no++;
				}
			}
    }
    return $result;
}


function save_extra_data($id, $data)
{
    $query = "REPLACE INTO extra_form_data(id_field, data) VALUES($id, '$data')";
    mysql_query($query);
    return mysql_affected_rows();
}

function get_extra_data_list($id_category = 0, $id_page = 0)
{
    $result = array();
    $query = "SELECT f.id_field, data 
                FROM extra_form_field f 
                LEFT JOIN extra_form_data d ON d.id_field = f.id_field 
                WHERE id_category = '$id_category' AND id_page = '$id_page'";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_assoc($rs))
        $result[$rec['id_field']] = $rec['data'];
    return $result;
}

function change_field_order($cat = 0, $id = 0, $move = null)
{
	$data = array();
	$sort = (strtolower($move) == 'down') ? 'DESC' : 'ASC';
	$query  = "SELECT * FROM extra_form_field WHERE id_category = $cat ORDER BY order_no $sort";
	$rs = mysql_query($query);
	//echo mysql_error().$query;	
	$prev = array();
	$curr = array();
	if ($rs && (mysql_num_rows($rs)>0)){
		while ($rec = mysql_fetch_assoc($rs)){
			$curr = $rec;
			if ($rec['id_field'] != $id) {
				$prev = $rec;
				continue;
			}
			break;
			//$data[$rec['id']] = $rec['order_no'];
		}
		if (!empty($prev) && !empty($curr)){
			$pk = $prev['id_field'];
			$query = "UPDATE extra_form_field SET order_no = $prev[order_no] WHERE id_field = $id";
			mysql_query($query);
			$query = "UPDATE extra_form_field SET order_no = $curr[order_no] WHERE id_field= $pk";
			mysql_query($query);
		}
	}
	
}


function count_location()
{
	$result = 0;
	$query  = "SELECT count(*) FROM location ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_location($id = 0)
{
    $rec = null;
	$query  = "SELECT * FROM location WHERE id_location = '$id'";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0)
        $rec = mysql_fetch_assoc($rs);
	return $rec;
}

function get_locations($sort = 'asc', $start = 0, $limit = 10)
{
	$query  = "SELECT * 
				FROM location 
				ORDER BY location_name $sort 
				LIMIT $start,$limit ";
	return mysql_query($query);
}
function get_store_list(){
	$data = array();
	$query = "SELECT * FROM `item_store_type`";
	$rs = mysql_query($query);
	
	while($rec = mysql_fetch_array($rs)){	
		$data[$rec['id_store']] = $rec['title'] .'-'.$rec['information'];
	}
	return $data;
}
function get_store(){
	$data = array();
	$query = "SELECT * FROM `item_store_type`";
	$rs = mysql_query($query);
	
	while($rec = mysql_fetch_array($rs)){	
		$data[] = $rec;
	}
	return $data;
}

function get_location_facility($swap = false, $lowercase = false)
{
	$data = array();        
	$query  = "SELECT l.id_location, l.location_name FROM facility_fixed_item f, location l where f.id_facility = l.id_location group by l.location_name ORDER BY l.location_name";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[1] = strtolower($rec[1]);
            $data[$rec[1]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[1];
    return $data;
}

function get_location_list($swap = false, $lowercase = false)
{
	$data = array();
    $query  = "SELECT id_location, location_name FROM location ORDER BY location_name ";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[1] = strtolower($rec[1]);
            $data[$rec[1]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[1];
    return $data;
}

function get_year_list($swap = false, $lowercase = false)
{
	$data = array();
    $query  = "SELECT year FROM student_classes GROUP BY year ORDER BY year ";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[0] = strtolower($rec[0]);
            $data[$rec[0]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[0];
    return $data;
}
/*
function get_class_list($swap = false, $lowercase = false)
{
	$data = array();
    $query  = "SELECT class FROM students GROUP BY class ORDER BY class";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[0] = strtolower($rec[0]);
            $data[$rec[0]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[0];
    return $data;
}
*/

function get_stores_list($swap = false, $lowercase = false)
{
	$data = array();
    $query  = "SELECT id_store, title FROM item_store_type` ORDER BY title ";
	$rs = mysql_query($query);
	while ($rec = mysql_fetch_row($rs))
        if ($swap){
			if ($lowercase)
				$rec[1] = strtolower($rec[1]);
            $data[$rec[1]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[1];
    return $data;
}
function set_location($_name)
{
    $result = 0;
	$query  = "INSERT INTO location(location_name, location_desc) value( '$_name', '$_name')";
	mysql_query($query);
	if (mysql_affected_rows()>0)
        $result = mysql_insert_id();
    return $result;
}

function file_read($fn)
{
    return file_get_contents($fn);
}

function file_write($fn, $data)
{
    return file_put_contents($fn, $data);
}

function writelog( $data)
{
    if (defined('LOG_PATH') && (LOG_PATH != ''))
        file_put_contents(LOG_PATH, $data, FILE_APPEND);
}

function compose_message($template_file, $data)
{
    $message = null;
    extract($data);
    $rows = file(FIGI_PATH.'/'.$template_file);
    foreach($rows as $line)
        @eval("\$message .= \"$line\";");     
    return $message;
}

function download_this($filename, $content)
{
	ob_clean();
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
}


function download_attachment($filename, $content)
{
	ob_clean();
	$mime_ct = get_mime_attachment($filename);
	header("Content-type: $mime_ct");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
}

function get_mime_attachment($filename){
	$fn_split = explode('.',$filename);
	$ct_split = count($fn_split);
	$ft = end($fn_split);
	$file_type = "";
	switch ($ft){
		case 'jpg' :
			$file_type = "image/jpeg";
		break;
		case 'pdf' :
			$file_type = "application/pdf";
		break;
		case 'docx' :
			$file_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		break;
		case 'doc' :
			$file_type = "application/msword";
		break;
		case 'xls' :
			$file_type = "application/vnd.ms-excel";
		break;
		case 'xlsx' :
			$file_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		break;
		case 'ppt' :
			$file_type = "application/vnd.ms-powerpoint";
		break;
		case 'pptx' :
			$file_type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
		break;
		case 'png' :
			$file_type = "image/png";
		break;
		case 'gif' :
			$file_type = "image/gif";
		break;
		
	}
	return $file_type;
}


function save_attachment_req($id_item,$file_name,$data_attach,$field){
	$query = "INSERT INTO ";
	switch($field){
		case 'SERVICE':
				$query .= "service_request_attachment(id_loan,filename,data)";
			break;
		case 'FAULTY':
				$query .= "fault_report_attachment(id_fault,filename,data) ";
			break;
		case 'FACILITY':
				$query .= "facility_book_attachment(id_book,filename,data) ";
			break;
	}
	$query .= " VALUES('$id_item','$file_name','$data_attach')";
	$rs = mysql_query($query);
	
}
function get_term_condition($mod, $dept)
{
	$result = null;
	$query = "SELECT message FROM term_condition WHERE module = '$mod' AND id_department = '$dept' ";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){      
        $rec = mysql_fetch_row($rs);
		$result = $rec[0];        
	}
	return $result;
}

function set_term_condition($mod, $dept, $msg)
{
    $msg = mysql_real_escape_string($msg);
	$query = "SELECT count(*) FROM term_condition WHERE module = '$mod' AND id_department = '$dept' ";
	if ($rs = mysql_query($query)){      
        $rec = mysql_fetch_row($rs);
		if ($rec[0] > 0){
            $query = "UPDATE term_condition SET message = '$msg' 
                        WHERE module = '$mod' AND id_department = '$dept' ";
        } 
        else {
            $query = "INSERT term_condition(module, id_department, message) 
                        VALUE('$mod', '$dept', '$msg')";
        }        
        mysql_query($query);
	}
}

function get_text($id)
{
	$result = null;
	$query = "SELECT `text` FROM `text` WHERE id = '$id'";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){      
        $rec = mysql_fetch_row($rs);
		$result = $rec[0];        
	}
	return $result;
}

function set_text($id, $msg)
{
    $msg = mysql_real_escape_string($msg);
	$text = get_text($id);
    if ($text!=null)
        $query = "UPDATE `text` SET  `text` = '$msg' WHERE id = '$id'";
    else 
        $query = "INSERT `text`(id, `text`) VALUE('$id', '$msg')";
    @mysql_query($query);
}

function get_department_by_category($cat)
{
	$result = array();
	$query = "SELECT id_department, department_name 
                FROM category c 
                LEFT JOIN department d ON d.id_department = c.id_department
                WHERE id_category = '$cat' ";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){      
        $rec = mysql_fetch_row($rs);
		$result = $rec;        
	}
	return $result;
}

function get_admins($dept)
{
	$result = null;
	$query = "SELECT * 
                FROM user u 
                WHERE id_department = '$dept' AND id_group = " . GRPADM;
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0)){      
        while($rec = mysql_fetch_assoc($rs))
            $result[] = $rec;        
	}
	return $result;
}

function get_admin($dept)
{
	$result = null;
	$query = "SELECT * 
                FROM user u 
                WHERE id_department = '$dept' AND id_group = " . GRPADM;
	$rs = mysql_query($query);
error_log($query);
	if ($rs && (mysql_num_rows($rs) > 0))
        $result = mysql_fetch_assoc($rs);
	return $result;
}

function get_hod($dept)
{
	$result = null;
	$query = "SELECT * 
                FROM user u 
                WHERE id_department = '$dept' AND id_group = " . GRPHOD;
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0))
        $result = mysql_fetch_assoc($rs);
	return $result;
}

function get_director()
{
	$result = null;
	$query = "SELECT * FROM user u WHERE id_group = " . GRPDIR;
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0))
        $result = mysql_fetch_assoc($rs);
	return $result;
}

function get_principle()
{
	$result = null;
	$query = "SELECT * FROM user u WHERE id_group = " . GRPPRI;
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs) > 0))
        $result = mysql_fetch_assoc($rs);
	return $result;
}

function in_range($check, $start, $end)
{
	return ($check >= $start && $check <= $end);
}

$put_nothing = false;
function putLog($prefix, $data)
{
    global $put_nothing;
    $put_nothing = false;
    if (is_array($data) && !$put_nothing){
        $fp = fopen(TMPDIR.'/log.txt', 'a+');
        foreach($data as $k => $v)
            if (is_array($v))
                foreach ($v as $sk => $sv)
                    fputs($fp, "$prefix. $k => $sk -> $sv\n");
            else
                fputs($fp, "$prefix. $k -> $v\n");
        fclose($fp);
    }
}

function get_lead_time($days = 1){
    $dow = date('N');
    $lead_time = strtotime('+' . $days . ' days');
    switch ($dow) {
    case 4:
    case 5:
    case 6: $lead_time  = strtotime('+2 days', $lead_time); break;
    case 7: $lead_time  = strtotime('+1 days', $lead_time); break;
    }
    return $lead_time;
}

function _t($k){
    global $labels;
    return $labels[$k];
}

/*
modified from http://php.net/manual/en/function.str-getcsv.php#115656
contributed by enmanuelcorvo at gmail dot com
*/
function convert_to_csv($input_array, $delimiter)
{
	return array_to_fixed_csv($input_array);

    /** open raw memory as file, no need for temp files */
    $temp_memory = fopen('php://memory', 'w');
    /** loop through array */
    foreach ($input_array as $line) {
        /** default php csv handler **/
        fputcsv($temp_memory, $line, $delimiter);
    }   
    /** rewrind the "file" with the csv lines **/
    fseek($temp_memory, 0); 
    $str = stream_get_contents($temp_memory);
    fclose($temp_memory);
    return $str;
}


function array_to_fixed_csv($input = array())
{
	$crlf = "\r\n";
	$result = null;
	$collen = 0;
	// find longest data in each field from first row if exist
	if (count($input)>0){
		$fld = array();
		//$row = array_slice($input, 0, 1);
		foreach ($input as $row){
			if (empty($fld)){ // get first row
				foreach ($row as $col)
					$fld[] = strlen($col);

			} else {
				$collen = count($row);
				for ($i=0; $i<$collen; $i++){
					$len = strlen($row[$i]);
					if ($len>$fld[$i]) { 
						$fld[$i] = $len; 
					}
				}
				
			}
		}
		// build fixed length delimited csv
		foreach ($input as $row){
			for ($i=0; $i<$collen; $i++){
				//$len = strlen($col);
				//if ($len>$fld[$i]) $fld[$i] = $len;
				$padlen = $fld[$i]+1; // plus one as delimiter
				$result .= str_pad($row[$i], $padlen, ' ');
			}
			$result .= $crlf;
		}
	}
	return $result;
}

function display_message($msg, $as_return = false)
{
    if (!empty($msg)){
        if (!is_array($msg)){
            $msg['text'] = $msg;
            $msg['title'] = 'Info';
        }
        $result = '<div class="msg info"><div id="msg_title">'.$msg['title'].'</div><div id="msg_text">'.$msg['text'].'</div></div>';
        if ($as_return) return $result;
        echo $result;
    }
}

function redirect($url, $msg = null)
{
    @ob_clean();
    echo '<script>';
    if (!empty($msg)) echo 'alert("'.$msg.'");';
    echo 'location.href = "'.$url.'"';
    echo '</script>';
    @ob_end_flush();
    exit();
    /*
    @ob_clean();
    header('Location: '.$url);
    @ob_end_flush();
    */
}

function check_numb_sms($number){
	$digits = strlen($number);
	$first_numb = substr($number, 0,1);
	if($digits == 8){
		if($first_numb == 8 || $first_numb == 9){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function get_config_sms(){
    $query = "select * from configuration where section = 'sms_school'";    
    $rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_assoc($rs);
	}
	return $rec;
}