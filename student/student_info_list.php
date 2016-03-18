<?php
$_id = (!empty($_GET['id'])) ? $_GET['id'] :  0;
$student = get_student($_id);
$parentinfo = getParentInfo_byId($_id);

ob_clean();
require 'header_popup.php';
$data = mysql_fetch_array($parentinfo);
?>
<br />
<table class="tbl_edit student" style="">
<tr><th class="center" colspan=8>Parent Info <?php echo $student['full_name'];?></th></tr>
<tr>
	<td>Father Name</td>
	<td><?php echo $data['father_name'];?></td>
	
</tr>
<tr>
	<td>Father Email :</td><td><?php echo $data['father_email_address'];?></td>
</tr>
<tr>
	<td>Father Phone Number :</td><td><?php echo $data['father_mobile_number'];?></td>
</tr>
<tr>
	<td>Mother Name :</td><td><?php echo $data['mother_name'];?></td>
</tr>
<tr>
	<td>Mother Email :</td><td><?php echo $data['mother_email_address'];?></td>
</tr>
<tr>
	<td>Mother Phone Number : </td><td><?php echo $data['mother_mobile_number'];?></td>
</tr>

<tr>
	<th colspan=2 class="center">
		<input type="button" name="cancel" id="cancel" value=" Close" >
		
	</th>
	
</tr>
</table>
<script>
$('#cancel').click(function(){
	parent.jQuery.fancybox.close();
});
</script>