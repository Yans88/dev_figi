<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$request = get_request($_id);
if (empty($request)){
	echo '<script>alert("Loan with ID: #' . $_id . ' is not found!")</script>';
	echo '<script>location.href="./?mod=expendable&sub=loan&status=partial";</script>';
	return;
}
// $need_approval = ($request['without_approval'] == 0);
// $loaned_items = get_request_items($_id);
// $returned_items = get_returend_items($_id);
// $item_list = build_returned_item_list($loaned_items, $returned_items);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_expendable_signatures($_id);
$returns = get_request_return($_id);  
// $users = get_user_list();  
$process = array_merge($process, $returns);
$lost_report = get_lost_report($_id);


?>

<h4>Item already Returned (View)</h4>
<form method="post">
<table  class="loanview return"  cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    $returns = array_merge($returns, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

    display_issuance_process($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process($returns, $signs); 

?>
    </td>
</tr>
<?php 
if (!empty($lost_report)) { 
    echo '<tr><td>';
    display_losing_report($lost_report);
    echo '</td></tr>';
}

?>
  
</table>
<br/>&nbsp;
<?php

if (USERGROUP == GRPADM){
    $faulty_items = array();
    $lost_items = array();
    foreach($returned_items as $id => $rec){
        if (($rec['status'] == 'FAULTY') && ($rec['process'] == 'NONE')) $faulty_items[] = $rec;
        if (($rec['status'] == 'LOST') && ($rec['process'] == 'NONE')) $lost_items[] = $rec;
    }
    $faulty = count($faulty_items);
    $lost = count($lost_items);
    if ($faulty>0 || $lost>0){
        $faulty_list = null;
        if ($faulty>0)
            $faulty_list = "Following items returned in faulty conditions:<br/>";    
        foreach ($faulty_items as $rec){
            $link = '<a class="button" href="./?mod=loan&act=return_faulty&id='.$rec['id_loan'].'&process=machrec&item='.$rec['id_item'].'">machine record</a> ';
            $link .= '<a class="button" href="./?mod=loan&act=return_faulty&id='.$rec['id_loan'].'&process=avoid&item='.$rec['id_item'].'">avoid</a>';
            $faulty_list .= "$rec[asset_no] ($rec[serial_no]) $link<br/>";
        }
        if ($faulty_list != null) $faulty_list .= '<br/>';
        $lost_list = null;
        if ($lost>0)
            $lost_list = "Following items are lost:<br/>";
        foreach ($lost_items as $rec){
            $lost_list .= "$rec[asset_no] ($rec[serial_no])<br/>";
        }
        if ($lost_list != null) {
            $lost_list = '<br/><a class="button" href="./?mod=loan&act=return_lost&id='.$rec['id_loan'].'&process=report">make losing report</a> ';
            $lost_list .= '<a class="button" href="./?mod=loan&act=return_lost&id='.$rec['id_loan'].'&process=avoid">avoid all</a>';
        }
        echo <<<MSG
<div id="popup" style="display: none; text-align: center; font-size: 10pt">
    <h4 style=" color: #000;">Faulty /Lost items found!</h4>
    <p style="text-align: left">
    $faulty_list    
    $lost_list
    </p>
</div>
<script type="text/javascript" src="./js/jquery.fancybox.pack.js?v=2.0.6"></script>
<link rel="stylesheet" type="text/css" href="./style/default/jquery.fancybox.css?v=2.0.6" media="screen" />
<script>$.fancybox.open({href: '#popup', 'hideOnContentClick': false});</script>
MSG;
    }
}
?>