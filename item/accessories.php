<?php
if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
if ($_act == null) $_act = 'list';

echo '<div id="content_accessories" class="content">';
include 'item/accessories_' . $_act . '.php';
echo '</div>';
?>
