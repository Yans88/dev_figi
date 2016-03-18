<?php
if (!defined('FIGIPASS')) exit;
/*
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
*/
$skipped_config = array('enable_notification');
if (isset($_POST['save'])){
    foreach($configuration['keyloan'] as $k => $v)
        if (!in_array($k, $skipped_config))
            set_configuration('keyloan', $k, $_POST[$k]);
    $configuration = load_configuration();
}
$config = $configuration['keyloan'];
?>

<form method="post" class="deskcopy_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Setting</legend>
<br/>
<table>
<tr>
    <td align="right">Enable SMS Reminder</td>
    <td align="left">
		<input type="radio" name="enable_sms_reminder" value="true" <?php echo ($config['enable_sms_reminder']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="enable_sms_reminder" value="false" <?php echo ($config['enable_sms_reminder']!='true') ? ' checked ' : null ?> >No
	</td>
</tr>
<tr>
    <td align="right">Enable Email Reminder</td>
    <td align="left">
		<input type="radio" name="enable_email_reminder" value="true" <?php echo ($config['enable_email_reminder']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="enable_email_reminder" value="false" <?php echo ($config['enable_email_reminder']!='true') ? ' checked ' : null ?> >No
	</td>
</tr>
<tr>
    <td align="right">Returned item</td>
    <td align="left"><input type="text" name="return_hours" value="<?php echo $config['return_hours']?>" size=5> Hours</td>
</tr>

</table>
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
