<?php 

if (!defined('FIGIPASS')) exit;
$_msg = null;

$import_val = !empty($_POST['val_import']) ? $_POST['val_import'] : 0;
$save = !empty($_POST['save']) ? $_POST['save'] : 0;


if($save == 'save'){
	
	if(!empty($import_val)){
		$val = explode(';', $import_val);
		$count_val = count($val);		
		for($i=0; $i<$count_val;$i++){
			$dt = explode(',',$val[$i]);			
			$brand_name = $dt[0];
			$id_manufactures = $dt[1];
			$query = "REPLACE INTO brand (brand_name , id_manufacturer) ";
			$query .= "VALUE('$brand_name', $id_manufactures)";
			$rs = mysql_query($query); 
			if($rs){
				echo '<script type="text/javascript">'; 
				echo 'alert("Succes import....");'; 
				echo 'window.location.href = "?mod=item&sub=brand";';
				echo '</script>';
			}
		}
	}
}

if(!empty($_FILES)){
	$target_dir = $root_path."item/uploads/";
	$target_file = $target_dir . basename($_FILES["csv"]["name"]);
	
	if (move_uploaded_file($_FILES["csv"]["tmp_name"], $target_file)) {			
		$dt_import = brand_import($target_file);
	}
}


function brand_import($path)
{
   $_data = array();
   $row = 1;
   if (($handle = fopen($path, "r")) !== FALSE) {		
		$fp = file($path);	
   }
   while (($data = fgetcsv($handle, 1000, ",",'"')) !== FALSE) {
	   if($row>2){
		   $brand = htmlspecialchars($data[0], ENT_QUOTES);
		   $manufacture = htmlspecialchars($data[1], ENT_QUOTES); 
		   $id_manufacture = getIdManufacture($manufacture);
		   if(!empty($id_manufacture)){
			   $_data[] = array('brand' => $brand, 'manufacture' => $manufacture, 'status'=>'ok', 'id_manufacture' => $id_manufacture);
		   }else{
			   $_data[] = array('brand' => $brand, 'manufacture' => $manufacture, 'status'=>'Manufacture not registered(failed)', 'id_manufacture' => 0);
		   }
	   }    
	$row++;
   }  
   return $_data;
}



function getIdManufacture($manufacture=null){
	$id = 0;
	if(!empty($manufacture)){
		$sql = "SELECT id_manufacturer FROM manufacturer WHERE manufacturer_name ='$manufacture'";
		
		$mysql = mysql_query($sql);
		$data = mysql_fetch_array($mysql);	
		$id = $data['id_manufacturer'];	
	}	
	return $id;
}

if(!empty($dt_import)){
	echo '<form method="post" id="frm_import">';
	echo '<h4 class="center">Import Preview</h4>';
	echo '<table width=500 cellpadding=2 cellspacing=2 class="itemlist" >
<tr height=30>
  	<th width=30>No</th>
	<th width=200>Brand</th>
    <th width=200>Manufacturer</th>
	<th width=300>Status</th>
</tr>';
$i= 1;
$valid_data = array();
foreach($dt_import as $_import){
	echo '<tr>';
	echo '<td>'.$i.'</td>';
	echo '<td>'.$_import['brand'].'</td>';
	echo '<td>'.$_import['manufacture'].'</td>';
	echo '<td>'.$_import['status'].'</td>';
	echo '</tr>';
	if(!empty($_import['id_manufacture'])){
		$valid_data[] = $_import['brand'].','.$_import['id_manufacture'];
	}	
	$i++;
}
$_valid = implode(';', $valid_data);
echo '<tr>';
echo '<td height=10 colspan=4 align=center>';
echo '</td></tr>';
echo '<tr>';
echo '<td colspan=4 align=center>';
echo '<button>Cancel</button>&nbsp';
echo '<button type="submit" value="save" name="save">Continue</button>';
echo '</td></tr>';
echo '<tr>';
echo '<td height=2 colspan=4 align=center>';
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
  <td align="center"> <input type="submit" name="import" value=" Import Brand(s) " > </td>
</tr> 
<tr><td height=20>&nbsp;</td></tr>
<tr><td align='center'>To download CSV Template, Click <a href="./?mod=item&sub=brand&act=import_template">Here</a></td></tr>  
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
