<?php
/*
 auth check
 */
 
if (!empty($_SESSION['figi_authenticated']) && 
    ($_SESSION['figi_authenticated'] === 'true')) {

  if (!empty($_SESSION['figi_accesslist'])) {
    define('FIGIPASS', TRUE);
    define('USERGROUP', $_SESSION['figi_usergroup']);
    define('USERNAME', $_SESSION['figi_username']);
    define('FULLNAME', $_SESSION['figi_fullname']);
    define('USERID', $_SESSION['figi_userid']);
	define('NRIC', $_SESSION['figi_nric']);
	
	if ((USERGROUP == GRPADM) || (USERGROUP == GRPHOD) || (USERGROUP == GRPASSETOWNER) || (USERGROUP == GRPASSETADMIN)){
		define('USERDEPT', $_SESSION['figi_department']);
		define('DEPTNAME', $_SESSION['figi_department_name']);
    } else {
		define('USERDEPT', 0);
		define('DEPTNAME', '');
	}
	
	if(USERGROUP == GRPSYSTEMADMIN){
		 define('GRPSYSTEMADMIN', true);
	}else{
		define('GRPSYSTEMADMIN', false);
	}
	
    if ((USERGROUP == GRPADM) && (strtolower(USERNAME) == 'admin'))
        define('SUPERADMIN', true);
    else
		define('SUPERADMIN', false);
	} else {
		define('USERDEPT', -1);
		define('USERGROUP', -1);
		define('FIGIPASS', FALSE);
        
  }
}
  
?>
