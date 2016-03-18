<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$skipped_config = array('enable_notification');
if (isset($_POST['save'])){
    foreach($configuration['consumable'] as $k => $v)
        if (!in_array($k, $skipped_config))
            set_configuration('consumable', $k, $_POST[$k]);
    $configuration = load_configuration();
}
$config = $configuration['consumable'];
?>

<form method="post" class="consumable_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Options</legend>
<br/>
<table>
<tr>
    <td align="right">Require Signature</td>
    <td align="left">
        <input type="radio" name="require_signature" value="true" <?php echo ($config['require_signature']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="require_signature" value="false" <?php echo ($config['require_signature']!='true') ? ' checked ' : null ?> >No
    </td>
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
