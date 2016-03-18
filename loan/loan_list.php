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
if (REQUIRE_LOAN_APPROVAL)
	echo <<<APPROVALLINK
<a href="./?mod=loan&sub=loan&status=pending" id="link_pending">Pending Loan Requests</a> | 
<a href="./?mod=loan&sub=loan&status=approved" id="link_approved">Approved Request (In-Process)</a> | 
<a href="./?mod=loan&sub=loan&status=unapproved" id="link_unapproved">Rejected Loan Requests</a> | 
<a href="./?mod=loan&sub=loan&status=loaned" id="link_loaned">Requests already Loaned Out</a> <br>
<a href="./?mod=loan&sub=loan&status=returned" id="link_returned">Loaned Items Pending Acknowlegement</a> | 
<a href="./?mod=loan&sub=loan&status=completed" id="link_completed">Acknowledgement Returned Items </a>
APPROVALLINK;
else
	echo <<<NONAPPROVALLINK
<a href="./?mod=loan&sub=loan&status=pending" id="link_pending">Pending Loan Requests</a> | 
<a href="./?mod=loan&sub=loan&status=unapproved" id="link_unapproved">Rejected Loan Requests</a> | 
<a href="./?mod=loan&sub=loan&status=loaned" id="link_loaned">Requests already Loaned Out</a> | 
<a href="./?mod=loan&sub=loan&status=completed" id="link_completed">Returned Items </a>
NONAPPROVALLINK;

echo '</div><br/>';

include 'loan/loan_list_'. $_status . '.php';
?>

<script>
var status = '<?php echo $_status?>';
//$('#link_'+status).css('background', '#687');
with($('#link_'+status)){
	css('color', '#fff');
	//css('background', '#0E2B19');
	css('padding', '2px 5px');
	css('text-decoration', 'none');
}
</script>
