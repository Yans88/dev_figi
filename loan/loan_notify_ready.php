<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$request = get_request($_id);
//print_r($request);
send_loan_item_ready($request);
?>

<h4>Notification Sending</h4>
<br/>
<p>
Email notification has been sent to requester.<br/> 
Telling that Equipment ready for collection.<br/>
Click <a href="./?mod=loan&sub=loan&act=issue&id=<?php echo $_id?>" class="button">here</a> to manage loan-out issue.
</p>