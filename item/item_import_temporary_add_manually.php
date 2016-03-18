<?php


ob_clean();
$dept = defined('USERDEPT') ? USERDEPT : 0;

if(!empty($_POST['save'])){
	$url = './?mod=item';

	//FROM FORM ADDED
	$asset_no = htmlspecialchars($_POST['asset_number'], ENT_QUOTES);
	$serial_number = htmlspecialchars($_POST['serial_number'], ENT_QUOTES);
	$id_brand = htmlspecialchars($_POST['id_brand'], ENT_QUOTES);
	$id_category = htmlspecialchars($_POST['id_category'], ENT_QUOTES);

	//DEFAULT DATA
	$locations = get_location_list(true,true);
	$vendors = get_vendor_list(true,true);
	$location = "school"; // location
	$vendorname = "Other";

	if (isset($vendors[$vendorname])) { $vid = $vendors[$vendorname]; } else { $vid = add_new_vendor_and_get_id($vendorname);  }
	if (isset($locations[$location])) $lid = $locations[$location];
	else {
			
			if (defined('UNLOCK_LOCATION') && UNLOCK_LOCATION && !empty($location) ){
					$lid = set_location($location); // original location text
					$locations[strtolower($location)] = $lid;
						$can_continue = true && $can_continue;
			} else { 
					$result['unknown_location']++; 
					$can_continue = true; 
					$incomplete_data = 7;
			}	
		}
	error_log("LID :".$location);
	$date_of_purchase = date('Y-m-d H:i:s'); // Default date of purchase
	
	if(empty($asset_no)){
		if (AUTO_GENERATED_ASSETNO)
			$asset_no = generate_asset_no($dept, $category, $date_of_purchase);
	} else {
		$asset_no = htmlspecialchars($_POST['asset_number'], ENT_QUOTES);
	}

	$query  = 'INSERT INTO item (asset_no, serial_no, model_no, issued_to, issued_date, id_category, id_vendor, 
				id_brand, id_location, brief, cost, invoice, date_of_purchase, warranty_periode, 
				warranty_end_date, id_status, status_update, status_defect, id_owner, id_department, id_store, hostname) ';
	$query .= "VALUES ('$asset_no', '$serial_number', '', '1', '0000-00-00 00:00:00', '$id_category', '$vid', '$id_brand', 
				'$lid', '', '', '', '$date_of_purchase', '', '', 
				'6', '', '', $dept, $dept, '', '')";
	mysql_query($query);
	error_log(mysql_error(). $query);

	if (mysql_errno()!=0){
		if (mysql_errno() == 1062 || mysql_errno() == 1586){
			$error_query = 1; // duplicate serial no
			break;
		}
	} else {
		if (mysql_affected_rows() == 1){
			$id_item = mysql_insert_id();
			$rs = mysql_query("select * from item where id_item = $id_item");
			$rec = mysql_fetch_assoc($rs);

			$msg = "Success";
		} else {
			$msg = "Failed";
		}
	}
	redirect($url, $msg);
	
} 

$categories[0] ='-- all categories --';
$categories += get_category_list('EQUIPMENT', $dept);

$brands[0] ='-- all Brands --';
$brands += get_brand_list();

require 'header_popup.php';

$caption = empty($id_student) ? 'Add Manually New Item' : 'Edit Item';

?>
<div id="loading" style="position: absolute; display: none">processing....</div>
<div style="margin-top: 20px;">
<form id="frm_edit" method="post">
<input type="hidden" id="id_student" name="id_student" value=0>
<table  class="tbl_edit student" style="">
<tr><th class="center" colspan=8><?php echo $caption;?></th></tr>
<tr>
	<td>Asset Number</td><td><input type="text" name="asset_number" id='asset_number' maxlength="100" size="30px">
	<span class="field-note error">*</span>
	</td>
</tr>
<tr>
	<td>Serial Number</td><td><input type="text" name="serial_number" id='serial_number' maxlength="100" size="30px"></td>
</tr>
<tr>
	<td>Category</td><td> <?php echo build_combo('id_category', $categories);?> </td>
</tr>
<tr>
	<td>Brand</td><td> <?php echo build_combo('id_brand', $brands);?> </td>
</tr>

<tr><td colspan=2><span class="field-note info"><span style="color:red">*)</span> the field is mandatory</span></td></tr>
<tr>
	<th colspan=2 class="center">
		<input type="button" name="cancel" id="cancel" value=" Cancel" >
		<input type="button" name="edit" id="edit" value=" Add " >
	</th>
	
</tr>
</table>
</form>

<script>
$('#cancel').click(function(){
	parent.jQuery.fancybox.close();
});

$('#edit').click(function(){
	var ok = false;
	var t = $('#asset_number').val();
	var i = $('#serial_number').val();
	if (t.length==0){
		alert('Asset Number cannot be null!');
		$('#asset_number').focus();
	} else if (i.length==0){
		alert('Serial Number cannot be null!');
		$('#serial_number').focus();
	} else ok = true;
	
	if (ok){	
		$('#loading').show();
		$('#frm_edit').append('<input type="hidden" name="save" value=1>');
		$('#frm_edit').submit();
		//parent.jQuery.fancybox.close();
		parent.location.reload();
	}
});

$('#asset_number').focus();

</script>
