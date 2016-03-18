<?php

include 'util.php';
include 'common.php';

$_dept = !empty($_POST['dept']) ? $_POST['dept'] : 0;
$_cat = !empty($_POST['cat']) ? $_POST['cat'] : 0;
$_mod = !empty($_POST['mod']) ? $_POST['mod'] : null;

$result = null;
$mobiles = get_notification_mobiles($_dept, $_cat, $_mod);
$rows = array();
foreach ($mobiles as $rec)
    $rows[] = $rec['name'] . '|' . $rec['mobile'];
if (count($rows)>0)
    $result = implode(',', $rows);
    
echo $result;
?>