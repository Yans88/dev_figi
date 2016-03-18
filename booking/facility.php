<?php
if (!defined('FIGIPASS')) exit;

$_path = $_mod . '/' . $_sub . '_' . $_act . '.php';
if (!file_exists($_path)) $_act = 'list';
$_path = $_mod . '/' . $_sub . '_' . $_act . '.php';

$modact_url = $submod_url."&act=$_act";

$current_url = $modact_url;
$i_can_create = true;
$i_can_delete = true;
require $_path;


