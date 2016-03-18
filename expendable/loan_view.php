<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$request = get_request($_id);
$need_approval = REQUIRE_LOAN_APPROVAL;//($request['without_approval'] == 0);

if ($request['status'] == APPROVED) 
	$caption = 'Approved Loan Request (In-Process)';
elseif ($request['status'] == REJECTED) 
	$caption = 'Rejected Loan Request (View)';
else {
    if (REQUIRE_LOAN_APPROVAL)
        $caption = 'Request Pending Approval (View)';
    else
        $caption = 'Pending Loan Request (View)';
}

?>

<h4><br/><?php echo $caption?></h4>
<table cellpadding=3 cellspacing=1 class="loanview request" >
<tr valign="top"><td><?php display_request($request);?></td></tr>

<?php
//print_r($request);

if (($request['status'] == APPROVED) ){ // request created as approval type
    $process = get_request_process($_id);
    $signs = get_signatures($_id);
    echo '<tr><td>';
    display_approval($process, $signs, false);
    echo '</td></tr>';

} // approval type & approved

if ($request['status'] == REJECTED) {
    $process = get_request_rejection($_id);
    $signs = get_signatures($_id);
    echo '<tr><td>';
    display_rejection($process, false);
    echo '</td></tr>';

} //rejected
?>
</table>
    <br/>
	<div class="loanview footer">
<?php

if (!SUPERADMIN) { // non superadmin
  if ((USERGROUP == GRPHOD) && ($need_approval && ($request['status'] == PENDING || $request['status'] == REJECTED))) { 
?>
	<div class="loanview footer">
        <a class="button" onclick="return approve()" href="javascript:void(0)">Approve</a>
	</div><br/>
  
<?php
  } // hod can only approve or reject

if (USERGROUP == GRPADM) {
    if ((!$need_approval && ($request['status'] == PENDING)) || ($need_approval && ($request['status'] == APPROVED))){
        if (!$need_approval)
            echo '<a class="button" href="./?mod=loan&sub=loan&act=unapprove&id='.$_id.'">Reject</a>';
        /*
            echo '<input type="image" value="Reject" src="images/reject.png" 
        onclick="location.href=\'./?mod=loan&sub=loan&act=unapprove&id='.$_id.'\'">';
        <input type="image" value="Notify" src="images/notify.png" 
    onclick="location.href='./?mod=loan&sub=loan&act=notify_ready&id=<?php echo $_id?>'">
        <input type="image" value="Manage" src="images/manage.png" 
        onclick="location.href='./?mod=loan&sub=loan&act=issue&id=<?php echo $_id?>'">
        */
?>
        <a class="button" href="./?mod=loan&sub=loan&act=notify_ready&id=<?php echo $_id?>">Notify</a>
        <a class="button" href="./?mod=loan&sub=loan&act=issue&id=<?php echo $_id?>">Manage</a>

<?php
        }
    }
} // non-superadmin
?>
</div><br/>
<script type="text/javascript">
function approve(){
    location.href = "./?mod=loan&sub=loan&act=approve&id=<?php echo $_id?>" ;
    return false;
}
</script>
&nbsp;<br/>