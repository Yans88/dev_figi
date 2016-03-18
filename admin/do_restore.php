<?php
require __DIR__ . '/../common.php';

$backup_folder = ($argc > 1) ? $argv[1] : null;
$data_path = BACKUP_PATH . $backup_folder;
if (null == $backup_folder) exit;
if (!file_exists($data_path)) exit;

$tables = array();
$rs = mysql_query("show tables");
if ($rs)
    while ($row = mysql_fetch_row($rs))
        $tables[] = $row[0];

foreach ($tables as $table){
    $path = $data_path . '/' . $table . '.csv';
    if (file_exists($path)){
        $query = "DELETE FROM $table";
        mysql_query($query);
        $query  = "LOAD DATA INFILE '$path' INTO TABLE $table ";
        $query .= " FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n'";
        mysql_query($query);
    }
}

?>