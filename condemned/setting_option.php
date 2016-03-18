<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$skipped_config = array('enable_notification', 'template','require_approval');
if (isset($_POST['save'])){
    foreach($configuration['condemned'] as $k => $v)
        if (!in_array($k, $skipped_config))
            set_configuration('condemned', $k, $_POST[$k]);
    echo '<script>alert("Condemn\'s Setting updated")</script>';
    $configuration = load_configuration();
}
$config = $configuration['condemned'];
?>

<form method="post" class="condemned_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Options</legend>
<br/>
<table>
<!--
<tr>
    <td align="right">Enable Notification</td>
    <td>
        <input type="radio" name="enable_notification" value="true">Yes
        <input type="radio" name="enable_notification" value="false">No
    </td>
</tr>
-->
<tr>
    <td align="right">Enable Email  Notification</td>
    <td align="left">
        <input type="radio" name="enable_notification_email" value="true" <?php echo ($config['enable_notification_email']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="enable_notification_email" value="false" <?php echo ($config['enable_notification_email']!='true') ? ' checked ' : null ?> >No
    </td>
</tr>
<tr>
    <td align="right">Enable SMS  Notification</td>
    <td align="left">
        <input type="radio" name="enable_notification_sms" value="true" <?php echo ($config['enable_notification_sms']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="enable_notification_sms" value="false" <?php echo ($config['enable_notification_sms']!='true') ? ' checked ' : null ?> >No
    </td>
</tr>
<!--
<tr>
    <td align="right">Require Approval</td>
    <td align="left">
        <input type="radio" name="require_approval" value="true" <?php echo ($config['require_approval']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="require_approval" value="false" <?php echo ($config['require_approval']!='true') ? ' checked ' : null ?> >No
    </td>
</tr>
-->
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
