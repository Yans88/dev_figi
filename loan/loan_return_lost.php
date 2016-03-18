<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_item = isset($_GET['item']) ? $_GET['item'] : 0;
$_process = isset($_GET['process']) ? $_GET['process'] : null;

//$loaned_items = get_request_items($_id);
$returned_items = get_returned_items($_id);

if ($_process != null){
    if (strtolower($_process) == 'avoid'){
        $item_ids = array();
        foreach ($returned_items as $id => $rec){
            if ($rec['status'] == 'LOST' && $rec['process'] == 'NONE')
                $item_ids[] = $rec['id_item'];
        }
        $query = "UPDATE loan_return_item SET process = 'VOID' 
                    WHERE id_loan = $_id AND id_item IN  (" . implode(',', $item_ids) . ")";
        mysql_query($query);
    } else
    if (strtolower($_process) == 'report'){
        //$query = "UPDATE loan_return_item SET process = 'DONE' WHERE id_loan = $_id AND id_item = $_item";
        //mysql_query($query);
        
        ob_clean();
        header('Location: ./?mod=loan&act=lost&id=' . $_id);
        ob_end_flush();
        exit;
    }
}

        ob_clean();
        header('Location: ./?mod=loan&act=view_return&id=' . $_id);
        ob_end_flush();
        exit;

?>