<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if (isset($_POST['import']) ) {
	$err = 0;
	if (is_uploaded_file($_FILES['csv']['tmp_name'])) 
		$err = import_csv_user($_FILES['csv']['tmp_name']);
	
	if ($err < 1)
		switch($err){
		case -1 : $_msg = 'Upload is Success. Import is failed: Invalid Number of Columns.'; break;
		case 0 : $_msg = 'Upload is failed. Import can not be performed.'; break;		
		case -2 : $_msg = 'System error.'; break;		
		case -3 : $_msg = 'Import failed. File does not contain user data.'; break;		
		}
	else
		$_msg = 'Upload is Success. Imported ' . $err . ' User(s).';
}

?>

<form method="POST" enctype="multipart/form-data" >

<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">Import User(s) from CSV File</th></tr>
<tr><td height=60>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
    Select a CSV file 
    <input type="file" name="csv" value="Select...">
  </td>
</tr>
<tr>
  <td align="center"> <input type="submit" name="import" value=" Import User(s) " > </td>
</tr>  
</table>  
</form>
<?php
 if ($_msg != null)
	echo '<script>alert("'. $_msg . '");location.href="./?mod=user";</script>';
?>