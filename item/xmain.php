<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) 	$_sub = 'item';
if ($_act == null)	$_act = 'list';
$_path = 'item/' . $_sub . '.php';

include 'item/item_util.php';
if (!file_exists($_path)) 
	return;
 
$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));

</tr>
 <?php } // mod=item ?>
</table>
<?php
  include($_path);
?>
</div>
<br>&nbsp;<br>
<br>&nbsp;<br>