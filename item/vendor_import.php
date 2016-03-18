<?php 

if (!defined('FIGIPASS')) exit;
$_msg = null;

$import_val = !empty($_POST['val_import']) ? $_POST['val_import'] : 0;
$save = !empty($_POST['save']) ? $_POST['save'] : 0;


if($save == 'save'){	
	if(!empty($import_val)){
		print_r($import_val);
		$val = explode(';', $import_val);
		$count_val = count($val);	
		
		for($i=0; $i<$count_val;$i++){
			$dt = explode(',',$val[$i]);			
			$vendor_name = $dt[0];
			$contact1 = $dt[1];
			$email1 = $dt[2];
			$contact2 = $dt[3];
			$email2 = $dt[4];
			$query = "REPLACE INTO vendor (vendor_name, contact_no_1, contact_email_1, contact_no_2, contact_email_2) 
			  VALUES ('$vendor_name', '$contact1', '$email1', '$contact2','$email2')";
			  echo(mysql_error().$query);
			$rs = mysql_query($query); 
			if($rs){
				echo '<script type="text/javascript">'; 
				echo 'alert("Succes import....");'; 
				echo 'window.location.href = "?mod=item&sub=vendor";';
				echo '</script>';
			}
		}
	}
}

if(!empty($_FILES)){
	$target_dir = $root_path."item/uploads/";
	$target_file = $target_dir . basename($_FILES["csv"]["name"]);
	
	if (move_uploaded_file($_FILES["csv"]["tmp_name"], $target_file)) {			
		$dt_import = vendor_import($target_file);
	}
}


function vendor_import($path)
{
   $_data = array();
   $row = 1;
   if (($handle = fopen($path, "r")) !== FALSE) {		
		$fp = file($path);	
   }
   while (($data = fgetcsv($handle, 1000, ",",'"')) !== FALSE) {
	   if($row>1){
		   $vendor_name = htmlspecialchars($data[0], ENT_QUOTES);
		   $contact1 = htmlspecialchars($data[1], ENT_QUOTES); 
		   $email1 = htmlspecialchars($data[2], ENT_QUOTES); 
		   $contact2 = htmlspecialchars($data[3], ENT_QUOTES); 
		   $email2 = htmlspecialchars($data[4], ENT_QUOTES);
		   if(!empty($vendor_name)){
			   $_data[] = array(
					'vendor_name' => $vendor_name,
					'contact1'    => $contact1,
					'contact2'    => $contact2,
					'email1'    => $email1,
					'email2'    => $email2,
			   );
		   }
	   }    
	$row++;
   }  
   return $_data;
}

if(!empty($dt_import)){
	echo '<form method="post" id="frm_import">';
	echo '<h4 class="center">Import Preview</h4>';
	echo '<table width=800 cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  	<th width=30>No</th>
	<th>Vendor Name</th>
    <th>Contact 1 No </th>
	<th>Contact 1 Email </th>
	<th>Contact 2 No </th>
	<th>Contact 2 Email </th>
</tr>';
$i= 1;
$valid_data = array();
foreach($dt_import as $_import){
	echo '<tr>';
	echo '<td>'.$i.'</td>';
	echo '<td>'.$_import['vendor_name'].'</td>';
	echo '<td>'.$_import['contact1'].'</td>';
	echo '<td>'.$_import['email1'].'</td>';
	echo '<td>'.$_import['contact2'].'</td>';
	echo '<td>'.$_import['email2'].'</td>';	
	echo '</tr>';
	if(!empty($_import['vendor_name'])){
		$valid_data[] = $_import['vendor_name'].','.$_import['contact1'].','.$_import['email1'].','.$_import['contact2'].','.$_import['email2'];
	}	
	$i++;
}
$_valid = implode(';', $valid_data);
echo '<tr>';
echo '<td height=10 colspan=6 align=center>';
echo '</td></tr>';
echo '<tr>';
echo '<td colspan=6 align=center>';
echo '<button>Cancel</button>&nbsp';
echo '<button type="submit" value="save" name="save">Continue</button>';
echo '</td></tr>';
echo '<tr>';
echo '<td height=2 colspan=6 align=center>';
echo '</td></tr>';
echo '<input type="hidden" name="val_import" id="val_import" value="'.$_valid.'">';
echo '</table></form>';
}else{
?>
<br/>
<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">Import Brand(s) from CSV File</th></tr>
<tr><td height=40>&nbsp;</td></tr>

<tr valign="top">
  <td align="center">
    Select a CSV file 
    <input type="file" name="csv" value="Select...">
  </td>
</tr>

<tr>
  <td align="center"> <input type="submit" name="import" value=" Import Vendor(s) " > </td>
</tr> 
<tr><td height=20>&nbsp;</td></tr>
<tr><td align='center'>To download CSV Template, Click <a href="./?mod=item&sub=vendor&act=import_template">Here</a></td></tr>  
</table>  
</form>
<div class="alertbox" id="msgbox" style="display: none; ">
    <div id="message"></div><br/>
    <div class="alertbutton"><button type="button" id="close" onclick="msgbox_hide()"> Close </button></div>
</div>

<div id='msgok' class='dialog ui-helper-hidden'>
    <div id="message-ok"></div><br/>
</div>


<div id='msgerror' class='dialog ui-helper-hidden'>
    <div id="message-error"><br></div><br/>
    </div>
</div>
<form id="force-import" method="post">
<input type="hidden" name="force-it" value="yes">

</form>
<?php }?>
