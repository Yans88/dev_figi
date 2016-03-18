<?php
header("access-control-allow-origin: *");
include '../common.php';
include '../user/user_util.php';
$logged = false;
//$params = json_decode(file_get_content('php://input'));
if (!empty($_POST['username'])&&!empty($_POST['password'])) {
    
    $rec = authenticate($_POST['username'], $_POST['password']); 
    if ($rec != null)
        $logged = $rec['id_user'] > 0;
	if($logged){
		$response['figi_authenticated'] = 'true';
		$response['figi_username'] = $rec['user_name'];
		$response['figi_fullname'] = $rec['full_name'];
		$response['figi_userid'] = $rec['id_user'];
		$response['figi_usergroup'] = $rec['id_group'];
		$response['figi_department'] = $rec['id_department'];
		$username = $_POST['username'];
		user_log(LOG_ACCESS, 'log-in as '.$username);
	}else{
		$response['figi_authenticated'] = 'false';
	}
	
}

echo json_encode($response);
?>