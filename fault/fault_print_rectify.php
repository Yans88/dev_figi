<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

// get request data

$rec = get_fault_request($_id);
$rectification = get_fault_rectification($_id);
$users = get_user_list();
$admin_name = !empty($users[$rectification['rectified_by']]) ? $users[$rectification['rectified_by']] : null;

$caption = 'Fault Report Under Rectification (View)';




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

<h4>Fault Report Under Rectification (Print-Out)</h4>
TEXTH;
//echo "<h4>$caption</h4>";
echo display_fault_report($rec, true);
$rec = array_merge($rec, $rectification);
$rec['rectified_by'] = $admin_name;
echo display_fault_rectified($rec, true);
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
