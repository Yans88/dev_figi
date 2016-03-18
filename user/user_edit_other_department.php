<?php


$id_department_parent = $_GET['id'];
$id_group = $_GET['id_group'];

ob_clean();	
$department_name_parent = get_name_department($id_department_parent);

if(isset($_POST['edit']) > 0){
	$parent = $_POST['department_parent'];
	$child = $_POST['id_department'];
	
	$x = add_other_department_execute($parent, $child, $id_group);
	
	echo "<script>
	
	alert('$x');
	parent.jQuery.fancybox.close();
	parent.location.reload(true);
	</script>
	";
}


require 'header_popup.php';
?>
<form method="POST">
<table class="tbl_edit student" style="width:300px;">
<tr class="normal" width="100">
	<th colspan="2" class="center">Add Other Department</th>
</tr>
<tr>
	<td> Main Department :</td>
	<td> <?php echo $department_name_parent;?></td>
	<input type="hidden" value="<?php echo $id_department_parent;?>" name="department_parent">
	<input type="hidden" value="<?php echo $id_group;?>" name="id_group">
<tr>
	<td>Other Department :</td>
	<td><select name="id_department" id="name_department"><option value="0"> </option><?php echo build_option(get_department_list());?></select></td>
</tr>
<tr>
	<th colspan=2 class="center">
		<input type="button" name="cancel" id="cancel" value=" Cancel" >
		<input type="submit" name="edit" id="edit" value="Save" >
	</th>
	
</tr>
</table>
</form>

<script>
$('#cancel').click(function(){
	parent.jQuery.fancybox.close();
});
</script>