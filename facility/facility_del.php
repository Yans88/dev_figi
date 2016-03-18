<?php
if (!defined('FIGIPASS')) exit;
$_id = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;

$_msg = null;

$facility = get_facility($_id);
$name = $facility['facility_no'];

// delete facility
mysql_query("DELETE FROM facility WHERE id_facility = $_id");

if (mysql_affected_rows() > 0) {
    user_log(LOG_DELETE, 'Delete facility '. $name. '(ID:'. $_id.')');
	
	// delete time sheet for the facility
    mysql_query("DELETE FROM facility_schedule WHERE id_facility = $_id");
    
	if (defined('USE_NEW_BOOKING') && USE_NEW_BOOKING){
		mysql_query("DELETE FROM facility_period_map WHERE id_facility = $_id");
	}

    $msg = 'Delete facility \"'.$name.'\" successfull!';
} else 
   $msg = 'Facility \"'.$name.'\" fail to delete!';

redirect('./?mod=facility&sub=facility', $msg);
/*
ob_clean();
header('Location: ./?mod=facility&sub=facility');
ob_flush();
ob_end_flush();
exit;
*/
?>
<br/>
<a href="./?mod=user"> Back to User List</a> 
