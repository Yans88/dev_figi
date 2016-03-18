<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_msg = null;
$skipped_config = array('enable_notification');
if (isset($_POST['save'])){
    foreach($configuration['item'] as $k => $v)
        if (!in_array($k, $skipped_config) && isset($_POST[$k]))
            set_configuration('item', $k, $_POST[$k]);
	$_msg = 'Options changes has been saved!';
    $configuration = load_configuration();
}
$config = $configuration['item'];

list($nd, $nm) = @explode('-', $config['issuance_notification_date']);
$dt = mktime(0, 0, 0, $nm, $nd, date('Y'));
$md = date('t', $dt);
$dom_option = null;
for ($i=1; $i<=$md; $i++){
    $selected = ($nd == $i) ? ' selected ': null;
    $dom_option .= '<option value="' . $i .'"'.$selected.'>' . $i . '</option>';
}
$mon_option = null;
for ($i=0; $i<12; $i++){
    $selected = ($nm == $i+1) ? ' selected ': null;
    $mon_option .= '<option value="' . ($i+1) .'"'.$selected.'>' . $short_month_names[$i] . '</option>';
}

?>

<form method="post" class="loan_setting">
<input type="hidden" value="<?php echo $_tab?>" name="tab" id="tab" >
<fieldset>
<legend class="tab" id="tab_option" class="legend">Options</legend>
<br/>
<table>
<tr>
    <td align="right">Enable Email  Notification</td>
    <td align="left">
        <input type="radio" name="enable_notification_email" value="true" <?php echo ($config['enable_notification_email']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="enable_notification_email" value="false" <?php echo ($config['enable_notification_email']!='true') ? ' checked ' : null ?> >No
    </td>
</tr>
<tr>
    <td align="right" colspan=2>Date of Notification for Item Issuance  </td>
</tr>
<tr>
    <td align="right"><!--- on certain date--></td>
    <td align="left">
       <select name="lt_date" id="lt_date"><?php echo $dom_option?></select>
       <select name="lt_mon" id="lt_mon"><?php echo $mon_option?></select>
       annualy
    </td>
</tr>
</table>
</fieldset>
<fieldset class="footer">
<input type="hidden" name="issuance_notification_date" id="issuance_notification_date">
<button name="save" id="save_button">Save</button>
</fieldset>
</form>
<br/>
<br/>
<script>
<?php
    if ($_msg != null)
        echo 'alert("' . $_msg . '");';
    //echo "change_tab('$_tab');";
    
?>
$('#save_button').click(function(e){
    $('#issuance_notification_date').val($('#lt_date').val()+'-'+$('#lt_mon').val());
});
</script>
