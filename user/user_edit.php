<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>

<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />

<?php

$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$group = defined('USERGROUP') ? USERGROUP : null;
$dept_optional = defined('USERDEPT') ? USERDEPT : null;

if (!($i_can_update || $i_can_create)) {
    include 'unauthorized.php';
    return;
}

$_msg = null;
if (!defined('FIGIPASS')) exit;
  
$readonly = !($i_can_update || $i_can_delete || $i_can_create);

$rec['full_name'] = null;
$rec['user_name'] = null;
$rec['user_email'] = null;
$rec['id_department'] = null;
$rec['id_group'] = 2;
$rec['nric'] = null;
$rec['contact_no'] = null;
$active = true;

if (isset($_POST['save'])) {
    $ok = save_user($_id, $_POST);
    if ($ok > 0){
        $_msg = 'User data has been saved successefully!';
        echo "<script>
                alert('$_msg');
                location.href='./?mod=user&sub=user';
                </script>";
    }
}

if ($_id != 0) {
    $rec = get_user($_id);
    $active = $rec['user_active'] == 1;
}
    
$_caption = ($_id == 0) ? 'Create New User' : 'View/Edit Existing User';

$hidden_fields  = null;
if ((USERID == $_id) && SUPERADMIN){
    $usergroup = $rec['group_name'];
    //$userdept = '--';
    $hidden_fields .= '<input type="hidden" name="id_group" value='.$rec['id_group'].'>';
} if(USERID == $rec['id_user']) {
	$usergroup = $rec['group_name'];
    //$userdept = '--';
    $hidden_fields .= '<input type="hidden" name="id_group" value='.$rec['id_group'].'>';
} else {
    $usergroup = build_group_combo($rec['id_group'], $group);
    //$userdept = build_combo('id_department', get_department_list(), $rec['id_department']);
}
// IF USERGROUP IS ASSET OWNER


	$hidden_dept  = null;
	if ((USERID == $_id) && SUPERADMIN){
		$dept_options = $rec['department_name'];
		//$userdept = '--';
		
		$hidden_dept .= '<input type="hidden" name="id_department" value='.$rec['id_department'].'>';
	} if(USERDEPT == $rec['id_department'] ) {
		$dept_options = build_option(get_department_list(), $rec['id_department']);
		//$userdept = '--';
		
		$hidden_dept .= '<input type="hidden" name="id_department" value='.$rec['id_department'].'>';

	} else {
		$dept_options = build_option(get_department_list(), $rec['id_department']);
		//$userdept = build_combo('id_department', get_department_list(), $rec['id_department']);
	}

	


