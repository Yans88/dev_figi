<?php
if (!defined('FIGIPASS')) exit;

if ($_sub != null) 
	$_sub = 'user';

$_path = 'user/user_' . $_sub . '.php';

if (!file_exists($_path)) 
	$_path = 'user/user_list.php';
	
?>
<div align="center" id="usercontent">
<table width="100%" border=0>
<tr>
  <td align="left" width="40%"><h3>USER MANAGEMENT</h3></td>
  <td align="right">
      <!--a href="./">Home</a> | -->
	<a href="?mod=user&act=list">Users</a> | 
	<a href="?mod=user&sub=group">Groups</a>
	<!--| <a href="?mod=user&act=search">Search</a>-->
  </td>
</tr>
</table>
<?php
  include($_path);
?>
</div>
