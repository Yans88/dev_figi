<?php
if (!defined('FIGIPASS')) exit;
$_id = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;

$_msg = null;

$query = 'SELECT time_start, time_end, id_facility FROM facility_schedule WHERE id_time = ' . $_id;
$rs = mysql_query($query);
$rec = mysql_fetch_array($rs);
$name = $rec['time_start'] . ' - ' . $rec['time_end'] ;
  
$query = "DELETE FROM facility_schedule WHERE id_time= " . $_id;
mysql_query($query);
if (mysql_affected_rows() > 0) {
  echo '<br/><div class="error">Delete a periode "'.$name.'" successfull!<br></div>';
} else 
echo '<br/><div class="error">Period "'.$name.'" fail to delete!<br></div>';
 
$_id = $rec['id_facility'];
ob_clean();
header('Location: ./?mod=facility&sub=schedule&act=view&id=' . $_id);
ob_flush();
ob_end_flush();
exit;
?>