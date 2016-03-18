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
	echo '<script>location.href="./?mod=loan&status=returned";</script>';
	return;
}
$need_approval = ($request['without_approval'] == 0);
$loaned_items = get_request_items($_id);
$returned_items = get_returend_items($_id);
$item_list = build_returned_item_list($loaned_items, $returned_items);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);  
$users = get_user_list();  
$process = array_merge($process, $returns);

$accessories = null;
$accessories_list = get_accessories_by_loan($_id);
if (!empty($accessories_list)){
    $accessories = '<ol style="margin:0;padding-left:15px;padding-top:0 ">';
    foreach($accessories_list as $idacc => $acc)
        $accessories .= '<li>'.$acc . '</li>';
    $accessories .= '</ol>';
} else
    $accessories .= 'n/a';



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

if ($need_approval){
    display_issuance_process_approval($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process_approval($returns, $signs); 
} 
else {
    display_issuance_process($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process($returns, $signs); 
}
?>
    </td>
</tr>
<tr>    
  <td colspan=2 align="right">
<?php
if ( (USERGROUP == GRPHOD) && $i_can_delete && $need_approval) {
    echo '<button type="button" onclick="location.href=\'./?mod=loan&sub=loan&act=acknowledge&id='.$_id.'\'"><img src="images/notes.png" > Acknowledge Returned Items</button>';
} 
?>
  </td>
</tr>
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
        foreach ($faulty_items as $rec){
            $link = '<a class="button" href="./?mod=loan&act=return_faulty&id='.$rec['id_loan'].'&process=machrec&item='.$rec['id_loan'].'">machine record</a> ';
            $link .= '<a class="button" href="./?mod=loan&act=return_faulty&id='.$rec['id_loan'].'&process=avoid&item='.$rec['id_item'].'">avoid</a>';
            $faulty_list .= "$rec[asset_no] ($rec[serial_no]) $link<br/>";
        }
        $lost_list = null;
        foreach ($lost_items as $rec){
            $link = '<a class="button" href="./?mod=loan&act=return_lost&id='.$rec['id_loan'].'&process=report&item='.$rec['id_loan'].'">report loosing</a> ';
            $link .= '<a class="button" href="./?mod=loan&act=return_lost&id='.$rec['id_loan'].'&process=avoid&item='.$rec['id_item'].'">avoid</a>';
            $lost_list .= "$rec[asset_no] ($rec[serial_no]) $link<br/>";
        }
        echo <<<MSG
<div id="popup" style="display: none; text-align: center; font-size: 10pt">
    <h4 style=" color: #000;">Faulty /Lost items found!</h4>
    <p style="text-align: left">
    Following items returned in faulty conditions:<br/>
    $faulty_list<br/>
    Following items are lost:<br/>
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