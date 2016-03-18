<?php

$backup_path = BACKUP_PATH;
$month_names = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

$_restore = isset($_GET['restore']) ? $_GET['restore'] : null;
$_download = isset($_GET['download']) ? $_GET['download'] : null;
$_delete = isset($_GET['delete']) ? $_GET['delete'] : null;

if ($_restore != null){
    // do restore
    if (preg_match('/Windows/i', FIGI_OS)){
        $path = PHP_WIN.' -f ' . FIGI_PATH . '/admin/do_restore.php '. $_restore ;
        //run_windows_shell($path);
        $WshShell = new COM("WScript.Shell");
        $oExec = $WshShell->Run($path, 0, false);
    } else {// linux
        $path = 'php -f ' . FIGI_PATH . '/admin/do_restore.php '. $_restore .' > /dev/null &';
        run_linux_shell($path);
    }
    echo '<script> alert("Data restore running in the background, need view times to be finished.")</script>';
}
else if ($_download != null){
    // do download
    $zip_name = $_download . '.zip';
    $zip_temp = TMPDIR . '/' . $zip_name;
    shell_exec("cd $backup_path; zip -r $zip_temp $_download ");
    ob_clean();
    header('Content-type: application/octet-stream');
    header('Content-disposition: attachment; filename=' . $zip_name);
    readfile($zip_temp);
    ob_end_flush();
    unlink($zip_temp);
    exit;
} else if ($_delete != null){
    if (preg_match('/Windows/i', FIGI_OS)){
        shell_exec('rd /qs ' . BACKUP_PATH . '/' . $_delete );
    } else {
        shell_exec('rm -rf ' . BACKUP_PATH . '/' . $_delete );
    }
}

function get_dirs($directory)
{
    global $backup_path;
    
    $result = array();
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) { 
                if ($file != "." && $file != "..") { 
                    if (is_dir($backup_path . $file) && substr($file, 0, 12) == 'figi-backup-')
                        $result[] = $file; 
                } 
            }        
        closedir($handle); 
    }
    return $result;
}

$backup_dirs = get_dirs($backup_path);
?>
<h4>Data Restore Utility</h4>
<table width=450 id="itemedit">
<tr><td>
<table id="restore_table">
<tr>
    <th>Backup Date</th>
    <th width=260>Action</th>
</tr>
<?php
foreach ($backup_dirs as $adir){
    if (preg_match('/figi-backup-(\d{4})(\d{2})(\d{2})/', $adir, $macthes)){
        $backup_date = $macthes[1] . '-' . $month_names[intval($macthes[2])-1] . '-' . $macthes[3];
        $backup_date .=  date(' H:i', filectime($backup_path . $adir));
        echo '<tr><td>'.$backup_date.'</td>';
        echo '<td align="center"><button type="button" name=restorebtn onclick="restore(\''.$adir.'\')">Restore</button>'  ;
        echo '&nbsp;<button type="button" name=downloadbtn onclick="download(\''.$adir.'\')">Download</button>'  ;
        echo '&nbsp;<button type="button" name=deletebtn onclick="delete_backup(\''.$adir.'\')">Delete</button></td></tr>'  ;
    }
}
    
?>
</table>
</td></tr>
</table>
<style type="text/css">
#restore_table { width: 100%; border: 1px solid #eee; }
#restore_table th { border-bottom: 1px solid #eee; }
#restore_table td { border-bottom: 1px solid #eee; }
</style>
<script type="text/javascript">
function download(path)
{
    location.href='./?mod=admin&sub=backuprestore&act=restore&download=' + path;
}
function restore(path)
{
    if (confirm('Are you sure restore selected data?'))
        location.href='./?mod=admin&sub=backuprestore&act=restore&restore=' + path;
}
function delete_backup(path)
{
    if (confirm('Are you sure delete selected data?'))
        location.href='./?mod=admin&sub=backuprestore&act=restore&delete=' + path;
}
</script>