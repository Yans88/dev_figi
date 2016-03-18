<?php
  if (!defined('FIGIPASS')) exit;
  
  $_id = isset($_GET['id']) ? $_GET['id'] : 0;
  $_msg = null;
  if ($_act == null) 
    $_act = 'list';
/*
?>
<a href="./?mod=item&sub=manufacturer&act=list">Manufacturer List</a> | 
<a href="./?mod=item&sub=manufacturer&act=edit">Create New Manufacturer</a>
<br /> 
<br />  

<?php
*/
  echo '<br/>';
  include 'item/manufacturer_' . $_act . '.php';
?>