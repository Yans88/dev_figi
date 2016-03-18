<?php
  if (!defined('FIGIPASS')) exit;
  
  $_id = isset($_GET['id']) ? $_GET['id'] : 0;
  $_msg = null;
  if ($_act == null) 
    $_act = 'list';
    echo '<br/>';
  include 'item/mobilecart_' . $_act . '.php';
?>