<?php
if (!defined('FIGIPASS')) exit;
$_msg = null;
function students_import($path)
{   
	$incomplete_data = 0;
	$error_query = 0;
	$row = 0;
	$_year = date("Y");
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
                   if($email = ''){
					   $email = null;
				   }       
                   $query = "INSERT INTO students (register_number, full_name, nric, email, class)VALUES"; 
				   $query .= "('$reg_numb', '$full_name', '$nric', '$email', '$class')";                     
                    mysql_query($query);
					$_studentID = mysql_insert_id();
                    error_log(mysql_error(). $query);					
                    if (mysql_affected_rows() == 1){
                       $query = "INSERT INTO student_classes (id_student, year, class) values ('$_studentID', '$_year', '$class')";
					   $rs = mysql_query($query);
                       $result['success']++;
					  //error_log(mysql_error(). $query);				  
                    } else
						$result['fail']++;
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

<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">Import data students(s) from CSV File</th></tr>
<tr><td height=40>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
    Select a CSV file 
    <input type="file" name="csv" value="Select...">
  </td>
</tr>
<tr>
  <td align="center"> <input type="submit" name="import" value=" Import Item(s) " > </td>
</tr>  
</table>  
</form>
<br>
<div style="color:yellow; font-size:10pt;">
<?php echo $_msg ?>
</div>
<script>
	function checkfile(frm){
		if (frm.csv.files.length == 0){
			alert('Please select a csv file to be uploaded!');
			return false;
		}
		return true;
	}
</script>