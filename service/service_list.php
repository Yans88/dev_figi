<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_status = 'pending';
if (empty($_GET['status'])){
    if (REQUIRE_SERVICE_APPROVAL){
        if (USERGROUP == GRPHOD)
            $_status = 'pending';
        else if (USERGROUP == GRPADM)
            $_status = 'approved';
    } 
} else
    $_status = $_GET['status'];

if (REQUIRE_SERVICE_APPROVAL){
    
	if (USERGROUP == GRPHOD) 
		echo '<a href="./?mod=service&sub=service&status=pending">Request Pending Approval</a> | ';
	else
	    echo '<a href="./?mod=service&sub=service&status=pending">Submitted Requests</a> | ';

	echo <<<LINK2
	<a href="./?mod=service&sub=service&status=approved">Approved Requests</a> | 
	<a href="./?mod=service&sub=service&status=issued">Requests In-Progress</a> | 
	<a href="./?mod=service&sub=service&status=completed">Completed Requests</a> | 
	<a href="./?mod=service&sub=service&status=unapproved">Rejected Requests</a> 
LINK2;
} else {
	echo <<<LINK3
	<a href="./?mod=service&sub=service&status=pending">Submitted Requests</a> | 
	<a href="./?mod=service&sub=service&status=issued">Requests In-Progress</a> | 
	<a href="./?mod=service&sub=service&status=completed">Completed Requests</a> | 
	<a href="./?mod=service&sub=service&status=unapproved">Rejected Requests</a> 
LINK3;
}
include 'service/service_list_'. $_status . '.php';
?>
<br/>&nbsp;
