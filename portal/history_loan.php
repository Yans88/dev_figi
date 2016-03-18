<?php
require_once('./loan/loan_util.php');
?>

<div id="tab_loan" class="tabset_content">
     <div class="leftcol" style="width: 300px; text-align: left; padding-left: 5px"><h2 style="color: #000; display: inline">Loan Request History</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=loan">Loan Request Form</a> | 
        <a href="./?mod=portal&sub=history&portal=loan">Loan Request History</a>
     </div>
    <br>
    <br>
    <div class="portal_history" id="history_loan">
<?php  
    $need_back = true;
    $act = !empty($_GET['act']) ? $_GET['act'] : null;
    if (defined('PORTAL') && (PORTAL == 'loan')){
        switch ($act){
            case 'view': $path =  'loan_view.php'; break;
            case 'view_lost': $path =  'loan_view_lost.php'; break;
            case 'view_issue': $path =  'loan_view_issue.php'; break;
            case 'view_return': $path =  'loan_view_return.php'; break; 
            case 'view_complete': $path =  'loan_view_complete.php'; break;
            default: $path = 'loan_history.php'; $need_back = false;
        }
        require $path;
        if ($need_back)
            echo '<div class="footnav"><a href="./?mod=portal&sub=history&portal=loan">Back to Loan History List</a></div><br>';

    }
?>
  </div>
  &nbsp;
</div>