<?php
include "../common.php";

$rs = mysql_query("select * from status");
while ($rec = mysql_fetch_assoc($rs))
  $statuses[$rec['status_name']] = $rec['id_status'];

$rs = mysql_query("select * from item_status");
while ($rec = mysql_fetch_assoc($rs))
  $data[$rec['id_item']] = $rec['status'];

foreach ($statuses as $k => $v){
  mysql_query("UPDATE item_status SET status = $v where status = '$k'");
}

?>
