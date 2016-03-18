<?php

function deskcopy_display_tabsheet(){
    global $messages;
	/*
    $lead_time = (ENABLE_REQUEST_LEADTIME) ? get_lead_time() : time();
    $next_two_day_str = date('j-M-Y H:i', $lead_time);
    $day_until = strtotime('+1 day', $lead_time);
    $day_until_str = date('j-M-Y H:i', $day_until);
    $_department = (!empty($_POST['id_department'])) ? $_POST['id_department'] : 0;
    $_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
	$department_list = get_department_list();
	$dkeys = array_keys($department_list);
	$first_dkey = !empty($dkeys[0]) ? $dkeys[0] : 0;
    */
?>
<style type="text/css">
</style>
<div id="tab_deskcopy" class="tabset_content history">
    <div class="portal_history">
<?php  
    $act = !empty($_GET['act']) ? $_GET['act'] : null;
    if (defined('PORTAL') && (PORTAL == 'deskcopy'))
        switch ($act){
            case 'view': require 'deskcopy_view.php'; break;
            case 'view_issue': require 'deskcopy_view_issue.php'; break;
            case 'view_complete': require 'deskcopy_view_complete.php'; break;
            default: require 'deskcopy_history.php'; 
        }
?>
  </div>
  &nbsp;
</div>
    <script type="text/javascript">
   //department_change('deskcopy');
  </script>

  <?php
  } // deskcopy_display_tabsheet
?>