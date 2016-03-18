<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
if (empty($_GET['status'])){
    if (USERGROUP == GRPHOD)
        $_status = 'pending';
    else if (USERGROUP == GRPADM)
        $_status = 'approved';
} else
    $_status = $_GET['status'];


    echo<<<LINK1
<a href="./?mod=service&sub=report&act=department">Report Service by Department</a> | 
<a href="./?mod=service&sub=report&act=category">Report Service by Category</a>
<br/> 

LINK1;

//include 'service/report_'. $_status . '.php';
?>