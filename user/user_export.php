<?php
$path = TMPDIR . '/' . session_id().'-export_all_user.csv';
export_csv_user($path);
if (file_exists($path)) {
	
    ob_clean();
    header("Content-type: text/x-comma-separated-values");
    header("Content-Disposition: attachment; filename=figi_all_user.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($path);
    ob_end_flush();
    exit;
}
?>


