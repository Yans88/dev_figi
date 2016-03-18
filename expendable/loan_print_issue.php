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
$need_approval = ($request['without_approval'] == 0);
// $request_items = get_request_items($_id);
$no = 1;
foreach ($request_items as $item)
  $items[] = ($no++) . ". $item[asset_no] ($item[serial_no])";
$item_list = implode("<br/>\r\n", $items);
  
$users = get_user_list();  
$approved_by = $users[$request['approved_by']];
$admin_name = $users[USERID];
$process = get_request_process($_id);
$signs = get_expendable_signatures($_id);


$issue_sign = '<img src="'.get_expendable_signature($_id, 'issue').'" width=200 height=80>';
$loan_sign = '<img src="'.get_expendable_signature($_id, 'loan').'" width=200 height=80>';
$issue = get_request_out($_id);


$long_term_tag = null;
if ($request['long_term'] == 1)
    $long_term_tag  = ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';

ob_clean();
$style_path = defined('STYLE_PATH') ? STYLE_PATH : '';
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

<br/><br/>
<h3>Loaned Out Item (Print-Out Copy)</h3>
<table cellpadding=2 cellspacing=1 class="report loan">
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
if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];
    display_issuance_process($issue, $signs, true); 


?>
</td></tr>
<tr><td colspan=4 align="center" valign="middle">
<br/>
    <div class="note">
    <?php
        echo get_term_condition('loan', USERDEPT);
    ?>
    </div>
    <div id="poweredby">Powered by FiGi Productivity</div>
</tr>
<tr><td colspan=4 align="center" valign="middle">
	<br/><br/>
    <button id="printbutton" class="print" onclick="print_it()" >Click to Print (button disappear)</button>
</tr>
</table>

    </div>
</div>
</body>
</html>