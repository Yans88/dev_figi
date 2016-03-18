<?php
$_msg = null;
if (!defined('FIGIPASS')) exit;
$_id = defined('USERID') ? USERID : 0;
  
if (isset($_POST['save']) && ($_id > 0)) {
    $query = "UPDATE user SET full_name = '$_POST[name]', contact_no = '$_POST[contact]',
                user_email = '$_POST[email]', nric = '$_POST[nric]' ";
    if (!empty($_POST['user_pass']))
        $query .= ', user_pass=md5("'.$_POST['user_pass'].'") ';
    $query .= " WHERE id_user=" . $_id;
    mysql_query($query);
    //echo mysql_error();
    if (mysql_affected_rows() > 0){        
        user_log(LOG_UPDATE, 'Update user account '. $_POST['name']. '(ID:'. $_id.')');
        $_msg = 'User account updated!';
        echo "<script>
                alert('$_msg');
                location.href='./?mod=user&act=account';
                </script>";
        exit;
    }
}

$rec = get_user($_id);

?>  
<script>	
function save_user(){
	var frm = document.forms[0];
	if (frm.name.value == ''){
		alert('Full name is mandatory, you must fill in!');
		frm.name.focus();
		return false;
	}
        frm.save.value="Save";
        frm.submit();
   
}
</script>	
<br/><br/>
<form method="POST">
	<input type=hidden name="save" value="">
	<table width=400 class="userlist" cellpadding=3 cellspacing=1>	
	<tr><th colspan=2><h2 style="color: #000">Update User Acccount</h2></th></tr>
	<tr class="normal">
    <td align="left">Username</td>
    <td align="left"><input type="text" readonly value="<?php echo $rec['user_name']?>" <?php if ($_id != 0) echo 'readonly'?>  size=30></td>
	</tr>
	<tr class="alt" valign="top">
    <td align="left">Password</td>
    <td align="left"><input type="password" name="user_pass" size=30>
<?php
	if ($_id > 0)
		echo "</br><small><cite>*leave it blank if you don't want to change the password</cite></small>";
?>
    </td>
	</tr>
	<tr class="normal">
    <td align="left">NRIC</td>
    <td align="left"><input type="text" name="nric" value="<?php echo $rec['nric']?>" size=30></td>
	</tr>
	<tr class="alt">
    <td align="left" width=100>Name</td>
    <td align="left" ><input type="text" name="name" value="<?php echo $rec['full_name']?>" size=30></td>
	</tr>
	<tr class="normal">
    <td align="left">Contact No.</td>
    <td align="left"><input type="text" name="contact" value="<?php echo $rec['contact_no']?>" size=30></td>
	</tr>
	<tr class="alt">
    <td align="left">Email</td>
    <td align="left"><input type="text" name="email" value='<?php echo $rec['user_email']?>' size=30></td>
	</tr>
	<tr class="normal">
    <td align="left">Group</td>
      <td align="left"><input type="text" readonly value="<?php echo $rec['group_name']?>" size=30></td>
   </tr>
	<tr class="alt"><th colspan=2><br/>
    <button type="button" onclick="save_user()">Save</button>
    <button type="button" onclick="javascript:location='./'">Cancel</button>
    <br/><br/>
   </th></tr>
	</table>
	</form>
<?php
  if ($_msg != null) 
    echo '<div class="error">'. $_msg .'</div>';
?>