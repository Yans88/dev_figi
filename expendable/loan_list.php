<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

$_status = 'pending';

if (empty($_GET['status'])){
    if (REQUIRE_LOAN_APPROVAL){
        if (USERGROUP == GRPHOD)
            $_status = 'pending';
        else if (USERGROUP == GRPADM)
            $_status = 'approved';
    } 
} else
    $_status = $_GET['status'];


/*
approval type
	- pending
	- approved
	- rejected
	- loaned
	- returned
	- acknowledged

non-approval
	- pending
	- rejected
	- loaned
	- returned

*/

echo '<div class="section-menu">';

	echo <<<NONAPPROVALLINK
<a href="./?mod=expendable&sub=loan&status=pending">Pending Loan Requests</a> | 

<a href="./?mod=expendable&sub=loan&status=loaned">Requests already Loaned Out</a> | 
<a href="./?mod=expendable&sub=loan&status=partial">Partial Loan</a> | 
<a href="./?mod=expendable&sub=loan&status=completed">Completed Items </a><br/>
NONAPPROVALLINK;

echo '</div>';

include 'expendable/loan_list_'. $_status . '.php';

?>
