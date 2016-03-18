<?php
if (!defined('FIGIPASS')) exit;
if (empty($_act)) $_act = 'list';

$modact_url = $submod_url.'&act='.$_act;
$_path = 'receive/receive_' . $_act . '.php';
if (!file_exists($_path)) $_act = 'list';
$_path = 'receive/receive_' . $_act . '.php';
?>


<div style="color:#fff;">
<?php

require($_path);
?>
</div>

