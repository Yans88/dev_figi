<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null)	$_sub = 'key';
//if ($_act == null)	$_act = 'list';

$_path = 'keyloan/' . $_sub . '.php';

//require 'keyloan/util.php';

if (!file_exists($_path)) 
	return;

 
?>
<div align="center" id="fum">
<table width="980" border=0>
<tr>
  <td align="left" width="35%"><h3>Key Management</h3></td>
  <td align="right">
    <a class="button" href="?mod=keyloan&sub=key"><img width=16 height=16 border=0 src="images/table.png"> Key List</a>

<?php

    echo '<a class="button" href="./?mod=keyloan&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Key</a> ';
   echo '<a class="button" href="./?mod=keyloan&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Key(s)</a> ';
   echo '<a class="button" href="./?mod=keyloan&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Key</a>';

?>

	
    <a class="button" href="?mod=keyloan&sub=setting"><img width=1 height=16 border=0 src="images/space.gif">Setting</a>

  <!--
	<a class="button" href="?mod=deskcopy&sub=item">Deskcopy Item</a>
	<a class="button" href="?mod=item&sub=item">Item</a>
	<a class="button" href="?mod=item&sub=category">Category</a>
	<a class="button" href="?mod=item&sub=specification">Specification</a>
	<a class="button" href="?mod=item&sub=vendor">Vendor</a>
	<a class="button" href="?mod=item&sub=manufacturer">Manufacturer</a>
	<a class="button" href="?mod=item&sub=brand">Brand</a>
    -->
  </td>
</tr>
</table>
<?php
  include($_path);
?>
</div>
