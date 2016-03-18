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
$returned_items = get_returned_items($_id);
$accessories = get_accessories_by_loan($_id);
$returned_item_list = build_returned_item_list($loaned_items, $returned_items, false, false, $accessories);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);  
$users = get_user_list();  
$process['quick_issue'] = $issue['quick_issue'];
$lost_report = get_lost_report($_id);
$issue['total_loaned_items'] = count($loaned_items);
$issue['total_returned_items'] = count($returned_items);
$item_list = loan_item_list($loaned_items, $accessories);

?>

<form method="post">
<table  class="itemlist loan return middle"  cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    //$returns = array_merge($returns, $process);
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
    display_return_process($returns, $signs, false, $process); 
}
?>
    </td>
</tr>
<?php 
if (!empty($lost_report)) { 
    echo '<tr><td>';
    display_losing_report($lost_report);
    echo '</td></tr>';
}

if ( (USERGROUP == GRPHOD) && $i_can_delete && $need_approval) {
    echo '<tr><td align="right">';
    echo '<button type="button" onclick="location.href=\'./?mod=loan&sub=loan&act=acknowledge&id='.$_id.'\'"><img src="images/notes.png" > Acknowledge Returned Items</button>';
    echo '</td></tr>';
} 
?>
 <tr>
    <td colspan=2 valign="middle">
    <table cellpadding=2 cellspacing=1 >
        <tr>
            <td width="100%"><div class="note" id="issue_note" ><?php echo $messages['loan_issue_note']?></div></td>
        </tr>
<?php

if ( (USERGROUP == GRPADM) && !SUPERADMIN && (USERDEPT==$request['id_department'])) {
	if ($issue['total_loaned_items']>$issue['total_returned_items']){
		if ($issue['quick_issue']==1)
			echo '<tr><td align="right"><a class="button" href="./?mod=loan&sub=quick_loan_return&id='.$_id.'">Return Rest Items</a> &nbsp; </td></tr>';
		else
			echo '<tr><td align="right"><a class="button" href="./?mod=loan&sub=loan&act=return&id='.$_id.'">Return Rest Items</a> &nbsp; </td></tr>';
	}
}
?>
    </table>
    </td>
</tr>
</table>
<div class="space5-top"></div>
<div class="right">
    <a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a>
</div>
<script>
function  print_preview(){
  window.open("./?mod=loan&sub=loan&act=print_complete&id=<?php echo $_id?>", 'print_preview');
}
</script>

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
<script>$.fancybox.open({href: '#popup', 'hideOnContentClick': false; width: 200px});</script>
MSG;
    }
}
?>
