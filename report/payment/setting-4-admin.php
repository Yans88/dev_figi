<?php
if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_emails = (!empty($_POST['emails'])) ?  $_POST['emails'] : null;

if  (isset($_POST['save'])){
    if (set_configuration('payment', 'email_for_notification', $_emails))
        $_msg = 'Emails has been save!';
} 
$email_list = '--- no email specified ---';
$_emails = get_configuration('payment', 'email_for_notification');
?>
<script src="./js/email_setting.js"></script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>

<h4>Email Setting for Payment Notification</h4>
<form method="post" class="payment_setting">
<input type="hidden" id="emails" name="emails" value="<?php echo $_emails?>">
<fieldset>
<legend class="legend">Email List</legend>
<ul id="email_list">
<?php echo $email_list?>
</ul>
<br/>
Type user's  name who will be notified on payment processes:<br/>
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
