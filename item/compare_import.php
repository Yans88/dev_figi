<?php 

if (!defined('FIGIPASS')) exit;
$_msg = null;

$_kind = isset($_POST['kind']) ? $_POST['kind'] : 0;
$_force = true;//isset($_POST['force-it']) ? ($_POST['force-it']=='yes') : false;

$required_fields = array('none', 'user', 'category', 'vendor', 'brand', 'status', 'date of purchase', 'location');

function department_has_category($id_category = 0, $id_department = 0)
{
    $query = "SELECT id_department FROM  department_category WHERE id_category = '$id_category' AND id_department= '$id_department'";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) == 1){
        $rec = mysql_fetch_row($rs);
        return ($id_department == $rec[0] );
    }
    return false;
}

function get_ymd_from_dmy($str)
{
	$result = null;
	/*
	if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $str, $matches)){
		if (strlen($matches[3]) < 4) 
			$matches[3] = '20'.$matches[3];
		$result = "$matches[3]-$matches[2]-$matches[1]";
	}
	*/
	$str = str_replace('/', '-', $str);
	$result = date('Y-m-d', strtotime($str));
	error_log("get_ymd_from_dmy($str): $result");
	return $result;
}

function my_department($id=0)
{
    $result = null;
    $query = "SELECT id_department, department_name WHERE id_department = '$id' ";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs) > 0)){     
        $rec = mysql_fetch_assoc($rs);
		$result = $rec['department_name'];
    }    
    return $result;
}


function item_import($path, $kind = 0, $force = false)
{
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $userid = defined('USERID') ? USERID: 0;
    $row = 0;
    $result['code'] = -2;
    $result['success'] = 0;
    $result['fail'] = 0;
	$my_dept = my_department($dept);
	if (!empty($my_dept)) $my_dept = strtolower($my_dept);

  	$incomplete_data = 0;
	$error_query = 0;
    if (!empty($path) && file_exists($path)) {
		$id_compare = 0;
		// save compare info
		$filename = basename($path);
		$query = "INSERT item_comparison (id_compare, compare_date, compared_by, filename) 
					VALUE (NULL, now(), $userid, '$filename' ) ";
		$rs = mysql_query($query);
	
		if ($rs && mysql_affected_rows()>0)
			$id_compare = mysql_insert_id();
		if ($id_compare>0){
			$query = "TRUNCATE item_comparison_data";
			mysql_query($query);

			ini_set('auto_detect_line_endings', 1);
			if (($fp = fopen($path, 'r')) !== FALSE){
				$row = fgets($fp);
				while ($row && substr($row, 0, 4)!='Zone') $row = fgets($fp);
				$cols = fgetcsv($fp, 1024, ','); // read column names
				//echo count($cols)."\r\n";
				mysql_query('START TRANSACTION'); // prevent auto commit
				$ignore_insert = ($force) ? ' IGNORE ' : '';
				while ($cols = fgetcsv($fp, 1024, ',')){
					error_log(serialize($cols));
					$row++;
					$result['data'] = $cols;
					$result['line'] = $row;
					$can_continue = true;
					$zonename = $cols[0]; // ignored
					$asset_code= $cols[1]; // 
					$asset_name= mysql_real_escape_string($cols[2]); // 
					$serial_code = $cols[3]; // 
					$location_name = mysql_real_escape_string($cols[4]); // location
					$issued_date = get_ymd_from_dmy($cols[5]);
					$purchase_date = get_ymd_from_dmy($cols[6]);
					//error_log("$row: $cols[6] - $purchase_date");
					//error_log(" $cols[6]  ---- $d --- $purchase_date");
					$purchase_value= $cols[7]; // 
					$department_name = mysql_real_escape_string($cols[8]);
					if ($my_dept && strtolower($department_name)!=$my_dept) continue;
					$status_name = $cols[9]; // status
					$retired_date = $cols[10]; // status
					$is_active = $cols[11]; // 
					$vendor_name = mysql_real_escape_string($cols[12]); // vendor
					$invoice_no =  $cols[13]; // 
					$remarks =  $cols[14]; // 
					$reference_no =  $cols[15]; // asset reference no
				        
					$query  = "INSERT $ignore_insert INTO item_comparison_data (asset_code, asset_name, serial_code, location_name, issued_date, purchase_date, purchase_value, department_name, status_name, retired_date, is_active, vendor_name, invoice_no, remarks, reference_no) ";
					$query .= "VALUE ('$asset_code', '$asset_name', '$serial_code', '$location_name', '$issued_date', '$purchase_date', '$purchase_value', '$department_name', '$status_name', '$retired_date', '$is_active', '$vendor_name', '$invoice_no', '$remarks', '$reference_no')";
					@mysql_query($query);
					//error_log($query);
					if (mysql_errno()!=0){
					 //error_log(mysql_error());
						if (mysql_errno() == 1062 || mysql_errno() == 1586){
							$error_query = 1; // duplicate serial no
							//break;
						}
					} else 
					if (mysql_affected_rows() == 1){
						$result['success']++;
					} else
						$result['fail']++;
				} // while
				if (($error_query == 0 && $incomplete_data == 0)||$force){
					$result['code'] = 0; // success, all data corrects and imported
					mysql_query('COMMIT');
				}  else {
					$result['line'] = $row;
					//$result['data'] = $cols;
					if ($error_query != 0){
						$result['code'] = -5; // duplicate
						
					} else{
						$result['code'] = -4; // incomplete data
						$result['field'] = $incomplete_data;
					}
					mysql_query('ROLLBACK');
				}
				fclose($fp);
			} else
				$result['code'] = -3; // system error, can't open the file		
		} else
			$result['code'] = -2; // file not found (upload failed)
		
	} // file exists
    return $result;
}

