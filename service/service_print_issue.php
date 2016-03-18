<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

// get request data

$rec = get_service_request($_id);  
$id_page = get_page_id_by_name('service');
$extra_data = get_extra_form($rec['id_category'], $id_page);


$users = get_user_list();  
$approved_by = isset($users[$rec['approved_by']]) ? $users[$rec['approved_by']] : null;
$approve_sign = get_signature($_id, 'approve');
$admin_name = $rec['issued_by_name'];

/*
$issue_sign = '<img class="signature" src="'.get_signature($_id, 'issue').'" width=200 height=80>';
$query = "SELECT li.*, date_format(loan_date, '$format_date_only') as loan_date, 
          date_format(return_date, '$format_date_only') as return_date, department_name 
          FROM loan_out li 
          LEFT JOIN department d ON d.id_department = li.id_department 
          WHERE id_loan = $_id";
$rs = mysql_query($query);
//echo mysql_error().$query;
$issue=array();
if (mysql_num_rows($rs)>0){
    $issue = mysql_fetch_assoc($rs);
}
*/
$caption = 'Service Request already Completed(View)';

ob_clean();
$style_path = defined('STYLE_PATH') ? STYLE_PATH : 'default';
echo <<<TEXTH
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

<h3>Completed Service (Print-Out)</h3>
TEXTH;
echo "<h4><br>$caption</h4>";
$rec['extra_data'] = $extra_data;
echo display_service_request($rec, true);

if (REQUIRE_SERVICE_APPROVAL){
	$rec['signature'] = $approve_sign;
	$rec['approved_by'] = $approved_by;
	echo '&nbsp;<br/>';
	echo display_service_approval($rec, true);
}
$rec['signature'] = get_signature($_id, 'issue');//$issue_sign;
$rec['issued_by'] = $rec['issued_by_name'];
echo '&nbsp;<br/>';
echo display_service_issuance($rec, true);
if ($rec['status']=='COMPLETED'){
	echo '&nbsp;<br/>';
	$rec['returned_by'] = $rec['returned_by_name'];
	$rec['signature'] = get_signature($_id, 'return');//$issue_sign;
	display_service_completion($rec, true);
}
echo <<<TEXT3
</table>
<div>
	&nbsp;<br/>
    <button id="printbutton" class="print" onclick="print_it()" >Click to Print (button will disappear)</button>
</div>
    </div>
</div>
<br/>&nbsp;
</body>
</html>
TEXT3;

?>
