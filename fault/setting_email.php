<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
$_emails = (!empty($_POST['emails'])) ?  $_POST['emails'] : null;
$_tab = (!empty($_GET['tab'])) ?  $_GET['tab'] : null;
$_cat = (!empty($_POST['id_category'])) ?  $_POST['id_category'] : null;
if ($_tab == null)
	$_tab = (!empty($_POST['tab'])) ?  $_POST['tab'] : 'email';

$category_list = get_fault_category_list($_dept);

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
    if ($_tab == 'mobile'){
        if (save_notification_mobiles($_dept, $_cat,  'fault', $emails))
            $_msg = 'Mobile numbers has been save!';
    } else {
        if (save_notification_emails($_dept, $_cat,  'fault', $emails))
            $_msg = 'Emails has been save!';
    }
} 
$email_list = '--- empty list ---';
$_emails = '';
?>
<script type="text/javascript" src="./js/email_setting.js"></script>
<script type="text/javascript">
function change_tab(id)
{
    $('#tab_email').hide();
    $('#tab_mobile').hide();
    $('#tab_'+id).show();
    $('#tab').val(id);
    if (id=='mobile')
        load_notification_mobiles('<?php echo "$_dept', '$_cat'"?>, 'fault');
    else
        load_notification_emails('<?php echo "$_dept', '$_cat'"?>, 'fault');
}

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
.tab {display: none}
</style>
<!--
<h4>Email Setting for Fault Reporting Notification</h4>
<a href="#" onclick="change_tab('email')">Email</a> | 
<a href="#" onclick="change_tab('mobile')">Mobile</a><br/>
-->
<form method="post" class="fault_setting middle">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<input type="hidden" id="emails" name="emails" value="<?php echo $_emails?>">
<fieldset>
<legend class="tab" id="tab_email" class="legend">Email List</legend>
<legend id="tab_mobile" class="tab" class="legend">Mobile List</legend>
Category: <?php echo build_combo('id_category', $category_list, $_cat); ?> <button>Change</button>
<ul id="email_list">
<?php echo $email_list?>
</ul>
<br/>
Type user's  name who will be notified on fault processes:<br/>
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
    echo "change_tab('$_tab');";
?>
</script>
