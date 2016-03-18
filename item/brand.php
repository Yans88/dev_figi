<?php
if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
if ($_act == null) 
    $_act = 'list';
    
    /*
if (!SUPERADMIN){
?>
<a class="button" href="./?mod=item&sub=brand&act=list">Brand List</a>
<a class="button" href="./?mod=item&sub=brand&act=edit">Add New Brand</a>
<?php
} // not superadmin
*/
  echo '<br/>';
  include 'item/brand_' . $_act . '.php';
?>