<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
$_message = (!empty($_POST['message'])) ?  $_POST['message'] : null;

if  (isset($_POST['save'])){
	
    set_term_condition('loan', USERDEPT, $_message);

    $_msg = 'Term & Condition message has been save!';
} 

$message = get_term_condition('loan', USERDEPT);

?>
<form method="post" class="loan_setting">
<fieldset>
<legend class="legend">Term & Condition</legend>
<textarea id="message" name="message" rows=15 cols=65><?php echo $message?></textarea>
</fieldset>
<fieldset class="footer">
<button name="save" id="save_button">Save</button>
</fieldset>
</form>
<br/>
<br/>

