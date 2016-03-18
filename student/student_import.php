<?php
if (!defined('FIGIPASS')) exit;
ob_clean();
require 'header_popup.php';

$_msg = null;
function students_import($path)
{   
	$incomplete_data = 0;
	$error_query = 0;
	$row = 0;
	///$_year = date("Y");
    if (!empty($path) && file_exists($path)) {
		ini_set('auto_detect_line_endings', 1);
        if (($fp = fopen($path, 'r')) !== FALSE){
            $cols = fgetcsv($fp, 1024, ',');
            if (count($cols) >= 4){                 
				mysql_query('START TRANSACTION');                 
                while ($cols = fgetcsv($fp, 1024, ',')){
                    $row++;
					$result['data'] = $cols;
					$result['line'] = $row;
                    $can_continue = true;
					$reg_numb = $cols[0];
                    $full_name = $cols[1];                     
                    $nric = $cols[2]; 
                    $email = strtolower($cols[3]); 
                    $class = $cols[4];
					$_year = $cols[5];
					$father_name = $cols[6];
					$father_email = $cols[7];
					$father_mobile_no = htmlspecialchars($cols[8], ENT_QUOTES);
					$mother_name = $cols[9];
					$mother_email = $cols[10];
					$mother_mobile_no = htmlspecialchars($cols[11], ENT_QUOTES);
                   if($email == ''){
					   $email = null;
				   }       
                   
				   $check_into_students = "SELECT id_student, nric FROM students WHERE nric = '$nric'";
				   $sql_check = mysql_query($check_into_students);
				   $row_check = mysql_fetch_array($sql_check);
				   if($row_check >= 1){
				   
						$_studentID = $row_check['id_student'];
						$check_student_info = "SELECT id_student FROM student_info WHERE id_student = $_studentID";
						$sql_check_student_info = mysql_query($check_student_info);
						$row_student_info = mysql_fetch_row($sql_check_student_info);
							
							if($row_student_info >= 1){
							
								$update_info = "UPDATE student_info SET
													father_name = '$father_name',
													father_email_address = '$father_email',
													father_mobile_number = '$father_mobile_no',
													mother_name = '$mother_name',
													mother_email_address = '$mother_email',
													mother_mobile_number = '$mother_mobile_no'
												WHERE id_student = $_studentID";
								
								$execute_student_info = mysql_query($update_info);
								error_log(mysql_error(). $update_info);
								
							} else {
						
								// START Input to Student Info Table
								$query_info = "INSERT INTO student_info (id_student, father_name, father_email_address, father_mobile_number, mother_name, mother_email_address, mother_mobile_number )
									VALUES ('$_studentID', $father_name, '$father_email', '$father_mobile_no', '$mother_name', '$mother_email', '$mother_mobile_no')";
									
								mysql_query($query_info);
								error_log(mysql_error(). $query_info);
								// END Input to Student Info Table
							}
							
				   } else {
				   
						$query = "INSERT INTO students (register_number, full_name, nric, email, class, active)VALUES"; 
						$query .= "('$reg_numb', '$full_name', '$nric', '$email', '$class', 1)";
						error_log(mysql_error(). $query);
						mysql_query($query);
						$_studentID = mysql_insert_id();

						//START input student to USER
						global $encryption;
						$_username  = $encryption->encode($nric);
						$password = md5($nric);

						$query_user = "INSERT INTO user(full_name, user_name, user_pass, user_email, id_group, nric, user_active ) VALUES 
									('$full_name','$_username', '$password', '$email', 15, '$nric', 1)";
						mysql_query($query_user);
						error_log(mysql_error(). $query_user);
						//END  input student to USER
						
						// START Input to Student Info Table
						$query_info = "INSERT INTO student_info (id_student, father_name, father_email_address, father_mobile_number, mother_name, mother_email_address, mother_mobile_number )
							VALUES ('$_studentID', '$father_name', '$father_email', '$father_mobile_no', '$mother_name','$mother_email', '$mother_mobile_no')";
						mysql_query($query_info);
						error_log(mysql_error(). $query_info);
						// END Input to Student Info Table
						
						if (mysql_affected_rows() == 1){
						   $query = "INSERT INTO student_classes (id_student, year, class) values ('$_studentID', '$_year', '$class')";
						   $rs = mysql_query($query);
						   $result['success']++;
						   error_log(mysql_error(). $query);				  
						} else
							$result['fail']++;
					}
                }
			if (($error_query == 0 && $incomplete_data == 0)){
					$result['code'] = 0; 
					mysql_query('COMMIT');
				}  else {					
					mysql_query('ROLLBACK');
				}
            } else 
                $result['code'] = -1;
            fclose($fp);
        } else
            $result['code'] = -3; 
    } else
		$result['code'] = -2; 
	
    return $result;
}

if (isset($_POST['import'])) {
	$err['code'] = -2;	
	$filename = $_FILES['csv']['tmp_name'];
	if (is_uploaded_file($filename)) 
		$err = students_import($filename);	
	switch($err['code']){
		case -1 : $_msg = 'Import can not be performed. Invalid Number of Columns.'; break;
		case -2 : $_msg = 'Upload was failed. Import can not be performed.'; break;		
		case -3 : $_msg = 'Internal System error.'; break;		
		case  0 : $_msg = 'Import ' . $err['success'] . ' data students successfully'; break;
	}	
}

?>
<div width=600 style="color:#fff;">
<h4>Import Student Data in CSV File</h4>
<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<?php 
if (empty($_msg)){
?>
<p class="center">
    Select the file  
</p>
<div style="height:20px">&nbsp;</div>
<p class="center">
    <input type="file" name="csv" value="Select...">
</p>
<div style="height:20px">&nbsp;</div>
<p class="center"> 
	<input type="submit" name="import" value=" Import Students(s) " > 
	<br /><br /><br /><br />
	To download CSV template, Click <a href="#" onclick="return download_csv_template()">Here</a>
</p>  
<?php
} else {
	echo ' <div style="height:20px">&nbsp;</div>';
	echo "<p class='msg center' style='width:100%'>$_msg</p>";
	echo ' <div style="height:20px">&nbsp;</div>';
	if ($err['code']!=0)
		echo '<p class="center">Click <a href="./?mod=student&act=import" class="button">Try Again</a>, to retry importing data.</p>';
}
?>
</form>
<br>
<script>
	function checkfile(frm){
		if (frm.csv.files.length == 0){
			alert('Please select a csv file to be uploaded!');
			return false;
		}
		return true;
	}
	
	function download_csv_template(){
		location.href="./?mod=student&act=csv_template";
	}
</script>
