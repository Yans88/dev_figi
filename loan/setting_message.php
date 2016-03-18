<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
$_term_condition = (!empty($_POST['term_condition'])) ?  $_POST['term_condition'] : null;
$_submit_note = (!empty($_POST['loan_submit_note'])) ?  $_POST['loan_submit_note'] : null;
$_issue_note = (!empty($_POST['loan_issue_note'])) ?  $_POST['loan_issue_note'] : null;

if  (isset($_POST['save'])){
	
    set_term_condition('loan', USERDEPT, $_term_condition);
    set_text('loan_submit_note', $_submit_note);
    set_text('loan_issue_note', $_issue_note);

    $_msg = 'Text messages has been saved!';
} 

$term_condition = get_term_condition('loan', USERDEPT);
$loan_submit_note = get_text('loan_submit_note');
$loan_issue_note = get_text('loan_issue_note');

?>
<div class="middle" style="width: 600px">
<form method="post" >
<fieldset>
<legend class="legend">Notes for Loan Requestor</legend>
<textarea id="loan_submit_note" name="loan_submit_note" rows=7 cols=75><?php echo $loan_submit_note?></textarea>
</fieldset>
<fieldset>
<legend class="legend">Notes Loan for Issuance</legend>
<textarea id="loan_issue_note" name="loan_issue_note" rows=7 cols=75><?php echo $loan_issue_note?></textarea>
</fieldset>
<fieldset>
<legend class="legend">Term & Condition</legend>
<textarea id="term_condition" name="term_condition" rows=10 cols=75><?php echo $term_condition?></textarea>
</fieldset>
<fieldset class="footer">
<button name="save" id="save_button">Save</button>
</fieldset>
</form>
</div>
<br/>
<br/>

