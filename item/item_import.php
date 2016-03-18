<?php 

if (!defined('FIGIPASS')) exit;
$_msg = null;

$_kind = isset($_POST['kind']) ? $_POST['kind'] : 0;
$_force = false;//isset($_POST['force-it']) ? ($_POST['force-it']=='yes') : false;

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

function item_import($path, $kind = 0, $force = false)
{
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $row = 0;
    $result['code'] = -2;
    $result['success'] = 0;
    $result['fail'] = 0;
    $result['unknown_user'] = 0;
    $result['unknown_category'] = 0;
    $result['unknown_vendor'] = 0;
    $result['unknown_brand'] = 0;
    $result['unknown_status'] = 0;
    $result['unknown_location'] = 0;
    
    /*
     * checks for required data
     *  - category
     *  - vendor
     *  - brand
     *  - status
     *  - issued-to
     * checks duplication on
     *  - serial no
     */
	$incomplete_data = 0;
	$error_query = 0;
    if (!empty($path) && file_exists($path)) {
		ini_set('auto_detect_line_endings', 1);
		
		
		
		
        if (($fp = fopen($path, 'r')) !== FALSE){
            $cols = fgetcsv($fp, 1024, ',');
            if (count($cols) >= 24){ // item:25, spec:29 = 54
                $users = get_user_list(true, false, true);
                $categories = get_category_list(null,$dept,true,true);
                $vendors = get_vendor_list(true,true);
                $brands = get_brand_list(true,true);
                $statuses = get_status_list(true,true);
                $locations = get_location_list(true,true);
				$manufactures = get_manufacturer_list(true, true);
				$departments = get_department_list(true,true,true);
                //print_r($statuses);
				mysql_query('START TRANSACTION'); // prevent auto commit
                
                while ($cols = fgetcsv($fp, 1024, ',')){
                    $row++;
					
					$result['data'] = $cols;
					$result['line'] = $row;
                    $can_continue = true;
					
                    $username = strtolower($cols[2]); // issued to
                    $catname = strtolower($cols[4]); // category
                    $vendorname = strtolower($cols[5]); // vendor
                    $brandname = strtolower($cols[8]); // brand
                    $department = strtolower($cols[7]);
                    $location = strtolower($cols[9]);
                    
                    $statusname = strtolower($cols[18]); // status ---> change
					$store_type = strtolower($cols[13]);
					if($store_type){
						$id_store = getIdStore($store_type);
					} else {
						$id_store = 6;
					}
					$hostname = strtolower($cols[10]);
					
					if (isset($departments[$department])) $dept = $departments[$department];
                    else { $dept = 0; $result['unknown_department']++; $can_continue = false; $incomplete_data = 4;  }
					
					//ISSUED TO
                    if (isset($users[$username])) { $uid = $users[$username]; } else { $uid = 0; $result['unknown_user']++; $can_continue = false; $incomplete_data = 1; }
					
                    if (isset($categories[$catname])) {
                        $cid = $categories[$catname]; // validate if category owned by user's department
                        if (!department_has_category($cid, $dept)){
                            $cid = 0; $result['unknown_category']++; $can_continue = false; $incomplete_data = 2;
                        }
                    }
                    else { $cid = 0; $result['unknown_category']++; $can_continue = false; $incomplete_data = 2;  }
                    if (isset($vendors[$vendorname])) $vid = $vendors[$vendorname];
                    else { $vid = 0; $result['unknown_vendor']++; $can_continue = false; $incomplete_data = 3;  }
                    if (isset($brands[$brandname])) $bid = $brands[$brandname];
                    else { $bid = 0; $result['unknown_brand']++; $can_continue = false; $incomplete_data = 4;  }
					
					
					
					//STATUS
                    if (isset($statuses[$statusname])) {						
							$sid = $statuses[$statusname];						
                    } else { 
						$sid = 0; $result['unknown_status']++; $can_continue = false; $incomplete_data = 5;  
					}
                    
                    if (empty($cols[15])){ //-----> change
                        $incomplete_data = 6;
                        $can_continue = false;
                    	$date_of_purchase = null;//date('Y-m-d H:i:s');
                    } else{
                        $date_of_purchase = convert_uk_date($cols[15], 'Y-m-d H:i:s'); //-----> change
                    }
                    
                    if (isset($locations[$location])) $lid = $locations[$location];
                    else {
                    	$lid = 0; 
						if (defined('UNLOCK_LOCATION') && UNLOCK_LOCATION && !empty($location)){
								$lid = set_location($location); // original location text
								$locations[strtolower($location)] = $lid;
									$can_continue = true && $can_continue;
						} else { 
								$result['unknown_location']++; 
								$can_continue = true; 
								$incomplete_data = 7;
						}	
                    }
                    
                    //error_log('location: '.$lid.', can continnue: '.$can_continue);
                    if (!$can_continue) 
                    	if (!$force) break;
                    
                    if (empty($cols[3]))
                        $issued_date = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else
                        $issued_date = convert_uk_date($cols[3], 'Y-m-d H:i:s');
                    if (empty($cols[16])) //-----> change
                        $warranty_end_date = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else
                        $warranty_end_date = convert_uk_date($cols[16], 'Y-m-d H:i:s'); //-----> change
                    if (empty($cols[20])) //-----> change
                        $status_update = '0000-00-00 00:00:00';//date('Y-m-d H:i:s');
                    else 
                        $status_update = convert_date($cols[20], 'Y-m-d H:i:s'); //-----> change
                    if (empty($cols[18])) $cols[18] = 0; //-----> change
                    $asset_no = $cols[0];
                    if (AUTO_GENERATED_ASSETNO)
                        $asset_no = generate_asset_no($dept, $cid, $date_of_purchase);

                    $query  = 'INSERT INTO item (asset_no, serial_no, model_no, issued_to, issued_date, id_category, id_vendor, 
                                id_brand, id_location, brief, cost, invoice, date_of_purchase, warranty_periode, 
                                warranty_end_date, id_status, status_update, status_defect, id_owner, id_department, id_store, hostname) ';
                    $query .= "VALUES ('$asset_no', '$cols[0]', '$cols[1]', '$uid', '$issued_date', '$cid', '$vid', '$bid', 
                                '$lid', '$cols[11]', '$cols[12]', '$cols[14]', '$date_of_purchase', '$cols[16]', '$warranty_end_date', 
                                '$sid', '$status_update', '$cols[20]', $dept, $dept, '$id_store', '$hostname')";
                    mysql_query($query);
                    //echo mysql_error(). $query;
					if (mysql_errno()!=0){
						if (mysql_errno() == 1062 || mysql_errno() == 1586){
							$error_query = 1; // duplicate serial no
							break;
						}
					} else 
                    if (mysql_affected_rows() == 1){
                        $id_item = mysql_insert_id();
						$rs = mysql_query("select * from item where id_item = $id_item");
						$rec = mysql_fetch_assoc($rs);
						//print_r($rec);
                        
                        //$vendor_name = $cols[7];
                        // update  vendor //
                        if ($vid > 0)
                            $query  = "UPDATE vendor SET contact_no_1='$cols[21]',contact_email_1='$cols[22]',
                                        contact_no_2='$cols[23]',contact_email_2='$cols[24]' WHERE id_vendor = $vid ";
                        else
                            $query  = "INSERT INTO vendor(vendor_name,contact_no_1,contact_email_1,contact_no_2,contact_email_2) 
                                        VALUES('$vendor_name','$cols[21]','$cols[22]','$cols[23]','$cols[24]')";
                        mysql_query($query);
                        if ($vid == 0){ // new vendor, update item
                            $vid = mysql_insert_id();
                            $query = "UPDATE item SET id_vendor = $vid WHERE id_item = $id_item";
                            mysql_query($query);
                        }
                      
                        // store spec
                        if ($kind == 1) { // 1-included in csv, 0-excluded
                            $spec_list = get_specification_list($cid);
                            $specs = array_keys($spec_list);
                            for ($i = 0; $i < count($specs); $i++){
                                $idx = 25 + $i; // start at index 25th in csv zero based
                                $query  = "REPLACE INTO item_specification(id_item, spec_id, spec_value) ";
                                $query .= "VALUES('$id_item', '$specs[$i]', '$cols[$idx]')";
                                mysql_query($query);
                            }
                        }
                        $result['success']++;
                    } else
						$result['fail']++;
                }
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
            } else // colums is mismatch
                $result['code'] = -1;
            fclose($fp);
        } else
            $result['code'] = -3; // system error, can't open the file		
    } else
		$result['code'] = -2; // file not found (upload failed)
	
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
	/*
	{
        $enter = '<br/>';
        $tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
		$_msg = "Import Statistic:" . $enter . $tab . 
                "Success: $err[success] item(s)" . $enter . $tab . 
                "Fail: $err[fail] item(s)" . $enter;
        if ($err['unknown_user'] > 0)
            $_msg .= $tab . $tab . "Unknown Issuer: $err[unknown_user]" . $enter;
        if ($err['unknown_category'] > 0)
            $_msg .= $tab . $tab . "Unknown Category: $err[unknown_category]" . $enter;
        if ($err['unknown_vendor'] > 0)
            $_msg .= $tab . $tab . "Unknown Vendor: $err[unknown_vendor]" . $enter;
        if ($err['unknown_brand'] > 0)
            $_msg .= $tab . $tab . "Unknown Brand: $err[unknown_brand]" . $enter;
        if ($err['unknown_status'] > 0)
            $_msg .= $tab . $tab . "Unknown Status: $err[unknown_status]" . $enter;
    }
    */
}



