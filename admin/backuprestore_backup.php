<?php

$backup_script = FIGI_PATH . '/admin/periodic_backup.php';
if (isset($_POST['save_schedule'])){
    $task_period = $_POST['period'];
    $task_time = $_POST['hour'] . ':' . $_POST['minute'] . ':00';
    $id_task = isset($_POST['currtask']) ? $_POST['currtask'] : 0;
    switch ($task_period){
        case 'monthly':
            $query = "REPLACE INTO schedule_task value('$id_task', 'Data Backup', '$backup_script', 'enable', '$task_period', '$task_time', 0, '$_POST[day_of_month]', now())";
            break;
        case 'weekly':
            $query = "REPLACE INTO schedule_task value('$id_task', 'Data Backup', '$backup_script', 'enable', '$task_period', '$_POST[day_of_week]', 0, '$task_time', now())";
            break;
        default:
            $query = "REPLACE INTO schedule_task value('$id_task', 'Data Backup', '$backup_script', 'enable', '$task_period', '$task_time', 0, 0, now())";
            break;
    }
    mysql_query($query);
} else
if (isset($_POST['backup_now'])){
    if (preg_match('/Windows/i', FIGI_OS)){
        $path = PHP_WIN.' -f ' . FIGI_PATH . '/admin/do_backup.php ';
        //run_windows_shell($path);
        $WshShell = new COM("WScript.Shell");
        $oExec = $WshShell->Run($path, 0, false);
    } else {// linux
        $path = 'php -f ' . FIGI_PATH . '/admin/do_backup.php > /dev/null &';
        run_linux_shell($path);
    }
  
    echo '<script> alert("Data backup process running in the background, need view times to be finished.")</script>';

}

$last_backup = date('Y-M-D H:i');
$current_schedule = get_current_schedule('Data Backup');
if (!empty($current_schedule)) {
    if ($current_schedule['task_period'] == 'monthly')
        $current_schedule_desc = $current_schedule['task_period'] . '(' . $current_schedule['task_mday']. ')';
    else if($current_schedule['task_period'] == 'weekly')
        $current_schedule_desc = $current_schedule['task_period'] . '(' . $current_schedule['task_dow']. ')';
    else
        $current_schedule_desc = $current_schedule['task_period'];
    $current_schedule_desc .=  ' at ' . substr($current_schedule['task_time'], 0, 5);
    $last_hour = substr($current_schedule['task_time'], 0, 2);
    $last_minute = substr($current_schedule['task_time'], 3, 2);
} else {
    $last_hour = date('H');
    $last_minute = date('i');
    $current_schedule_desc = 'no scheduled backup set';
}
?>
<script type="text/javascript">
function change_period(me)
{
    var weekly_option = $('#weekly_option');
    var monthly_option = $('#monthly_option');
    weekly_option.hide();
    monthly_option.hide();
    if (me.selectedIndex == 1) { // weekly_option
        weekly_option.show();
    } else
    if (me.selectedIndex == 2) { // monthly_option
        monthly_option.show();
    }
}
</script>

<h4>Data Backup Utility</h4>
<form method="post">
<p>
<ul style="text-align: left">Backup options: 
    <li>
        Immediate Backup. <br/>&nbsp;<br/>&nbsp;
        Click  <button name="backup_now">Backup Now</button> to start immediate backup task. 
        <br/>&nbsp;<br/>&nbsp;
    </li>
    <li>Scheduled Backup. <br/>&nbsp;<br/>
        Current schedule: <?php echo $current_schedule_desc?>
        <br/>&nbsp; <br/>
        Period: 
        <select name="period" onchange="change_period(this)">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select> &nbsp;<br/><br/>
        <div id="weekly_option" style="display: none">
           Select  Day:  
            <select name="day_of_week">
                <option value=0>Sunday</option>
                <option value=1>Monday</option>
                <option value=2>Tuesday</option>
                <option value=3>Wednesday</option>
                <option value=4>Thursday</option>
                <option value=5>Friday</option>
                <option value=6>Saturday</option>
            </select><br/>&nbsp;<br/>
        </div>
        <div id="monthly_option" style="display: none">
            Select Date: 
            <select name="day_of_month">
            <?php
            $maxdays = date('t');
            $to_date = date('d');
            for ($i=1; $i < $maxdays; $i++){
                $selected = ($i == $to_date) ? ' selected ': ' '; 
                echo '<option value='.$i. $selected .' >'.str_pad($i, 2, '0', STR_PAD_LEFT).'</option>';
            
            }
        ?>
                
            </select><br/>&nbsp;<br/>
        </div>
        Time: <input type="text" name="hour" size=3 value='<?php echo $last_hour?>'>:<input type="text" name="minute" size=3 value='<?php echo $last_minute?>'> (hh:mm)
        <button name="save_schedule">Save Backup Schedule</button>
</ul>

<input type="hidden" name="currtask" value="<?php echo $current_schedule['id_task']?>">
</form>

<br/>&nbsp;