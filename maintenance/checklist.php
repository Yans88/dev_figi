<div style="color:#fff;">
<?php
if (!defined('FIGIPASS')) exit;
if ($_act == null) $_act = 'manage';

$_path = 'maintenance/checklist_' . $_act . '.php';

if (file_exists($_path)) require $_path;
else echo 'Unknown module or action!';
?>
</div>