function getIdStore($store_type){

	$sql = "SELECT id_store FROM item_store_type WHERE title = '$store_type'";
	$mysql = mysql_query($sql);
	$data = mysql_fetch_array($mysql);
	
	return $data['id_store'];
	
}



?>
<br/>
<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">Import Item(s) from CSV File</th></tr>
<tr><td height=40>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
    Specification data: &nbsp;
		<input type="radio" name="kind" value="0" <?php echo ($_kind==0) ? 'checked' : ''?> >Excluded &nbsp;
		<input type="radio" name="kind" value="1" <?php echo ($_kind==1) ? 'checked' : ''?>>Included 	
  </td>
</tr>
<tr valign="top">
  <td align="center">
    Select a CSV file 
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
<tr><td height=30>&nbsp;</td></tr>
<tr><td align='center'>To download CSV Template, Click <a href="#" onclick="return download_csv_template()">Here</a></td></tr> 
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
	location.href = './?mod=item&act=import';
	$('#msgok').dialog('close');
	
}
function close_dialog_error(){
	location.href = './?mod=item&act=import';
	$('#msgerror').dialog('close');
	
}
function continue_import(){
	$('#msgerror').dialog('close');
	//$('#force-import').submit();
}
function download_csv_template(){
		location.href="./?mod=item&act=import_template";
	}
</script>
