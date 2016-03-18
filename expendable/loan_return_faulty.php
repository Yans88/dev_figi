<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_item = isset($_GET['item']) ? $_GET['item'] : 0;
$_process = isset($_GET['process']) ? $_GET['process'] : null;

//$loaned_items = get_request_items($_id);
$returned_items = get_returend_items($_id);

if ($_process != null){
    if (strtolower($_process) == 'avoid'){
        $query = "UPDATE loan_return_item SET process = 'VOID' WHERE id_loan = $_id AND id_item = $_item";
        mysql_query($query);
    } else
    if (strtolower($_process) == 'machrec'){
        $query = "UPDATE loan_return_item SET process = 'DONE' WHERE id_loan = $_id AND id_item = $_item";
        mysql_query($query);
        $info = $returned_items[$_item];
        ob_clean();
        if (!empty($info['asset_no']))
            header('Location: ./?mod=machrec&sub=machine&act=info&by=asset_no&value=' . $info['asset_no']);
        elseif (!empty($info['serial_no']))
            header('Location: ./?mod=machrec&sub=machine&act=info&by=serial_no&value=' . $info['serial_no']);
        else
            header('Location: ./?mod=machrec&sub=machine&act=info&by=id_item&value=' . $info['id_item']);
        ob_end_flush();
        exit;
    }
}

        ob_clean();
        header('Location: ./?mod=loan&act=view_return&id=' . $_id);
        ob_end_flush();
        exit;

?>