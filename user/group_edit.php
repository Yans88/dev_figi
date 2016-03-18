<?php
if (!defined('FIGIPASS')) exit;
$_id = (isset($_GET['id'])&& !empty($_GET['id'])) ? $_GET['id'] : 0;

if (!SUPERADMIN) {
    include 'unauthorized.php';
    return;
}
if ($_id == GRPADM) return;
  
  $_msg = null;
  
	if (isset($_POST['save'])) {
        //print_r($_POST);
		$group=strtoupper($_POST['group']);
        $saved = 0;
		if ($_id == 0) { // add new group
            $query = "INSERT INTO `group`(group_name) values('$group')";
            mysql_query($query);
            $_id = mysql_insert_id();
            if ($_id > 0) {
                user_log(LOG_CREATE, 'Create group '. $_name. '(ID:'. $_id.')');
                $saved++;
            }
        } else { // update
            $query = "UPDATE `group` SET group_name = '$group' WHERE id_group=$_id";
            mysql_query($query); 
            
            if (mysql_affected_rows()>0) {
                user_log(LOG_UPDATE, 'Update group '. $_name. '(ID:'. $_id.')');
                $saved++;    
            }
		}
		// delete old entries of access
        $query = "DELETE FROM access WHERE id_group = $_id";
        mysql_query($query);
        $view_access = (isset($_POST['pview'])) ? $_POST['pview'] : array();
        $create_access = (isset($_POST['pcreate'])) ? $_POST['pcreate'] : array();
        $update_access = (isset($_POST['pupdate'])) ? $_POST['pupdate'] : array();
        $delete_access = (isset($_POST['pdelete'])) ? $_POST['pdelete'] : array();
        $pages = get_page_list();
        //print_r($pages);
        $access = array();
        foreach ($pages as $pid => $pname){            
            $access[0] = isset($view_access[$pid]) ? '1' : '0';
            $access[1] = isset($create_access[$pid]) ? '1' : '0';
            $access[2] = isset($update_access[$pid]) ? '1' : '0';
            $access[3] = isset($delete_access[$pid]) ? '1' : '0';
            $page_access = implode(',', $access);
            $query = "INSERT INTO access(id_page, id_group, access_view, access_create, access_update, access_delete)
                        VALUES($pid, $_id, $page_access)";
            mysql_query($query);
            //echo mysql_error().$query;
        }
		
		if ($saved > 0)
			$_msg = 'Group "'.$group.'" is saved!';
		else
			$_msg = 'Fail to save group "'.$group.'"!';
	}
	
	$_caption = 'Create New Group';
	$_group = null;
	$user_privileges = array();
	$all_privileges = get_privilege_list();
	$query = "SELECT group_name FROM `group` WHERE id_group = $_id";
	$rs = mysql_query($query);
    //echo mysql_error().$query;  
	if (mysql_num_rows($rs)>0) {
		$rec = mysql_fetch_row($rs);
		$_group = $rec[0];
		$_caption = 'Edit Existig Group';
		$user_privileges = get_privilege_list($_id);
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
<tr><th colspan=2><h2 style="color: #000"><?php echo $_caption?></h2></th></tr>
<tr class="normal">
  <th align="left" width="100px">Group Name</th>
  <td align="left"><input type="text" name="group" value="<?php echo $_group?>" size=30></td>
 </tr>
<tr class="normal"><td colspan=2>
<table width="100%" cellspacing=1 cellpadding=2 class="userlist">
<tr><th colspan=2>Module</th><th>View</th><th>Create</th><th>Update</th><th>Delete</th></tr>

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
foreach($result as $mod_name => $pages){
    $rows = count($pages);
    echo '<tr><td colspan=7 height=10></td></tr>';
    echo '<tr><th align="left" colspan=2>'.$mod_name.'</th></tr>';
    $counter = 0;
    foreach ($pages as $pid => $pname){
        $class = ($counter % 2 == 0) ? 'class="alt"' : 'class="normal"';
        $checks[0] = isset($user_access[$pid][0]) && ($user_access[$pid][0] == '1') ? ' checked ' : '';
        $checks[1] = isset($user_access[$pid][1]) && ($user_access[$pid][1] == '1') ? ' checked ' : '';
        $checks[2] = isset($user_access[$pid][2]) && ($user_access[$pid][2] == '1') ? ' checked ' : '';
        $checks[3] = isset($user_access[$pid][3]) && ($user_access[$pid][3] == '1') ? ' checked ' : '';
        echo '<tr '.$class.'><td width=80></td><td class="underline" align="left">' . $pname . '</td>';
        echo '<td class="underline center"><input type="checkbox" name="pview['.$pid.']"'.$checks[0].'></td>';
        echo '<td class="underline center"><input type="checkbox" name="pcreate['.$pid.']"'.$checks[1].'></td>';
        echo '<td class="underline center"><input type="checkbox" name="pupdate['.$pid.']"'.$checks[2].'></td>';
        echo '<td class="underline center"><input type="checkbox" name="pdelete['.$pid.']"'.$checks[3].'></td>';
        echo '</tr>';
        $counter++;
    }
}

?>
</table>
</td></tr>
<tr>
  <th colspan=2>
    <input type="submit" name="save" value="Save Group">
    <input type="button" name="del" value="Delete Group" onclick="delete_group(<?php echo $_id?>)" <?php echo ($_id <= 0) ? 'disabled' : ''?> >
	<input type="button" value="Cancel" onclick="location.href='?mod=user&sub=group'">
  </th>
</tr>
<tr><td colspan=2 style="background-color: grey; padding-left: 10px">
<p>
<ul style="color: black; margin-left: 10px;">note: Meaning of Privileges of Loan & Service
<li>create : user can submit / make a request</li>
<li>view : user able to see their own requests</li>
<li>update: user able to make loan issue and render the service</li>
<li>delete: user able to approve a request</li>
</ul>
</p>
</td></tr>
</table>
<?php
  if ($_msg != null)
    echo '<br/><div class="error">' . $_msg . '</div>';
    
?>