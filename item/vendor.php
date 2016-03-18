<?php
  if (!defined('FIGIPASS')) exit;
  
  $_id = isset($_GET['id']) ? $_GET['id'] : 0;
  $_msg = null;
  if ($_act == null) 
    $_act = 'list';
/*
?>
<a href="./?mod=item&sub=vendor&act=list">Vendor List</a> | 
<a href="./?mod=item&sub=vendor&act=edit">Create New Vendor</a>
<br/>
<br /> 

<?php
*/
  echo '<br/>';
  include 'item/vendor_' . $_act . '.php';
?>