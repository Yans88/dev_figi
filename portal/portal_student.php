<?php
if (!defined('FIGIPASS')) return;
include_once './student/student_util.php';

?>

<form method="post">
	<table>
		<tr>
			<td>Room</td>
			<td><input type="text" name="room" ></td>
			<td><input type="checkbox" name="present"> Check All Present</td>
			
		</tr>
		<tr>
			<td>Class</td>
			<td><input type="text" name="class" ></td>
			<td><input type="checkbox" name="absent"> Check All Absent</td>
			
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><input class="buttons" type="submit" value="Use" name="submit"></td>
		</tr>
	</table>
</form>

<?php

if(isset($_POST['submit'])){
	$room = @$_POST['room'];
	$class = @$_POST['class'];
	$present = @$_POST['present'];
	$absent = @$_POST['absent'];
	
	echo "The data submitted.";

}

?>