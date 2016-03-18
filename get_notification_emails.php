<?php

include 'util.php';
include 'common.php';

$_dept = !empty($_POST['dept']) ? $_POST['dept'] : 0;
$_cat = !empty($_POST['cat']) ? $_POST['cat'] : 0;
$_mod = !empty($_POST['mod']) ? $_POST['mod'] : null;

$result = null;
$emails = get_notification_emails($_dept, $_cat, $_mod);
$rows = array();
foreach ($emails as $rec)
    $rows[] = $rec['email'] . '|' . $rec['name'];
if (count($rows)>0)
    $result = implode(',', $rows);
    
echo $result;
?>