<?php
if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
if ($_act == null) 
    $_act = 'list';
include 'item/specification_' . $_act . '.php';
?>