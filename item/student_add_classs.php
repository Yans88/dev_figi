<?php

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$total_item = count_student();
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

?>

<h2>Add Class</h2>
<form method="post" action="">
<table cellpadding=0 cellspacing=0 class="itemlist" width="400px">
<tr>
	<td>Student</td>
	<td>
		<select name="student" id="id_student">
			<option value=''> -- Option -- </option>
			<?php
				$rs = get_students();
				while($rec = mysql_fetch_array($rs)){
					echo "<option value='".$rec['id_student']."'>".$rec['full_name']."</option>";
				}
			?>
		</select>
		<input type='hidden' id="id_student_class" name="id_student_class" disabled>
	</td>
</tr>
<tr>
	<td>Year</td><td><input type="text" name="year" id="year" maxlength="4" size="10px"></td>
</tr>
<tr>
	<td>Class</td><td><input type="text" name="class" id="class" maxlength="3" size="10px"></td>
</tr>
<tr>
	<td></td><td><input type="submit" name="submit" value="Add" id="add"> <input type="submit" name="edit" id="edit" value="Save Changes" disabled></td>
</tr>
</table>
</form>


<br /><br /><br /><br />
<?php

if(@$_POST['submit']){

	$id_student = htmlspecialchars($_POST['student'], ENT_QUOTES);
	$year = htmlspecialchars($_POST['year'], ENT_QUOTES);
	$class = htmlspecialchars($_POST['class'], ENT_QUOTES);
	
	
	$check = "SELECT id_student, year, class FROM student_classes WHERE year = '$year' AND class = '$class' AND id_student = '$id_student'";
	$query_check = mysql_query($check);
	$row = mysql_num_rows($query_check);

	if($row > 0) { echo "<script>alert('Student, Year and Class is already in the Class List.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (empty($id_student)) { echo "<script>alert('Student cannot be a null.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (empty($year)) { echo "<script>alert('Year cannot be a null.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (! is_numeric($year)) { echo "<script>alert('Invalid format Year. Year should be Numeric Format.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (empty($class)) { echo "<script>alert('Class cannot be a null.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	else {	
		$query = "
			INSERT INTO 
				student_classes (id_student, year, class)
			VALUES 
				('$id_student', '$year', '$class')
		";
		
		$execute = mysql_query($query);
		//echo $query;
		
		$full_name = get_student_by_id($id_student);
		if (mysql_affected_rows()>0){
			$insert = "UPDATE students set class_now = '".$class."' where id_student= ".$id_student;
			$rs = mysql_query($insert);
        error_log(mysql_error().$insert);
	}	 
		
		if($execute){
			echo "<script>alert('$full_name Successfuly added.');location.href='./?mod=item&sub=student_add_class';</script>";
		} else {
			echo "<script>alert('$full_name Adding Failed.');location.href='./?mod=item&sub=student_add_class';</script>";
		}
	}
} 



if(@$_POST['edit']){

	$id_student_class = htmlspecialchars($_POST['id_student_class'], ENT_QUOTES);
	$id_student = htmlspecialchars($_POST['student'], ENT_QUOTES);
	$year = htmlspecialchars($_POST['year'], ENT_QUOTES);
	$class = htmlspecialchars($_POST['class'], ENT_QUOTES);
	
	$check = "SELECT id_student, year, class FROM student_classes WHERE year = '$year' AND class = '$class' AND id_student = '$id_student'";
	$query_check = mysql_query($check);
	$row = mysql_num_rows($query_check);

	if($row > 0) { echo "<script>alert('Student, Year and Class is already in the Class List.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (empty($id_student)) { echo "<script>alert('Student cannot be a null.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (empty($year)) { echo "<script>alert('Year cannot be a null.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (! is_numeric($year)) { echo "<script>alert('Invalid format Year. Year should be Numeric Format.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	elseif (empty($class)) { echo "<script>alert('Class cannot be a null.');location.href='./?mod=item&sub=student_add_class';</script>"; }
	else {	
		$query = "
			UPDATE student_classes SET
				id_student = $id_student,
				year = '$year',
				class = '$class'
			WHERE id_student_class = $id_student_class
		";
		
		$execute = mysql_query($query);
		//echo $query;
		
		$full_name = get_student_by_id($id_student);
		
		if($execute){
			echo "<script>alert('$full_name Successfuly added.');location.href='./?mod=item&sub=student_add_class';</script>";
		} else {
			echo "<script>alert('$full_name Adding Failed.');location.href='./?mod=item&sub=student_add_class';</script>";
		}
	}
	

} 

?>

	<table class='itemlist' cellspacing='1' cellpadding='3' width='500px'>
		<tr>
			<th>No</th><th>Student Name</th><th>Year</th><th>Class</th><th>Action</th>
		</tr>
		<?php
		$counter = 0;
		$rs = getStudentClasses($_start, $_limit);
		while($data = mysql_fetch_array($rs)){
		
		$delete_button = "<a href='./?mod=item&sub=student_class_delete&id_student_class=".$data['id_student_class']."&id_student=".$data['id_student']."' onclick=\"return confirm('Are you sure you want to delete ".$data['full_name']." ?')\" title='Delete'> <img class='icon' src='images/delete.png' alt='delete'> </a>";
			
			
			$_year = $data['year'];
			$_class = $data['class'];
			
			
		$edit_button = "<a href='#' title='Edit' onclick='return getById(".$data['id_student_class'].", ".$data['id_student'].", \"$_year\",  \"$_class\")'> <img class='icon' src='images/edit.png' alt='delete'> </a>";
		
		
		$counter++;
			echo "
			<tr>
				<td>$counter</td><td>".$data['full_name']."</td><td>".$data['year']."</td><td>".$data['class']."</td><td align='center'>".$delete_button." ".$edit_button."</td>
			</tr>
			
			";
		}
		?>
		<tr>
			<td colspan='5' align="center">
			<?php
			echo make_paging($_page, $total_page, './?mod=item&sub=student_add_class&page=');
			?>
			</td>
		</tr>
	</table>
	
<?php


// ================= FUNCTIONS =================

function get_students(){

	$query = "SELECT * FROM students WHERE active = 1";
	
	$mysql = mysql_query($query);
	return $mysql;

}

function get_student_by_id($id_student){

	$query = "SELECT * FROM students WHERE id_student='$id_student'";
	
	$mysql = mysql_query($query);
	$data = mysql_fetch_array($mysql);
	return $data['full_name'];

}


function count_student(){

	$query = "SELECT count(*) as total FROM students ";
	
	$mysql = mysql_query($query);
	$fetch = mysql_fetch_array($mysql);
	
	return $fetch['total'];
	
}

function getStudentClasses($_start, $_limit){

	$query = "
		SELECT student_classes.id_student_class, student_classes.id_student,student_classes.year, student_classes.class , students.full_name FROM
			student_classes
		LEFT JOIN students ON students.id_student = student_classes.id_student
		ORDER BY year DESC LIMIT $_start, $_limit";
	$mysql = mysql_query($query);
	
	return $mysql;

}

?>



<script>
	
	function getById(id, id_student, year, classes){
		
		document.getElementById("id_student_class").disabled = false ;
		document.getElementById("id_student_class").value = id ;
		
		document.getElementById("id_student").value = id_student ;
		document.getElementById("year").value = year ;
		document.getElementById("class").value = classes ;
		document.getElementById("edit").disabled = false ;
		document.getElementById("add").disabled = true ;
		
		
		//alert(id);
		
	}
</script>