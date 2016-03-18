<?php
//require '../util.php';
require '../common.php';
require '../authcheck.php';
//require '../message.php';
require_once '../user/user_util.php';

$_mod = !empty($_GET['mod']) ? strtolower($_GET['mod']) : 'portal';
$_sub = !empty($_GET['sub']) ? strtolower($_GET['sub']) : null;
$_portal = !empty($_GET['portal']) ? strtolower($_GET['portal']) : 'calendar';
define('PORTAL', $_portal);


if (!empty($_portal)){
    $path = $_mod .'_'. $_portal . '.php';
    
    if (file_exists($path)) {
        $style_path = '../'.STYLE_PATH;
        
        echo  <<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="X-UA-Compatible" content="chrome=1" />
<link rel="stylesheet" type="text/css" href="{$style_path}figi.css" media="screen" />
<link rel="stylesheet" type="text/css" href="{$style_path}anytimec.css" />
<link rel='stylesheet' type='text/css' href='../style/default/jquery-ui-1.8.13.custom.css'/>	
<script type="text/javascript" src="../js/jquery/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="../js/figi.js"></script>
<script type="text/javascript" src="../js/anytimec.js"></script>
<script type="text/javascript" src="../js/moment.min.js"></script>
<script type='text/javascript' src='../js/jquery/jquery-ui-1.8.13.custom.min.js'></script>		
</head>
<body>

TEXT;
/*
        if ($_portal != 'calendar'){
        
            echo '<div><a class="" href="./ajax.php?mod=portal&portal='.ucfirst($_portal).'">Create '.ucfirst($_portal).' Request</a> | ';
            echo '<a class="" href="./ajax.php?mod=history&portal='.$_portal.'">'.ucfirst($_portal).' Request History</a></div>';
            echo '<div class="clear"></div>';
        }
*/
        echo '<div id="contentcenter" align="center">';
        require_once $path;
        echo '</div></body></html>';
    }
    exit;
}

?>
