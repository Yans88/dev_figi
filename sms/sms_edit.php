<?php


$user_name = isset($_POST['username']) ? $_POST['username'] : null;
$pass = isset($_POST['password']) ? $_POST['password'] : null;

$save = isset($_POST['save']) ? $_POST['save'] : null;


$username = null;
$password = null;

$data_edit = get_config_sms();

if(!empty($data_edit)){
	$username = $data_edit['name'];
	$password = $data_edit['value'];
}

if($save){		
	$query = "UPDATE configuration set name='$user_name', value='$pass' where section = 'sms_school'";		
	$ok_save = mysql_query($query);	
	if($ok_save){
		redirect('./?mod=sms');
	}else{
		echo '<span class="error">Failed to save data</span>';
	}	
}

?>
<style>#id_location{width:243px;}</style>
<br/>

<form id="frm_edit" method="post" autocomplete="off">
<input type="hidden" id="id_sms" name="id_student" value="<?php echo $id;?>">
<table  class="tbl_edit student" style="">
<tr><th class="center" colspan=3>SMS Form</th></tr>

<tr>
	<td>&nbsp;</td>
	<td>Username</td><td><input type="text" name="username" id="username" size="30px" value="<?php echo $username;?>"></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>Password</td><td><input type="text" name="password" id="password" size="30px" value="<?php echo $password;?>"></td>
</tr>
<tr>
	<th colspan=3 class="center">
		<input type="button" name="cancel" id="cancel" value=" Cancel" >
		<input type="submit" name="save" id="save" value=" Ok " >
	</th>
	
</tr>
</table>
</form>

<script>

$('#cancel').click(function(){
	var href = "./?mod=sms";
	window.location.href = href;
});

$('#edit').click(function(){
	$('#frm_edit').submit();
});

</script>