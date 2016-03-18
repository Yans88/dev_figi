<?php

$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$total_item = count_student();
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;
$_searchby = !empty($_POST['searchby']) ? $_POST['searchby'] : null;
$_searchtext = !empty($_POST['searchtext']) ? $_POST['searchtext'] : null;
$_year = date("Y");
?>

<h2>Create Student</h2>
<a class='button' style="margin-left:730px;" href='./?mod=item&sub=student_import'>Import data Student</a>
<br/>
<form method="post" action="" id="student">
<table cellpadding=0 cellspacing=0 class="itemlist" width="400px">

<tr>
	<td>Register Number</td><td><input type="text" name="register_number" id='register_number' maxlength="2" size="10px">
	<input type="hidden" name="id_student" id='id_student' maxlength="2" size="10px" disabled>
	</td>
</tr>
<tr>
	<td>Full Name</td><td><input type="text" name="full_name" id='fullname' maxlength="100" size="30px"></td>
</tr>
<tr>
	<td>NRIC</td><td><input type="text" name="nric" id="nric" maxlength="10" size="30px"></td>
</tr>
<tr>
	<td>Email</td><td><input type="text" name="email" id="email" maxlength="120" size="30px"></td>
</tr>
<tr>
	<td>Class</td><td><input type="text" name="class" id="classs" maxlength="3"></td>
</tr>
<tr>
	<td>Status</td><td><input type="radio" name="status" id="active" value="1" checked> Active <input type="radio" name="status" id="inactive" value="0"> Inactive</td>
</tr>
<tr>
	<td></td>
	<td>
		<input type="submit" name="submit" id="add" value="Add"> <input type="submit" name="edit" id="edit" value="Save Changes" disabled>
		
	</td>
	
</tr>
</table>
<br /><br /><br /><br />
<div class="searchbox" >
    Search by
    <select name="searchby" id="searchby">
	<option value="" selected>-- Select --</option>
	<option value="full_name" <?php if ($_searchby == 'full_name') echo 'selected'?>>Full name</option>
    <option value="class" <?php if ($_searchby == 'class') echo 'selected'?> >Class</option>   
    </select>
   <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off style="width: 140px">    
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>
</form>
<style>
.searchbox{
	margin-right:230px;
}
</style>
<br /><br />
<?php
if(@$_POST['submit']){ // ADD HERE
	
	$register_number = htmlspecialchars($_POST['register_number'], ENT_QUOTES);
	$full_name = htmlspecialchars($_POST['full_name'], ENT_QUOTES);
	$nric = htmlspecialchars($_POST['nric'], ENT_QUOTES);
	$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
	$class = htmlspecialchars($_POST['class'], ENT_QUOTES);
	$status = htmlspecialchars($_POST['status'], ENT_QUOTES);	
	$check = "SELECT nric, email FROM students WHERE nric = '$nric' OR email= '$email'";
	$query_check = mysql_query($check);
	$row = mysql_num_rows($query_check);
	if($row > 0)
		echo "<script>alert('NRIC or Email is already in the Student List.');location.href='./?mod=item&sub=student_add';</script>";
	elseif(empty($register_number)) echo "<script>alert('Register Number cannot be a null.');location.href='./?mod=item&sub=student_add';</script>"; 
	elseif(! is_numeric($register_number)) 
		echo "<script>alert('Invalid format Register Number. Register Number should be Numeric Format.'); location.href='./?mod=item&sub=student_add'; </script>";
	elseif(empty($full_name)) echo "<script>alert('Full Name cannot be a null.');location.href='./?mod=item&sub=student_add';</script>";	
	elseif(empty($class)) echo "<script>alert('Class cannot be a null.');location.href='./?mod=item&sub=student_add';</script>";
	//elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){ 
		//echo "<script>alert('Invalid Email format.');location.href='./?mod=item&sub=student_add';</script>"; 
	//}		
	else {	
		$query = "
			INSERT INTO 
				students (register_number, full_name, nric, email, class, active)
			VALUES 
				('$register_number', '$full_name', '$nric', '$email', '$class', '$status')
		";
		
		$execute = mysql_query($query);		
		$_studentID = mysql_insert_id();
		//error_log(mysql_query().$query);
		if (mysql_affected_rows()>0){
			$query = "INSERT INTO student_classes (id_student, year, class) values ('$_studentID', '$_year', '$class')";
			$rs = mysql_query($query);       
	}	 
		if($execute){			
			echo "<script>alert('$full_name Successfuly added.');location.href='./?mod=item&sub=student_add';</script>";
		} else {
			echo "<script>alert('$full_name Adding Failed.');location.href='./?mod=item&sub=student_add';</script>";
		}
	}
} 

