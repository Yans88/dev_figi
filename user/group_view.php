<?php
if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_group = null;
$user_privileges = array();
$all_privileges = get_privilege_list();
$query = "SELECT group_name FROM `group` WHERE id_group = $_id";
$rs = mysql_query($query);
echo mysql_error();
if (mysql_num_rows($rs)>0) {
    $rec = mysql_fetch_row($rs);
    $_group = $rec[0];
}
  
?>
<script>
function toggle_check_all(frm){
  var inputs = frm.getElementsByTagName('input');
  
  for(i=0;i<inputs.length;i++){
      if (inputs[i].type == 'checkbox') 
        inputs[i].checked = ! inputs[i].checked;
  }
}

function delete_group(id){
  ok = confirm('Are you sure want to delete group "' + document.forms[0].group.value + '"?');
  if (ok) 
    location.href = './?mod=user&sub=group&act=del&id='+id;
  
}

</script>
<br/>

<form method="POST">
<table class="userlist" cellpadding=4 cellspacing=1 width=600>
<tr><th colspan=2><h2 style="color: #000">View Group Detail</h2></th></tr>
<tr class="normal">
  <th align="left" width="100px">Group Name</th>
  <td align="left" style="font-weigth: bold"><?php echo $_group?></td>
</tr>
<tr class="alt" valign="top">
  <th align="left" >Access Rights</th>
  <td >
<?php

$user_access = get_page_access($_id);

$query = "SELECT id_page, page_name, module_name 
            FROM page p 
            LEFT JOIN module m ON p.id_module = m.id_module 
            ORDER BY module_name";
$rs = mysql_query($query);   
$result = array();
while ($rec = mysql_fetch_assoc($rs))
    $result[$rec['module_name']][$rec['id_page']] = $rec['page_name'];
echo '<table width="100%" cellspacing=2 cellpadding=2 class="userlist">';
echo '<tr><th colspan=2>Module</th><th>View</th><th>Create</th><th>Update</th><th>Delete</th></tr>';
$maintenance_checklist = Maintenance_Checklist ? 'Maintenance Checklist' : 0;
$common = array($maintenance_checklist=>$maintenance_checklist);
foreach($result as $mod_name => $pages){
    $counter = 0;
    $rows = count($pages);
    echo '<tr><td colspan=7 height=10></td></tr>';
    echo '<tr><th align="left" colspan=2>'.$mod_name.'</th></tr>';
	$checkmark = '<img src="images/check-mark.png">';
	$crossmark = '<img src="images/cross-mark.png">';
    foreach ($pages as $pid => $pname){
        $class = ($counter % 2 == 0) ? 'class="alt"' : 'class="normal"';
        $checks[0] = isset($user_access[$pid][0]) && ($user_access[$pid][0] == '1') ? $checkmark : $crossmark;
        $checks[1] = isset($user_access[$pid][1]) && ($user_access[$pid][1] == '1') ? $checkmark : $crossmark;
        $checks[2] = isset($user_access[$pid][2]) && ($user_access[$pid][2] == '1') ? $checkmark : $crossmark;
        $checks[3] = isset($user_access[$pid][3]) && ($user_access[$pid][3] == '1') ? $checkmark : $crossmark;
		//if(array_search($pname, $common)){
			echo '<tr '.$class.'><td width=80></td><td class="underline" align="left">' . $pname . '</td>';
			echo '<td class="underline center">'.$checks[0].'</td>';
			echo '<td class="underline center">'.$checks[1].'</td>';
			echo '<td class="underline center">'.$checks[2].'</td>';
			echo '<td class="underline center">'.$checks[3].'</td>';
			echo '</tr>';
			$counter++;
		//}
		
       
        
    }
}
echo '</table>';

?>
</td></tr>
<tr>
  <th colspan=2>
  <!--
    <input type="submit" name="save" value="Save Group">
    <input type="button" name="del" value="Delete Group" onclick="delete_group(<?php echo $_id?>)">
	-->
	<input type="button" value="Back to Group List" onclick="location.href='?mod=user&sub=group'">
  </th>
</tr>

</table>
<?php
  if ($_msg != null)
    echo '<br/><div class="error">' . $_msg . '</div>';
    
?>