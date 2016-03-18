<?php
if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}
$skipped_config = array('enable_notification', 'enable_notification_email', 'enable_notification_sms', 'enable_calendar');
if (isset($_POST['save'])){
    foreach($configuration['global'] as $k => $v)
        if (!in_array($k, $skipped_config))
            set_configuration('global', $k, $_POST[$k]);
    $configuration = load_configuration();
}
$config = $configuration['global'];
?>

<form method="post" class="global_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Global Options</legend>              
<br/>
<table>
<tr>
    <td align="right">Site Name</td>
    <td align="left"><input type="text" name="site_name" value="<?php echo $config['site_name']?>" size=35></td>
</tr>
<tr>
    <td align="right">System Email</td>
    <td align="left"><input type="text" name="system_email" value="<?php echo $config['system_email']?>" size=30></td>
</tr>
<tr>
    <td align="right">Sms Sender</td>
    <td align="left"><input type="text" name="sms_sender" value="<?php echo $config['sms_sender']?>" size=20></td>
</tr>
<tr>
    <td align="right">Theme / Style</td>
    <td align="left"><input type="text" name="style" value="<?php echo $config['style']?>" size=16></td>
</tr>
<tr>
    <td align="right">Displayed record/page</td>
    <td align="left"><input type="text" name="number_of_record_per_page" value="<?php echo $config['number_of_record_per_page']?>" size=6></td>
</tr>
<tr>
    <td align="right">Displayed record/page (portal)</td>
    <td align="left"><input type="text" name="number_of_record_per_page_portal" value="<?php echo $config['number_of_record_per_page_portal']?>" size=6></td>
</tr>
<tr>
    <td align="right">Currency Sign</td>
    <td align="left"><input type="text" name="currency_sign" value="<?php echo $config['currency_sign']?>" size=6></td>
</tr>
<tr>
    <td align="right">Currency Sign Position</td>
    <td align="left">
        <input type="radio" name="currency_position" value="prefix" <?php echo ($config['currency_position']=='prefix') ? ' checked ' : null ?> >Prefix
        <input type="radio" name="currency_position" value="suffix" <?php echo ($config['currency_position']=='suffix') ? ' checked ' : null ?>>Suffix
    </td>
</tr>
<tr>
    <td align="right">Backup Path</td>
    <td align="left"><input type="text" name="backup_path" value="<?php echo $config['backup_path']?>" size=26></td>
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
