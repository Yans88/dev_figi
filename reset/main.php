<?php
define('RESET_EXPIRED_TIME', 3600*2); //second
/* 
reset password idea
	- enter username/email that registered
	- search the information, found out send reset link to the email and display notification about the email
	- otherwise, say data is not found and request to entry the correct data
	- user read the email and click the link, goes to change password
	- enter new password and confirm (append captch if necessary)
	- update password in db and forward user to login page to do normal login
 */
function get_user_by_username_email($username, $email)
{
    global $encryption;
    $result = null;
    $username = $encryption->encode($username);
	$criteria = array();
	if (!empty($username)) $criteria[] = ' user_name = "'.$username.'"';
	if (!empty($email)) $criteria[] = ' user_email = "'.$email.'"';
    $query = 'SELECT * FROM user WHERE user_active = 1 AND '.implode(' AND ', $criteria);
    $res = mysql_query($query);  
	//echo mysql_error().$query;
    if ($res && (mysql_num_rows($res) > 0)){ 
        $result = mysql_fetch_assoc($res);
        $result['user_name'] = $encryption->decode($result['user_name']);
    }
	//print_r($result);
 	return $result;
}

$require_form = true;
$error  = null;

if (isset($_POST['doReset'])) {

    $rec = get_user_by_username_email($_POST['name'], $_POST['email']); 
    if ($rec != null){
		if ($rec['user_name'] == $_POST['name'] ||  $rec['user_email']==$_POST['email']){
			$requestid = 'figi|'.time().'|'.$rec['id_user'].'|'.$rec['user_name'];
			$encoded_requestid = $encryption->encode($requestid);
			$reset_url = FIGI_URL.'/?mod=reset&sub=change&req='.$encoded_requestid;
			// sending reset link via email
			$data['full_name'] = $rec['full_name'];
			$data['user_email'] = $rec['user_email'];
			$data['figi_url'] = FIGI_URL;
			$data['reset_url'] = $reset_url;
			$data['reset_title'] = 'Set new password';
			$message = compose_message('messages/password-reset-request.msg', $data);
			$from = $configuration['global']['system_email'];
			SendEmail($from, $rec['user_email'], 'Reset Password - Figi.sg', $message, null);
			// display notification
			$after_request = true;
			$require_form = false;
		} else
 	       $error = 'Username or email does not match!';
    } else
        $error = 'Username or email does not found in database!';
} else if ($_sub=='change' && !empty($_GET['req'])){

	
	$require_form = false;
	$requestid = $encryption->decode($_GET['req']);
	$requestinfo = explode('|', $requestid);
	//print_r($requestinfo);
	$now = time();
	$elapsed = time()-$requestinfo[1];
	if ($elapsed > RESET_EXPIRED_TIME){

		require 'expired.php';

	} else {
		
		if (isset($_POST['doChange'])){
			//print_r($_POST);
			save_password($requestinfo[2], $_POST['new_password']);
			require 'change_done.php';
		} else {
			require 'change.php';
		}
	}
}

if (isset($after_request)){
	require 'request_done.php';
} else if ($require_form){
	require 'request.php';
}


