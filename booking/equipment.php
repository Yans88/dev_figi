<?php
if (!defined('FIGIPASS')) exit;
$item_id = isset($_GET['item']) ? $_GET['item'] : 0;
$_msg = null;
if ($_act == null) $_act = 'view';

$_path = 'booking/equipment_' . $_act . '.php';

require $_path;
