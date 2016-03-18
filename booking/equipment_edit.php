<?php 

if (!defined('FIGIPASS')) exit;
require './item/item_util.php';
$id_facility = isset($_GET['id']) ? $_GET['id'] : 0;
$id_equipment = isset($_GET['id_equipment']) ? $_GET['id_equipment'] : 0;
$_msg = null;
ob_clean();
$facility_data = bookable_facility_info($id_facility);
if (isset($_POST['save'])) {
		
	$id_equipment = isset($_POST['id_equipment']) ? $_POST['id_equipment'] : 0;
	$id_category = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
	$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
	$force = isset($_POST['force']) ? ($_POST['force']==1) : false;
	if ($id_equipment == 0){ 
		$check = get_equipment_by_category($id_facility, $id_category);
		if (!empty($check) && !$force){

			echo <<<FORM
<form id="overwrite" method="post">
<input type="hidden" name="id_facility" value="$id_facility">
<input type="hidden" name="id_category" value="$id_category">
<input type="hidden" name="quantity" value="$quantity">
<input type="hidden" name="save" value=1>
<input type="hidden" name="force" value=0>
</form>
<script>
if (confirm("There is equipment '$check[name]' assigned already!.\\r\\nIf you continue to overwrite will be reset quantity to zero.\\r\\nContinue?")){
	$('input[name=force]').val(1);
	$('#overwrite').submit();
} else {
	location.href = "?mod=facility&act=view&id=$id_facility";
}
</script>
FORM;
		} else {
			$query = "REPLACE INTO facility_equipment (id_equipment, id_facility,  id_category, quantity) 
					  VALUES ($id_equipment, $id_facility, $id_category, $quantity)";
			$rs = mysql_query($query);
			if ($id_equipment == 0 && mysql_affected_rows()>0)
				$id_equipment = mysql_insert_id();
			
			$_msg = "Equipment for Facility has been (re)assigned!";
			echo '<script>alert("'.$_msg.'");parent.jQuery.fancybox.close();</script>';

		}
	}
	return;
		
} else if (isset($_POST['delete'])) {
	$id_facility = isset($_POST['id_facility']) ? $_POST['id_facility'] : 0;
	$id_equipment= isset($_POST['id_equipment']) ? $_POST['id_equipment'] : 0;
	if (empty($facility_data))
		$facility_data = get_facility($id_facility);
	$equipment_data = get_equipment($id_facility, $id_equipment);
	$ok = remove_equipment($id_facility, $id_equipment);
	$_msg = "$equipment_data[name] has been unassigned from $facility_data[facility_name]!";
			echo '<script>alert("'.$_msg.'");parent.jQuery.fancybox.close();</script>';
	//echo '<script>alert("'.$_msg.'");location.href="./?mod=facility&act=view&id='.$id_facility.'"</script>';
	/*
	ob_clean();
	header('Location: ./?mod=facility&sub=equipment&act=del&id=' . $id_facility);
	ob_flush();
	ob_end_flush();
	*/
	return;
}		
	
$equipment_data = get_equipment($id_facility, $id_equipment);
$categories = get_category_list('equipment');   

if ($id_equipment == 0){
	$caption = 'Assign Equipment';
	$equipment_data['id_category'] = 0;
	$equipment_data['quantity'] = 0;
} else {
    $caption = 'Edit Assigned Equipment';
	//$equipment_data = get_equipment($id_equipment);
	$equipment_name = $equipment_data['name'];
}

?>
<link rel="stylesheet" type="text/css" href="./style/default/figi.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<link rel='stylesheet' type='text/css' href='./style/default/jquery-ui-1.8.13.custom.css'/>	
<script type="text/javascript" src="./js/jquery/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="./js/anytimec.js"></script>
<script type="text/javascript" src="./js/moment.min.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		

<script type="text/javascript">
$(function(){
	$('input[name=save]').click( function() {
		var q = $('input[name=quantity]').val();
		$(this).append('<input type="hidden" name="save">');
		this.form.submit();
	});
	  
});
</script>
<style type="text/css">
table.itemlist td { padding: 5px 5px; border-spacing: 1px; border: 0 }
table.itemlist { border-collapse: collapse; }
</style>
<div style="margin: 5px 5px;"><?php echo $caption?></div>
<br/>
<form method="POST">
<table width=400 class="itemlist" >
<tr>
  <td width=100>Facility</td>
  <td><?php echo $facility_data['facility_name']?></td>
</tr>
<tr valign="top" class="alt">
  <td>Equipment</td>
  <td>
  	<?php 
  	if (!empty($equipment_name)){
		echo $equipment_name;
		echo '<input type="hidden" name="id_category" value="'.$equipment_data['id_category'].'">';
	} else
		echo build_combo('id_category', $categories);
		?>
	</td>
 </tr>
<tr valign="top">
  <td>Quantity</td>
  <td>
  	<input type="text" name="quantity" style="width: 40px" value="<?php echo $equipment_data['quantity']?>" >
	</td>
 </tr>
<tr valign="middle">
  <th colspan=2><br/>
	<?php 
	if (!empty($equipment_name)) {
		echo '<input type="button" name="save" value=" Update "> ';
		echo '<input type="reset" value="Reset" />'; 
	} else 
		echo ' <input type="button" name="save" value=" Assign Equipment ">';
	?>
	<input type="button" name="cancel" value=" Cancel " onclick="parent.jQuery.fancybox.close();">

<?php
    
if ($id_facility > 0) {
echo <<<TEXT
<input type="submit" name="delete" value=" Remove " 
	onclick="return confirm('Do you sure remove this equipment from facility \'$facility_data[facility_name]\'?')">
TEXT;
}
?>
<br>&nbsp;
</td></tr>
</table>
<input type="hidden" name="id_facility" value="<?php echo $id_facility?>" > 
<input type="hidden" name="id_equipment" value="<?php echo $id_equipment?>" > 
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
