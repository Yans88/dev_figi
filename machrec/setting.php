<?php
if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_emails = (!empty($_POST['emails'])) ?  $_POST['emails'] : null;
$_dept = (!empty($_POST['id_department'])) ?  $_POST['id_department'] : null;

if  (isset($_POST['save'])){
	$emails = array();
	$recs = explode(',', $_emails);
	foreach($recs as $line){
		$line = trim($line);
		if (!empty($line)) {
			list($email, $name) = explode('|', $line);
			$emails[] = array('email' => $email, 'name' => $name);
		}
	}
    if (save_notification_emails($_dept, 'loan', $emails))
        $_msg = 'Emails has been save!';
} 
$email_list = '--- empty list ---';
$emails = get_notification_emails($_dept, 'loan');
$_emails = '';
foreach ($emails as $rec){
	if (!empty($_emails))
		$_emails .= ',';
	$_emails .= $rec['email'].'|'.$rec['name'];
}
?>
<script src="./js/email_setting.js"></script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>

<h4>Email Setting for Loan Notification</h4>
<form method="post" class="loan_setting">
<input type="hidden" id="emails" name="emails" value="<?php echo $_emails?>">
<fieldset>
<legend class="legend">Email List by Department</legend>
Department: <?php echo build_department_combo($_dept); ?> <button>Change</button>
<ul id="email_list">
<?php echo $email_list?>
</ul>
<br/>
Type user's  name who will be notified on loan processes:<br/>
    <input type="text" id="edit_email" name="edit_email"
	onKeyUp="suggest(this, this.value);" onBlur="fill('edit_email', this.value);" >
  <button type="button" id="add_button" onclick="add_email()">Add</button>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</fieldset>
<fieldset class="footer">
<button name="save" id="save_button">Save</button>
</fieldset>
</form>
<br/>
<br/>
<script>
<?php
    if ($_msg != null)
        echo 'alert("' . $_msg . '");';
?>
display_list($("#emails").val());
</script>
