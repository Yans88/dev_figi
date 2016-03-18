<?php
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$dow_list = array(0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat');
$dom_list = range(1,31);
$hour_list = range(0, 23);
$type_list = array('daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly');

$_msg = null;
$skipped_config = array('enable_notification', 'require_approval');
if (isset($_POST['save'])){
    foreach($configuration['loan'] as $k => $v)
        if (!in_array($k, $skipped_config) && isset($_POST[$k]))
            set_configuration('loan', $k, $_POST[$k]);
	$_msg = 'Options changes has been saved!';
    $configuration = load_configuration();
}
$config = $configuration['loan'];
list($nd, $nm) = @explode('-', $config['long_term_notification_date']);
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

<form method="post" >
<div style="width: 600px;" class="middle">
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
<tr>
    <td align="right">Enable Return Alert</td>
    <td align="left">
        <input type="radio" name="return_alert" value="true" <?php echo ($config['return_alert']=='true') ? ' checked ' : null ?> >Yes
        <input type="radio" name="return_alert" value="false" <?php echo ($config['return_alert']!='true') ? ' checked ' : null ?>>No
    </td>
</tr>
<tr>
    <td align="right"> Return Alert Lead (days)</td>
    <td align="left"><input type="text" name="return_alert_lead_days" value="<?php echo $config['return_alert_lead_days']?>" size=5></td>
</tr>
<tr>
    <td align="right">Request Lead Time (working days)</td>
    <td align="left"><input type="text" name="request_leadtime" value="<?php echo $config['request_leadtime']?>" size=5></td>
</tr>
<tr>
    <td align="right" colspan=2>Date of Notification for Long Term Loan Physical Check</td>
</tr>
<tr>
    <td align="right"><!--- on certain date--></td>
    <td align="left">
       <select name="lt_date" id="lt_date"><?php echo $dom_option?></select>
       <select name="lt_mon" id="lt_mon"><?php echo $mon_option?></select>
       annualy
    </td>
</tr>
<tr>
    <td align="center" colspan=2>Loan Return Due Date Report</td>
</tr>
<tr>
    <td align="right">Frequency</td>
    <td align="left"><?php echo build_combo("report_frequency_type", $type_list, $config['report_frequency_type'])?></td>
</tr>
<tr id="dom_row" style="display: none">
    <td align="right">Execution Date</td>
    <td align="left"><?php echo build_combo("report_frequency_day", $dom_list, $config['report_frequency_day'])?></td>
</tr>
<tr id="dow_row" style="display: none">
    <td align="right">Execution Day</td>
    <td align="left"><?php echo build_combo("report_frequency_day", $dow_list, $config['report_frequency_day'])?></td>
</tr>
<tr>
    <td align="right">Execution Hour</td>
    <td align="left"><?php echo build_combo("report_frequency_hour", $hour_list, $config['report_frequency_hour'])?></td>
</tr>

<!--
<tr>
    <td align="right">- on a period after loan</td>
    <td align="left"><input type="text" name="long_term_confirm_period" value="<?php echo $config['long_term_confirm_period']?>" size=5> month(s)</td>
</tr>
-->

</table>
</fieldset>
<fieldset class="footer">
<input type="hidden" name="long_term_notification_date" id="long_term_notification_date">
<button name="save" id="save_button">Save</button>
</fieldset>
</div>
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
    $('#long_term_notification_date').val($('#lt_date').val()+'-'+$('#lt_mon').val());
});

$('select[name=report_frequency_type]').change(function(){
	var type = $(this).find('option:selected').val();
	$('#dow_row').hide();
	$('#dom_row').hide();
	if (type=='monthly') $('#dom_row').show();
	else if (type=='weekly') $('#dow_row').show();

});

$('select[name=report_frequency_type]').trigger('change');
</script>
