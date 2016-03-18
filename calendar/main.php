<?php
//if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) $_sub = 'calendar';
//if ($_act == null) $_act = 'view_month';

$i_can_create= false;
$i_can_delete = false;
$i_can_update = false;	
$i_can_view= true;

//$_path = 'calendar/' . $_sub . '_' . $_act . '.php';
$_path = 'calendar/' . $_sub . '.php';
if (!file_exists($_path)) return;

?>
<div align="center" id="fum" style="width: 800px">
<h3 class="leftlink">Calendar</h3>
<div style="text-align: right">
<a href="?mod=calendar" class="button">Calendar</a>
<?php
if (defined('USERID') && USERID)
    echo '<a href="?mod=calendar&act=edit" class="button">Create Event</a>'."\n";
if (defined('SUPERADMIN') && SUPERADMIN)
    echo '<a href="?mod=calendar&sub=setting" class="button">Setting</a>'."\n";
?>
</div>
<br/>
<?php
require_once 'calendar/calendar_util.php';
include($_path);
?>
</div>