if(@$_POST['edit']){ // UPDATE HERE
	//echo "Oke";
	$id_student 		= htmlspecialchars($_POST['id_student'], ENT_QUOTES);
	$register_number 	= htmlspecialchars($_POST['register_number'], ENT_QUOTES);
	$full_name 			= htmlspecialchars($_POST['full_name'], ENT_QUOTES);
	$nric 				= htmlspecialchars($_POST['nric'], ENT_QUOTES);
	$email				= htmlspecialchars($_POST['email'], ENT_QUOTES);
	$class				= htmlspecialchars($_POST['class'], ENT_QUOTES);
	$status 			= htmlspecialchars($_POST['status'], ENT_QUOTES);
	$check 				= "SELECT nric, email FROM students WHERE nric = '$nric' OR email= '$email'";
	
	if(empty($register_number)) { echo "<script>alert('Register Number cannot be a null.');location.href='./?mod=item&sub=student_add';</script>"; }
	else if(! is_numeric($register_number)) { echo "<script>alert('Invalid format Register Number. Register Number should be Numeric Format.');location.href='./?mod=item&sub=student_add';</script>"; }
	else if(empty($full_name)) { echo "<script>alert('Full Name cannot be a null.');location.href='./?mod=item&sub=student_add';</script>"; }
	else if(empty($email)) { echo "<script>alert('Email cannot be a null.');location.href='./?mod=item&sub=student_add';</script>"; }
	else if(empty($class)) { echo "<script>alert('Class cannot be a null.');location.href='./?mod=item&sub=student_add';</script>"; }
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "<script>alert('Invalid Email format.');location.href='./?mod=item&sub=student_add';</script>";
	
	//echo $query;
	
	} else {		
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
		
		if($execute){
			echo "<script>alert('$full_name Successfuly Updated.');location.href='./?mod=item&sub=student_add';</script>";
		} else {
			echo "<script>alert('$full_name Updating Failed.');</script>";
		}
	
	}
}			
			$data_row = get_students($_searchby, $_searchtext, $_start, $_limit);
			$count = mysql_num_rows($data_row);
			if($count > 0){
			?>
			<table class='itemlist' cellpadding=3 cellspacing=1 width='600px'>
				<tr>
					<th>No</th><th>Full Name</th><th>Register No.</th><th>NRIC</th><th>Email</th><th>Class</th><th>Status</th><th>Action</th>
				</tr>
			<?php 			
			$counter = 0;			
			while($data = mysql_fetch_array($data_row)){
			$counter++;
			
			$delete_button = "<a href='./?mod=item&sub=student_delete&id_student=".$data['id_student']."' onclick=\"return confirm('Are you sure you want to delete ".$data['full_name']." ?')\" title='Delete'> <img class='icon' src='images/delete.png' alt='delete'> </a>";
			
			$_full_name = $data['full_name'];
			$_nric = $data['nric'];
			$_email = $data['email'];
			$_class = $data['class'];
			$_status = $data['active'];
			
			$edit_button = "<a href='#' title='Edit' onclick='return getById(".$data['id_student'].", ".$data['register_number'].", \"$_full_name\", \"$_nric\", \"$_email\", \"$_class\", $_status )'> <img class='icon' src='images/edit.png' alt='delete'> </a>";
			
			if($data['active'] == 0) { $status = "Inactive"; } else { $status = "Active";}
			
			echo "
			
				<tr>
					<td>$counter</td><td>".$data['full_name']."</td><td>".$data['register_number']."</td><td>".$data['nric']."</td><td>".$data['email']."</td>
					<td>".$data['class']."</td><td>".$status."</td><td align='center'>".$edit_button." </td>
				</tr>
			
			";
			}
			
		?>
		<tr>
			<td colspan=8 align=center>
			<?php
				echo make_paging($_page, $total_page, './?mod=item&sub=student_add&page=');
			?>
			</td>
		</tr>
	</table>
			<?php } else{
				echo 'Data not available';			
			}


// ================= FUNCTIONS =================

function get_students($search_by = null, $search_text=null, $start = 0, $limit = 10){
	$query = "SELECT *  FROM students";
	if(!empty($search_by) && !empty($search_text) && $search_by == 'class'){
		 $query .= " where class ='$search_text'";
	}
	if(!empty($search_by) && !empty($search_text) && $search_by == 'full_name'){
		 $query .= " where full_name ='$search_text'";
	}
	$query .= " ORDER BY register_number ASC LIMIT $start, $limit";		
	//error_log(mysql_error().$query);
	$mysql = mysql_query($query);
	return $mysql;
}

function get_student_by_id($id_student){
	$query = "SELECT *  FROM students WHERE id_student = '$id_student'";		
	$mysql = mysql_query($query);
	return $mysql;

}

function count_student(){
	$query = "SELECT count(*) as total FROM students ";	
	$mysql = mysql_query($query);
	$fetch = mysql_fetch_array($mysql);
	
	return $fetch['total'];
	
}

?>


<script>
	
	function getById(id, reg_no, full_name, nric, email, classes, status){
		
		document.getElementById("id_student").disabled = false ;
		document.getElementById("id_student").value = id ;
		document.getElementById("register_number").value = reg_no ;
		document.getElementById("full_name").value = full_name ;
		document.getElementById("nric").value = nric ;
		document.getElementById("email").value = email ;
		document.getElementById("class").value = classes ;
		document.getElementById("edit").disabled = false ;
		document.getElementById("add").disabled = true ;
		
		if(status==1){
			document.getElementById("active").checked = true;
			//alert(status);
		} else {
			document.getElementById("inactive").checked = true;
			//alert(status);
		} 
		
		
		
		
		//alert(id);
		
	}
	
	function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	var searchBy = $('#searchby option:selected').val();
	//console.log(searchBy);
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("item/suggest_students.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", searchBy: ""+searchBy+""}, function(data){
			if(data.length >0) {				
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#searchtext').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({left:pos.left});
			} else
                 $('#suggestions').fadeOut();
		});
	}
}
	
	$('.searchsubmit').click(function(){
		$('#student').submit();
	});
</script>
	