<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y H:i:s');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$request = get_request($_id);
$need_approval = ($request['without_approval'] == 0);
$loaned_items = get_request_items($_id);
$returned_items = get_returned_items($_id);
$accessories = get_accessories_by_loan($_id);
$item_list = build_returned_item_list($loaned_items, $returned_items, true, false, $accessories);
//$item_list = build_returned_item_list($loaned_items, $returned_items, true);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);
$users = get_user_list();
$issue['total_loaned_items'] = count($loaned_items);
$issue['total_returned_items'] = count($returned_items);

$issue['chk'] = get_checklist($_id); 


$parent_info = get_parent_info($request['id_user']);
$issue['parent_name'] = $parent_info['father_name'];

$issue['students_loan'] = $request['students_loan'];
$issue['parent_info'] = $parent_info;

$acknowledged_by = !empty($process['acknowledged_by']) ? $users[$process['acknowledged_by']] : null;
$approved_by = !empty($process['approved_by']) ? $users[$process['approved_by']] : null;
$issued_by = !empty($process['issued_by']) ? $users[$process['issued_by']] : null;

$approve_sign = '<img src="'.get_signature($_id, 'approve').'" class="signature">';
$issue_sign = '<img src="'.get_signature($_id, 'issue').'" class="signature">';
$loan_sign = '<img src="'.get_signature($_id, 'loan').'" class="signature">';
$return_sign = '<img src="'.get_signature($_id, 'return').'" class="signature">';
$receive_sign = '<img src="'.get_signature($_id, 'receive').'" class="signature">';
$acknowledge_sign = '<img src="'.get_signature($_id, 'acknowledge').'" class="signature">';


$long_term_tag = null;
if ($request['long_term'] == 1)
    $long_term_tag  = ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';
ob_clean();
$style_path = defined('STYLE_PATH') ? STYLE_PATH : '';
$quick_loan = ($issue['quick_issue']==1) ? 'Quick' : null;
echo <<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>FiGi Productivity Tools</title>
<link rel="shortcut icon" type="image/x-icon" href="images/figiicon.ico" />
<link rel="stylesheet" href="$style_path/style_print.css" type="text/css"  />
<script>
function print_it(){
    var btn = document.getElementById("printbutton");
    if (btn){
        btn.style.display = "none";
        print();
    }
}
</script>
</head>
<body>
<div id="contentcenter" align="center" >
    <div id="printout">
        <div id="header"><img src="images/logo_print.png" /></div>

<h3>$quick_loan Returned Item (Print-Out)</h3>
<table  border=0 cellpadding=2 cellspacing=1 width=1010>
<tr valign="top">
    <td>
TEXT;
        display_request($request, true); 
        echo '<br/>';
        display_issuance($issue, true); 

echo <<<TEXTX
    </td>
</tr>
<tr><td colspan=4 height=20> &nbsp;</td></tr>
<tr><td colspan=4 >
TEXTX;

$issue = array_merge($issue, $process);
//$returns = array_merge($returns, $process);
if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

if ($need_approval){
    display_issuance_process_approval($issue, $signs, true); 
    echo '</td></tr><tr><td colspan=4 >';
    display_return_process_approval($returns, $signs, true, false, true); 
} 
else {
    display_issuance_process($issue, $signs, true); 
    echo '</td></tr><tr><td colspan=4 >&nbsp;<br>';
	$process['quick_issue'] = $issue['quick_issue'];
    display_return_process($returns, $signs, true, $process); 
}

echo <<<TEXT3
</td></tr>
<!--
<tr>
    <td colspan=2 valign="middle">
    <table cellpadding=2 cellspacing=1 >
        <tr>
            <td width="100%"><div class="note" id="issue_note" >$messages[loan_issue_note]</div></td>
        </tr>
    </table>
    </td>
</tr>
-->
<tr><td colspan=4 align="center" valign="middle">
	&nbsp;<br>

    <button id="printbutton" class="print" onclick="print_it()" >Click to Print (button will disappear)</button>
</tr>
</table>
    </div>
</div>
<br/>&nbsp;
</body>
</html>
TEXT3;

?>
