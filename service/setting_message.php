<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
//$_term_condition = (!empty($_POST['term_condition'])) ?  $_POST['term_condition'] : null;
$_submit_note = (!empty($_POST['service_submit_note'])) ?  $_POST['service_submit_note'] : null;
//$_issue_note = (!empty($_POST['service_issue_note'])) ?  $_POST['service_issue_note'] : null;

if  (isset($_POST['save'])){
	
    //set_term_condition('service', USERDEPT, $_term_condition);
    set_text('service_submit_note', $_submit_note);
    //set_text('service_issue_note', $_issue_note);

    $_msg = 'Text messages has been saved!';
} 

//$term_condition = get_term_condition('service', USERDEPT);
$service_submit_note = get_text('service_submit_note');
//$service_issue_note = get_text('service_issue_note');

?>
<form method="post" class="loan_setting">
<fieldset>
<legend class="legend">Notes for Service Requestor</legend>
<textarea id="service_submit_note" name="service_submit_note" rows=5 cols=65><?php echo $service_submit_note?></textarea>
</fieldset>
<fieldset class="footer">
<button name="save" id="save_button">Save</button>
</fieldset>
</form>
<br/>
<br/>

