<?php
if (!defined('FIGIPASS')) exit;
if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}
  
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
if ($_act == null) 
    $_act = 'list';
  /*
?>
<br/>
<a href="./?mod=user&sub=department&act=list">Department List</a> | 
<a href="./?mod=user&sub=department&act=edit">Create New Department</a>
<br /> 
<br />  

<?php
*/
  echo '<br/>';
  include 'user/department_' . $_act . '.php';
?>