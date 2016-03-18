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
    foreach($configuration['calendar'] as $k => $v)
        if (!in_array($k, $skipped_config))
            set_configuration('calendar', $k, $_POST[$k]);
    $configuration = load_configuration();
}
$config = $configuration['calendar'];
?>

<form method="post" class="calendar_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Options</legend>
<br/>
<table>
<tr>
    <td align="right">Available Time Range for Events:</td>
    <td align="left"></td>
</tr>
<tr>
    <td align="right">Start: <input type="text" name="time_start" value="<?php echo $config['time_start']?>" size=5></td>
    <td align="left">Finish: <input type="text" name="time_finish" value="<?php echo $config['time_finish']?>" size=5></td>
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
