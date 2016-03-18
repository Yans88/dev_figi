<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = 'ERR';

if (!empty($_id)){
    
    $request = get_condemned_issue($_id);
    if (empty($request)){
        echo '<script type="text/javascript">';
        echo 'alert("Data with id:# ' . $_id . ' is not found!");';
        echo 'location.href="./?mod=condemned";';
        echo '</script>';
        return;
    } else {
    
    }
}
?>