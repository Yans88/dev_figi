<div id="tab_service" class="tabset_content history">
     <div class="leftcol" style="width: 300px; text-align: left; padding-left: 5px"><h2 style="color: #000; display: inline">Service Request History</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=service">Service Request Form</a> | 
        <a href="./?mod=portal&sub=history&portal=service">Service Request History</a>
     </div>
    <br>
    <br>
    <div class="portal_history" id="history_service" >
<?php  
    $act = !empty($_GET['act']) ? $_GET['act'] : null;
    $need_back = true;
    if (defined('PORTAL') && (PORTAL == 'service'))
        switch ($act){
            case 'view': require 'service_view.php'; break;
            case 'view_issue': require 'service_view_issue.php'; break;
            case 'view_complete': require 'service_view_complete.php'; break;
            default: require 'service_history.php'; $need_back = false;
        }
        if ($need_back)
            echo '<div class="footnav"><a href="./?mod=portal&sub=history&portal=service">Back to Service Request List</a></div><br>';
            
?>
  </div>
  &nbsp;
</div>
