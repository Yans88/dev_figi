<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$receive = receive_view($_id);
?>

<h4 class="center">Receive detail(view)</h4>
<table cellpadding=3 cellspacing=1 class="itemlist loan request" width="70%" >
<tr valign="top"><td><?php display_receive($receive) ;?></td></tr>
</table>
<br/>
<div style="text-align:center;">
<a class="button" href="./?mod=receive">Back to receive list</a>
</div>