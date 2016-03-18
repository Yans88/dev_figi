<?php
require __DIR__ . '/../common.php';

$backup_folder = BACKUP_PATH . 'figi-backup-' . date('YmdH');

if (!file_exists($backup_folder)){
    @mkdir($backup_folder, 0777);
    @chmod($backup_folder, 0777);
}

$tables = array();
$rs = mysql_query("show tables");
if ($rs)
    while ($row = mysql_fetch_row($rs))
        $tables[] = $row[0];

foreach ($tables as $table){
    $out_file = $backup_folder . '/' . $table . '.csv';
    $query  = "SELECT * FROM  $table  INTO OUTFILE '$out_file'";
    $query .= " FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n'";
    mysql_query($query);
}

?>