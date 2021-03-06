<?php 

if (!defined('FIGIPASS')) exit;
$_msg = null;

if (isset($_POST['import']) ) {
	$err = 0;
	
	if (is_uploaded_file($_FILES['csv']['tmp_name']))
		$err = import_csv_key_item($_FILES['csv']['tmp_name'], USERDEPT);
	print_r($err);
	
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
<tr><th style="color: white">Import Key(s) from CSV File</th></tr>
<tr><td height=40>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
    Select a CSV file 
    <input type="file" name="csv" value="Select...">
  </td>
</tr>
<tr>
  <td align="center"> 
    <button type="submit" name="import">Import Key(s)</button>
    <button type="button" onclick="location.href='./?mod=keyloan';">Cancel</button>  
    </td>
</tr> 
<tr><td height=20>&nbsp;</td></tr>
<tr><td align='center'>To download CSV Template, Click <a href="./?mod=keyloan&act=import_template">Here</a></td></tr>   
</table>  
</form>

<?php
 if ($_msg != null)
	echo '<script>alert("'. $_msg . '");location.href="./?mod=keyloan";</script>';
?>