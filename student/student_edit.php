<?php
$_id = (!empty($_GET['id'])) ? $_GET['id'] :  0;

ob_clean();
$student = get_student($_id);

if(!empty($_POST['save'])){ // ADD HERE
	
	$register_number = htmlspecialchars($_POST['register_number'], ENT_QUOTES);
	$full_name = htmlspecialchars($_POST['full_name'], ENT_QUOTES);
	$nric = htmlspecialchars($_POST['nric'], ENT_QUOTES);
	$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
	$class = htmlspecialchars($_POST['class'], ENT_QUOTES);
	$status = htmlspecialchars($_POST['status'], ENT_QUOTES);
	$id_student 		= htmlspecialchars($_POST['id_student'], ENT_QUOTES);
	
	$url = './?mod=item&sub=student&act=list';
	if ($id_student==0){
		$check = "SELECT nric, email FROM students WHERE nric = '$nric' OR email= '$email'";
		$query_check = mysql_query($check);
		$row = mysql_num_rows($query_check);
		if($row > 0)
			$msg = 'NRIC or Email is already in the Student List.';
			/*
		elseif(empty($register_number)) echo "<script>alert('Register Number cannot be a null.');location.href='./?mod=item&sub=student_add';</script>"; 
		elseif(! is_numeric($register_number)) 
			echo "<script>alert('Invalid format Register Number. Register Number should be Numeric Format.'); location.href='./?mod=item&sub=student_add'; </script>";
		elseif(empty($full_name)) echo "<script>alert('Full Name cannot be a null.');location.href='./?mod=item&sub=student_add';</script>";
		elseif(empty($email)) echo "<script>alert('Email cannot be a null.');location.href='./?mod=item&sub=student_add';</script>";
		elseif(empty($class)) echo "<script>alert('Class cannot be a null.');location.href='./?mod=item&sub=student_add';</script>";
		elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "<script>alert('Invalid Email format.');location.href='./?mod=item&sub=student_add';</script>"; }
		
		*/
		else {
		
		
			$query = "
				INSERT INTO 
					students (register_number, full_name, nric, email, class, active)
				VALUES 
					('$register_number', '$full_name', '$nric', '$email', '$class', '$status')
			";
			
			$execute = mysql_query($query);
			//error_log(mysql_error().$query);
			
			if($execute){
				$msg = "$full_name successfuly added.";
			} else {
				$msg = "$full_name failed on saving date!.";
			}
		}
	} else { // update/edit
		$query = "
				UPDATE students SET
					register_number = '$register_number',
					full_name = '$full_name',
					nric = '$nric',
					email = '$email',
					class = '$class',
					active = '$status'
				WHERE 
					id_student = $id_student
			";
		$execute = mysql_query($query);
		//error_log(mysql_error().$query);	
		if($execute){
			$msg = "Changed data has been updated";
		} else {
			$msg = "Failed to save changed data.";
		}
	
	}
	
	redirect($url, $msg);
} 


require 'header_popup.php';

$caption = empty($id_student) ? 'Create New Student' : 'Edit Student';
?>
<div id="loading" style="position: absolute; display: none">processing....</div>
<div style="margin-top: 20px;">
<form id="frm_edit" method="post">
<input type="hidden" id="id_student" name="id_student" value=0>
<table  class="tbl_edit student" style="">
<tr><th class="center" colspan=8><?php echo $caption;?></th></tr>
<tr>
	<td>Full Name</td><td><input type="text" name="full_name" id='full_name' maxlength="100" size="30px">
	<span class="field-note error">*</span>
	</td>
</tr>
<tr>
	<td>NRIC</td><td><input type="text" name="nric" id="nric" maxlength="10"  style="width: 90px"></td>
</tr>
<tr>
	<td>Email</td><td><input type="text" name="email" id="email" maxlength="120" size="30px"></td>
</tr>
<tr>
	<td>Class</td><td><input type="text" name="class" id="class" maxlength="3" style="width: 30px"></td>
</tr>
<tr>
	<td>Register Number</td><td><input type="text" name="register_number" id='register_number' maxlength="2" style="width: 30px">
	</td>
</tr>
<tr>
	<td>Status</td><td><input type="radio" name="status" id="active" value="1" checked> Active <input type="radio" name="status" id="inactive" value="0"> Inactive</td>
</tr>
<tr><td colspan=2><span class="field-note info"><span style="color:red">*)</span> the field is mandatory</span></td></tr>
<tr>
	<th colspan=2 class="center">
		<input type="button" name="cancel" id="cancel" value=" Cancel" >
		<input type="button" name="edit" id="edit" value=" Save " >
	</th>
	
</tr>
</table>
</form>

<script>
$('#cancel').click(function(){
	parent.jQuery.fancybox.close();
});
$('#edit').click(function(){
	var ok = false;
	var t = $('#full_name').val();
	if (t.length==0){
		alert('Please enter full name of the student!');
		$('#full_name').focus();
	} else ok = true;
	if (ok){	
		$('#loading').show();
		$('#frm_edit').append('<input type="hidden" name="save" value=1>');
		$('#frm_edit').submit();
		//parent.jQuery.fancybox.close();
		parent.location.reload();
	}
});

<?php
if (!empty($student)){
	echo '$(\'#id_student\').val("'.$student['id_student'].'");';	
	echo '$(\'#full_name\').val("'.$student['full_name'].'");';	
	echo '$(\'#nric\').val("'.$student['nric'].'");';	
	echo '$(\'#email\').val("'.$student['email'].'");';	
	echo '$(\'#class\').val("'.$student['class'].'");';	
	echo '$(\'#register_number\').val("'.$student['register_number'].'");';	
	if ($student['active']>0)
		echo '$(\'#active\').checked=true;';	
	else
		echo '$(\'#inactive\').checked=true;';	
}
?>
$('#full_name').focus();

</script>
