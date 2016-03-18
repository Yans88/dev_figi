<?php
//if (!defined('FIGIPASS')) exit;
require_once('./fault/fault_util.php');
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$rec = get_fault_request($_id);

//echo '<div id="itemedit">';
if ($rec['fault_status']==FAULT_PROGRESS){
  require_once('./fault/fault_view_progress.php');
} 
else if ($rec['fault_status']==FAULT_COMPLETED){
  require_once('./fault/fault_view_complete.php');
} 
else {
  require_once('./fault/fault_view.php');
}
//</div>
?>
