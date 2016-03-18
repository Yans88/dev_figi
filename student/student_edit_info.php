<?php
$_id = (!empty($_GET['id'])) ? $_GET['id'] :  0;
//echo $_id;
ob_clean();
$student = get_student_info_by_id($_id);

if(!empty($_POST['save'])){ // ADD HERE
	
	$father_name = htmlspecialchars($_POST['father_name'], ENT_QUOTES);
	$father_email = htmlspecialchars($_POST['father_email'], ENT_QUOTES);
	$father_mobile_no = htmlspecialchars($_POST['father_mobile_no'], ENT_QUOTES);
	$mother_name = htmlspecialchars($_POST['mother_name'], ENT_QUOTES);
	$mother_email = htmlspecialchars($_POST['mother_email'], ENT_QUOTES);
	$mother_mobile_no = htmlspecialchars($_POST['mother_mobile_no'], ENT_QUOTES);
	$id_student 		= htmlspecialchars($_POST['id_student'], ENT_QUOTES);
	
	$url = './?mod=item&sub=student&act=list';
	
	$check = "SELECT * FROM student_info WHERE id_student = $id_student";
	$mysql = mysql_query($check);
	$rs = mysql_fetch_array($mysql);
	
	if($rs['id_student'] > 0){
		$query = "
				UPDATE student_info SET
					father_name = '$father_name',
					father_email_address = '$father_email',
					father_mobile_number = '$father_mobile_no',
					mother_name = '$mother_name',
					mother_email_address = '$mother_email',
					mother_mobile_number = '$mother_mobile_no'
				WHERE 
					id_student = $id_student
			";
		$execute = mysql_query($query);
		//error_log(mysql_error().$query);	
		if($execute){
			$msg = "Changed data has been updated";
			error_log($query);
		} else {
			$msg = "Failed to save changed data.";
			//error_log($query);
		}
		
	} else {
		$query = "
				INSERT INTO 
					student_info (id_student, father_name,father_email_address, father_mobile_number, mother_name,mother_email_address, mother_mobile_number)
				VALUES 
					('$id_student', '$father_name', '$father_email', '$father_mobile_no', '$mother_name', '$mother_email', '$mother_mobile_no')
			";
			
			$execute = mysql_query($query);
			//error_log($query);
			
			if($execute){
				$msg = "$full_name successfuly added.";
			} else {
				$msg = "$full_name failed on saving date!.";
			}
			
	}
	redirect($url,$msg);
	
} 


require 'header_popup.php';
$x = check_parentInfo($_id);
if($x>0) $caption="Edit Parent Info"; else $caption="Create New Parent Info"; 
?>
<div id="loading" style="position: absolute; display: none">processing....</div>
<div style="margin-top: 20px;">
<form id="frm_edit" method="post">
<input type="hidden" id="id_student" name="id_student" value="<?php echo $_id;?>">
<table  class="tbl_edit student" style="">
<tr><th class="center" colspan=8><?php echo $caption; ?></th></tr>
<tr>
	<td>Father Name</td><td><input type="text" name="father_name" id='father_name' maxlength="64" size="30px">
	
	<span class="field-note error">*</span>
	</td>
</tr>
<tr>
	<td>Father Email</td><td><input type="text" name="father_email" id="father_email" maxlength="100" size="30px"></td>
</tr>
<tr>
	<td>Father Phone Number</td><td><input type="text" name="father_mobile_no" id="father_mobile_no" maxlength="120" size="30px"></td>
</tr>
<tr>
	<td>Mother Name</td><td><input type="text" name="mother_name" id='mother_name' maxlength="64" size="30px"></td>
</tr>
<tr>
	<td>Mother Email</td><td><input type="text" name="mother_email" id="mother_email" maxlength="100"  size="30px"></td>
</tr>
<tr>
	<td>Mother Phone Number</td><td><input type="text" name="mother_mobile_no" id="mother_mobile_no" maxlength="120" size="30px"></td>
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
	var t = $('#father_name').val();
	if (t.length==0){
		alert('Please enter full name of the Father Name!');
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
	echo '$(\'#father_name\').val("'.$student['father_name'].'");';	
	echo '$(\'#father_email\').val("'.$student['father_email_address'].'");';	
	echo '$(\'#father_mobile_no\').val("'.$student['father_mobile_number'].'");';	
	echo '$(\'#mother_name\').val("'.$student['mother_name'].'");';	
	echo '$(\'#mother_email\').val("'.$student['mother_email_address'].'");';	
	echo '$(\'#mother_mobile_no\').val("'.$student['mother_mobile_number'].'");';	
}
?>
$('#father_name').focus();

</script>
