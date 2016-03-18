<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_facility = isset($_POST['id_facility']) ? $_POST['id_facility'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if (!empty($_do)){
    switch($_do){
    case 'view': include 'booking_view.php'; break;
    case 'list_day': include 'booking_list_day.php'; break;
    default: include 'booking_list_month.php';
    }
} else
    include 'booking_list_month.php';
?>