$err = array();

$filename = null;
if (isset($_POST['import'])) {
	$err['code'] = -2;
	
	$filename = $_FILES['csv']['tmp_name'];
	if (is_uploaded_file($filename)) 
		$err = item_import($filename, $_kind, $_force);
	//print_r($err);
	switch($err['code']){
		case -1 : $_msg = 'Import can not be performed. Invalid Number of Columns.'; break;
		case -2 : $_msg = 'Upload was failed. Import can not be performed.'; break;		
		case -3 : $_msg = 'Internal System error.'; break;
		case -4 : 
			if ($err['field'] != 6) 
				$_msg = 'Incomplete data. Unknown ' . $required_fields[$err['field']]. ' on line ' . $err['line']; 
			else
				$_msg = 'Date of purchase is not set on line ' . $err['line']; 
			break;
		case -5 : $_msg = 'Duplicate serial no: \"'. $err['data'][2] . '\" on row '.$err['line'] ; break;
		case  0 : $_msg = 'Import ' . $err['success'] . ' rows successfully'; break;
	}

}


?>
<br/>
<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">CSV Upload For Item Comparison</th></tr>
<tr><td height=40>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
  </td>
</tr>
<tr valign="top">
  <td align="center">
    <div>Select a CSV file </div>
    <input type="file" name="csv" value="Select...">
  </td>
</tr>
<?php 
	if ((count($err)>0) && ($err['code']<-3)){
?>
<tr valign="top">
  <td align="center">
    <input type="checkbox" checked name="force-it" value="yes"> Force import, duplicate or incomplete data will be ignored). 
  </td>
</tr>
	<?php } ?>
<tr>
  <td align="center"> <input type="submit" name="import" value=" Import Item(s) " > </td>
</tr>  
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
<input type="hidden" name="filename" value="<?php echo $filename?>">
</form>

<script>
	function checkfile(frm){
		if (frm.csv.files.length == 0){
			alert('Please select a csv file to be uploaded!');
			return false;
		}
		return true;
	}
<?php

echo 'var msg = "' .$_msg . '";';
?>
//if (msg != '') msgbox_show(msg);
//$('#close').click(function(){ location.href="./?mod=item"; });

<?php 
if (count($err)>0){ 
	echo 'var err = ' .$err['code'] . ';';
	if ($err['code']==0){ 
?>
var buttons = {'Close': close_dialog}
$('#message-ok').text(msg);
$('#msgok').dialog({
		modal: true, 
		buttons: buttons,
		title: 'Importing result', width: 350, height: 140});
<?php } else { ?>
var buttons = {'Cancel': close_dialog_error};
if (err < -3){
	//buttons.Continue = continue_import;
}
$('#message-error').text(msg);
$('#msgerror').dialog({
		modal: true, 
		buttons: buttons,
		title: 'Importing result', width: 450, height: 160});
<?php 
	} 
}
?>	
function close_dialog(){
	location.href = './?mod=item&sub=compare&act=comparing';
	$('#msgok').dialog('close');
	
}
function close_dialog_error(){
	location.href = './?mod=item&sub=compare&act=import';
	$('#msgerror').dialog('close');
	
}
function continue_import(){
	$('#msgerror').dialog('close');
	//$('#force-import').submit();
}

</script>
