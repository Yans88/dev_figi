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
$request_items = get_request_items($_id);
$accessories = get_accessories_by_loan($_id);
$item_list = loan_item_list($request_items, $accessories);

$users = get_user_list();  
$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$issue['chk'] = get_checklist($_id); //12052015 add by hansen for point 23
$issue['total_loaned_items'] = count($request_items);
$issue['id_category'] = $request['id_category'];
$parent_info = get_parent_info($request['id_user']);
$issue['parent_name'] = $parent_info['father_name'];

$issue['students_loan'] = $request['students_loan'];
$issue['parent_info'] = $parent_info;

if (empty($issue['nric'])) $issue['nric'] = '-';
if (empty($issue['contact_no'])) $issue['contact_no'] = '-';
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

<br/>
<h3>$quick_loan Loaned Out Item (Print-Out Copy)</h3>
<table width="100%" class="report loan">
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

if ($need_approval){
    display_issuance_process_approval($issue, $signs, true); 
} 
else {
    display_issuance_process($issue, $signs, true); 
}
/*
if ($request['status'] == 'PARTIAL_IN'){
	$returns = get_request_return($_id);  
    display_return_process($returns, $signs, false, $process); 
	
}
*/

//add by hansen 13/05/2015 for point 23
echo '<br/>';
display_checklist($issue, true); 
// End of add by hansen 13/05/2015 for point 23

?>
</td></tr>
<?php
if ($issue['quick_issue']!=1){
?>
<tr>
    <td colspan=2 valign="middle">
    <table cellpadding=2 cellspacing=1 width="100%">
        <tr>
            <td width="100%"><div class="note" id="issue_note" ><?php echo $messages['loan_issue_note']?></div></td>
        </tr>
    </table>
    </td>
</tr>
<?php
}
?>
<tr><td colspan=4 align="center" valign="middle">
<br/>
    <div id="poweredby">Powered by FiGi Productivity</div>
</td>
</tr>
<tr><td colspan=4 align="center" valign="middle">
	<br/><br/>
    <button id="printbutton" class="print" onclick="print_it()" >Click to Print (button disappear)</button>
</tr>
</table>

    </div>
</div>
<br>
<br>
<script>
var cites = document.getElementsByTagName('cite');
for(var i = 0; i < cites.length; i++)
	cites[i].style.display = 'none';
</script>
</body>
</html>
