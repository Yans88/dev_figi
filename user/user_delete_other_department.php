<?php


$id_department_parent = $_GET['id'];
$id_department_child = $_GET['id_2'] ? $_GET['id_2'] : 0;

ob_clean();	

$department_name_child = get_name_department($id_department_child) ? get_name_department($id_department_child) : "Administrator";

require 'header_popup.php';
?>
<form method="POST">
<input type="hidden" value="<?php echo $id_department_parent ?>" name="parent">
<input type="hidden" value="<?php echo $id_department_child ?>" name="child">
<div style="text-align:center;padding-top:50px;">
Are you sure want to delete <?php echo $department_name_child;?> ?<br /><br /><br />
<input type="button" id="cancel" name="cancel" value="Cancel"><input type="submit" name="delete" value="Delete">
</div>

</form>

<script>
$('#cancel').click(function(){
	parent.jQuery.fancybox.close();
});
</script>

<?php
if($_POST['delete']){
	$parent = $_POST['parent'];
	$child = $_POST['child'];
	
	$mysql = "DELETE FROM department_member WHERE id_department_parent= $parent AND id_department_child = $child";
	$mysql_query = mysql_query($mysql);
	
	echo "<script>
		alert('Delete Successfully.');
		parent.jQuery.fancybox.close();
		parent.location.reload(true);
	</script>";


}

?>