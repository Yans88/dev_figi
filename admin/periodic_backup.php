<?php

require __DIR__ . '/../util.php';
require __DIR__ . '/../common.php';


$backup_folder = BACKUP_PATH . 'figi-backup-' . date('YmdH');

$do_backup = false;
$query  = "SELECT st.*, time_to_sec(timediff(time(now()),task_time)) delta_time,
            DAYOFWEEK(NOW()) dow, DAYOFMONTH(NOW()) mday 
            FROM  schedule_task st WHERE task_name = 'Data Backup'";
$rs = mysql_query($query);


if (mysql_num_rows($rs) > 0){
    $rec = mysql_fetch_assoc($rs);
	echo 'Found schedule backup: ' . $rec['task_period']; 
    switch ($rec['task_period']){
    case 'daily' : $do_backup = ($rec['delta_time'] < 60);  break;    
    case 'weekly' : $do_backup = ($rec['task_dow'] == $rec['dow']) && ($rec['delta_time'] < 60); echo "($rec[task_dow])"; break;   
    case 'monthly' : $do_backup = ($rec['task_mday'] == $rec['mday']) && ($rec['delta_time'] < 60); echo "($rec[task_mday])"; break;  
    }
	echo $rec['task_time'];
    if ($do_backup){
		echo ". Schedule match, run backup routine.....\n";
        shell_exec('php -f '.FIGI_PATH . '/admin/do_backup.php > /dev/null &');
    } else
		echo ". No schedule match, ignore.\n";
}
?>
