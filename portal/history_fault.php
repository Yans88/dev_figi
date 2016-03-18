<div id="tab_fault" class="tabset_content history">
     <div class="leftcol" style="width: 300px; text-align: left; padding-left: 5px"><h2 style="color: #000; display: inline">Fault Report History</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=fault">Fault Report Form</a> | 
        <a href="./?mod=portal&sub=history&portal=fault">Fault Report History</a>
     </div>
    <br>
    <br>
    <div class="portal_history" id="fault_history">
<?php  
    $transaction_prefix = TRX_PREFIX_FAULT;

    $need_back = true;
    $act = !empty($_GET['act']) ? $_GET['act'] : null;
    if (defined('PORTAL') && (PORTAL == 'fault'))
        switch ($act){
            case 'view': $path =  'fault_view.php'; break;
            case 'view_issue': $path =  'fault_view_issue.php'; break;
            case 'view_complete': $path =  'fault_view_complete.php'; break;
            default: $path =  'fault_history.php';  $need_back = false;
        }
        require $path;		
		if ($need_back)
            echo '<div class="footnav"><a href="./?mod=portal&sub=history&portal=fault">Back to Loan History List</a></div><br>';

?>
  </div>
  &nbsp;
</div>
