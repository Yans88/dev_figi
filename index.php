<?php
/*
index
*/


//include 'message.php';
include 'common.php';
include 'user/user_util.php';

$_mod = (!empty($_GET['mod'])) ? $_GET['mod'] : null;
$_sub = (!empty($_GET['sub'])) ? $_GET['sub'] : null;
$_act = (!empty($_GET['act'])) ? $_GET['act'] : null;

$public_pages = array('calendar', 'reset');

include 'authcheck.php';
require_once 'calendar/calendar_util.php';

if (defined('FIGIPASS'))
    $accessible_modules = get_user_module_list(USERGROUP);


include 'header.php';

if (defined('FIGIPASS') && ($_mod == null)) {
        if (USERGROUP == GRPTEA || USERGROUP == GRPTEADM){

			if(ALTERNATE_PORTAL_STATUS == 'enable'){ 
				header("location: ./?mod=portal&portal=alternate"); 
			} else {
				header("location: ./?mod=portal&portal=loan");
			}

        } if (USERGROUP == GRPSTUDENT){
			include 'portal/main.php';
		} if (USERGROUP == GRPSYSTEMADMIN or item_is_homepage){
			header("location: ?mod=item"); 
		}else
            include 'main.php';
} else {
	/*
	if ('reset'==$_mod)
		include 'reset.php';
	else
	*/
   	include 'login.php';
}


    if ($_mod != null) {		
        if (defined('FIGIPASS') || in_array($_mod, $public_pages)) {    
            $_path = $_mod . '/main.php';
            if (file_exists($_path)) {
                include ($_path);
	
            } else 
                $_mod = null;
        } else {
            echo '<script>location.href="./";</script>';
        }
    }  

include 'footer.php';
ob_end_flush();
?>
