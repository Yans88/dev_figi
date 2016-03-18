<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}
$skipped_config = array('enable_notification');
if (isset($_POST['save'])){
    foreach($configuration['deskcopy'] as $k => $v)
        if (!in_array($k, $skipped_config))
            set_configuration('deskcopy', $k, $_POST[$k]);
    $configuration = load_configuration();
}
$config = $configuration['deskcopy'];
?>

<form method="post" class="deskcopy_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Options</legend>
<br/>
<table>
<tr>
    <td align="right">Length of ISBN</td>
    <td align="left"><input type="text" name="length_of_isbn" value="<?php echo $config['length_of_isbn']?>" size=5></td>
</tr>
<tr>
    <td align="right">Length of NRIC</td>
    <td align="left"><input type="text" name="length_of_nric" value="<?php echo $config['length_of_nric']?>" size=5></td>
</tr>
<tr>
    <td align="right">Length of Serial</td>
    <td align="left"><input type="text" name="length_of_serial" value="<?php echo $config['length_of_serial']?>" size=5></td>
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
