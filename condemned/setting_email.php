<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$_msg = null;
$_dept = USERDEPT;
$_emails = (!empty($_POST['emails'])) ?  $_POST['emails'] : null;
//$_cat = (!empty($_POST['id_category'])) ?  $_POST['id_category'] : null;
$_cat = 0;
$_tab = (!empty($_GET['tab'])) ?  $_GET['tab'] : null;
if ($_tab == null)
	$_tab = (!empty($_POST['tab'])) ?  $_POST['tab'] : 'email';

$category_list = get_category_list('EQUIPMENT', $_dept);
if (empty($_cat))
    if (count($category_list) > 0) {
        $arrkeys = array_keys($category_list);
        if (count($arrkeys)>0)
            $_cat = $arrkeys[0];
    }
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
        if (save_notification_mobiles($_dept, $_cat,  'condemned', $emails))
            $_msg = 'Mobile numbers has been save!';
    } else {
        if (save_notification_emails($_dept, $_cat,  'condemned', $emails))
            $_msg = 'Emails has been save!';
    }
} 
$email_list = '--- empty list ---';
$_emails = '';
/*
$emails = get_notification_emails($_dept, $_cat, 'condemned');
foreach ($emails as $rec){
	if (!empty($_emails))
		$_emails .= ',';
	$_emails .= $rec['email'].'|'.$rec['name'];
}
*/
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
        load_notification_mobiles('<?php echo $_dept?>', '<?php echo $_cat?>', 'condemned');
    else
        load_notification_emails('<?php echo $_dept?>', '<?php echo $_cat?>', 'condemned');
}

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
.tab {display: none}
</style>
<!--
<h5>Email and Mobile Setting for Loan Notification</h5>
<a href="#" onclick="change_tab('email')">Email</a> | 
<a href="#" onclick="change_tab('mobile')">Mobile</a><br/>
-->
<form method="post" class="fault_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<input type="hidden" id="emails" name="emails" value="<?php echo $_emails?>">
<fieldset>
<legend class="tab" id="tab_email" class="legend">Email List</legend>
<legend id="tab_mobile" class="tab" class="legend">Mobile List</legend>
<!--
Category: <?php echo build_combo('id_category', $category_list, $_cat); ?> <button>Change</button>
-->
<ul id="email_list">
<?php echo $email_list?>
</ul>
<br/>
Type user's  name who will be notified on condemned processes:<br/>
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