?>  
<script>	
function save_user(){
	var frm = document.forms[0];
	if (frm.name.value == ''){
		alert('Full name is mandatory, you must fill in!');
		frm.name.focus();
		return false;
	}
	if (frm.user.value == ''){
		alert('Username is mandatory, you must fill in!');
		frm.user.focus();
		return false;
	}
	if ((frm.uid.value<=0) && (frm.user_pass.value == '')){
		alert('Passwords is mandatory!');
		frm.user_pass.focus();
		return false;
	}
        frm.save.value="Save";
        frm.submit();
    
}
</script>	
<form method="POST"><br/>
    <?php echo $hidden_fields?>
	<input type="hidden" name="save" value="">
	<input type="hidden" name="uid" value="<?php echo $_id?>">
	<table width=400 class="userlist" cellpadding=3 cellspacing=1>	
	<tr><th colspan=2><h2 style="color: #000"><?php echo $_caption ?></h2></th></tr>
	<tr class="normal">
    <td align="left" width=120>Full Name</td>
    <td align="left" ><input type="text" name="name" value="<?php echo $rec['full_name']?>" size=30></td>
	</tr>
	<tr class="alt">
    <td align="left">NRIC</td>
    <td align="left"><input type="text" name="nric" value="<?php echo $rec['nric']?>" size=30></td>
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
    <td align="left">Username</td>
    <td align="left"><input type="text" name="user" value="<?php echo $rec['user_name']?>" <?php if ($_id != 0) echo 'readonly'?>  size=30></td>
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
    <td align="left">Main Department</td>
    <td align="left">
	  <select id="id_department" name="id_department">
	  <option value=0> </option>
	  <?php echo $dept_options; ?> 
	  </select>
    </td>
	<tr class="alt">
	<td align="left">Other Department</td>
    <td align="left">
	
		<?php
		
		if($rec['id_group'] == GRPASSETOWNER || $rec['id_group'] == GRPADM ){
			$x = check_department($rec['id_department'], $rec['id_group']);
			
			$y = mysql_query($x);
			while($execute = mysql_fetch_array($y)){
			
				$hidden_other_dept  = null;
				if ((USERID == $_id) && SUPERADMIN){
					$other_dept_options = $execute['department_name'];
					//$userdept = '--';
					
					$hidden_other_dept .= '<input type="hidden" name="id_department" value='.$rec['id_department'].'>';
				} if(USERDEPT == $rec['id_department'] ) {
					$other_dept_options = build_option(get_department_list(), $execute['id_department']);
					//$userdept = '--';
					
					$hidden_other_dept .= '<input type="hidden" name="id_department" value='.$execute['id_department'].'>';

				} else {
					$other_dept_options = build_option(get_department_list(), $execute['id_department']);
					//$userdept = build_combo('id_department', get_department_list(), $rec['id_department']);
				}
				
				if($execute['department_name'] == $rec['department_name']){} else{
					$delete = "<a href='#delete_other_department' title='Delete' class='delete_btn' id='delete-$rec[id_department]-$execute[id_department]-$rec[id_group]' ><img class='icon' src='images/delete.png' alt='delete'></a>";
					echo "<span id=".$execute['id_department'].">".$execute['department_name'].' '.$delete	.'
					</span><br />';
				}
			}
			
			$add= "<a href='#add_other_department' title='Add' class='add_btn' id='add-$rec[id_department]-$rec[id_group]' ><img class='icon' src='images/add.png' alt='add'></a>";
			echo $add;
		
		} else {
			Echo "This account cannot add other department.";
		}
		?>
		
    </td>  
	</tr>
   </tr>
	<tr class="normal" >
    <td align="left">Group</td>
      <td align="left">
	  <?php 
	  
	  echo $usergroup;
	  
	  ?>
	  
	  </td>
   </tr>
	<tr class="normal" valign="top">
    <td align="left">Status</td>
    <td align="left">
		<input type="radio" name="status" value="active" <?php if ($active) echo 'checked'?> > Active
		<input type="radio" name="status" value="inactive" <?php if (!$active) echo 'checked'?> <?php echo (USERID==$_id) ? 'disabled' : '' ?> > Inactive
    </td>
	</tr>
	<tr><th colspan=2><br/>
     <?php 
     if (!$readonly) {
    ?>
    <button type="button" onclick="save_user()" >Save</button>
    <button type="reset" >Reset</button>
    <button type="button" name="cancel"  onclick="javascript:location='./?mod=user&act=list'">Cancel</button>
    <?php
    } else {
    ?>
    <button type="button" name="cancel" onclick="javascript:location='./?mod=user&act=list'">Back to User List</button>
    <?php
    } // else
    ?>
    <br/><br/>
   </th></tr>
	</table>
	</form>
<?php
  if ($_msg != null) 
    echo '<div class="error">'. $_msg .'</div>';
	
?>
<script>
$('.add_btn').click(function (){

		var cols = this.id.split('-');

		var id = cols[1];
		var id_group = cols[2];
		
		var url = './?mod=user&act=edit_other_department&id='+id+'&id_group='+id_group;

		$.fancybox({href: url, type: 'iframe', padding: 5, width: 300, height: 80});


	});
$('.delete_btn').click(function (){

		var cols = this.id.split('-');

		var id = cols[1];
		var id_2 = cols[2];
		var id_group = cols[3];
		
		var url = './?mod=user&act=delete_other_department&id='+id+'&id_2='+id_2+'&id_group='+id_group;

		$.fancybox({href: url, type: 'iframe', padding: 5, width: 300, height: 80});


	});
</script>
