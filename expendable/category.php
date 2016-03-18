<?php
  if (!defined('FIGIPASS')) exit;
  
  $_id = isset($_GET['id']) ? $_GET['id'] : 0;
  $_msg = null;
  if ($_act == null) 
    $_act = 'list';
/*    
?>
<a href="./?mod=item&sub=category&act=list">Category List</a> | 
<a href="./?mod=item&sub=category&act=edit">Create New Category</a>
<br /> 
<br />  

<?php
*/
  echo '<br/>';
  // require_once('item/item_util.php');
  
  include 'expendable/category_' . $_act . '.php';
  
?>