<?php

	if (!defined('FIGIPASS')) exit;
	$_spec = mysql_real_escape_string($_GET['spec']);
	$_part = mysql_real_escape_string($_GET['part']);

?>
<h2>Show by Calendar (Date/Time of Service Transaction)</h2>


<div id="tab_calendar" class="tabset_content history">
     <div class="leftcol" style="width: 300px; text-align: left; padding-left: 5px"><h2 style="color: #000; display: inline">Calendar</h2></div>
     
    <br>
    <br>
    <div class="portal_history" id="calendar_history">

<?php




if (empty($_spec)) $_spec = 'view_month';
$path = 'calendar/calendar_' . $_spec . '_services.php';
require ($path);


?>
  </div>
  &nbsp;
</div>

