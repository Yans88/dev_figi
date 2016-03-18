<?php 

if (!defined('FIGIPASS')) exit;
$_msg = null;

if (isset($_POST['import']) ) {
	$err = 0;
	if (is_uploaded_file($_FILES['csv']['tmp_name'])) 
		$err = import_csv_deskcopy_item($_FILES['csv']['tmp_name'], USERDEPT);
	
	if ($err < 1)
		switch($err){
		case -1 : $_msg = 'Import process was failed: Invalid Number of Columns.'; break;
		case  0 : $_msg = 'File upload was failed. Import can not be performed.'; break;		
		case -2 : $_msg = 'System error.'; break;		
		case -3 : $_msg = 'Import process was failed. File does not contain item data.'; break;		
		case -4 : $_msg = 'Import process was finished. Nothing imported, perhaps data already exists.'; break;		
		}
	else
		$_msg = 'Upload is Success. Imported ' . $err . ' item(s).';
}


?>
<br/>
<form method="POST" enctype="multipart/form-data" >
<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">Import Item(s) from CSV File</th></tr>
<tr><td height=40>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
    Select a CSV file 
    <input type="file" name="csv" value="Select...">
  </td>
</tr>
<tr>
  <td align="center"> 
    <button type="submit" name="import">Import Item(s)</button>
    <button type="button" onclick="location.href='./?mod=deskcopy';">Cancel</button>  
    </td>
</tr>  
</table>  
</form>
<?php
 //if ($_msg != null)
	//echo '<script>alert("'. $_msg . '");location.href="./?mod=deskcopy";</script>';
?>