<?php 

if (!defined('FIGIPASS')) exit;
$id_location = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : 0);
//$id_location = isset($_POST['id']) ? $_POST['id'] : $id_location;
$_msg = null;
require 'facility/facility_util.php';

if (isset($_POST['save'])) {
	$exists = false;
	$id_facility = isset($_POST['id']) ? $_POST['id'] : 0;
	if (empty($id_facility))
		$id_facility = isset($_GET['id']) ? $_GET['id'] : 0;
	$description = mysql_real_escape_string($_POST['description']);
	$query = "REPLACE INTO bookable_facility (id_facility, id_location, description, id_term)  
			  VALUE ($id_facility, '$_POST[id_location]', '$description', $_POST[id_term])"; 
	$rs = mysql_query($query);
	error_log(mysql_error().$query);
	if (mysql_affected_rows()>0){
		if ($id_facility == 0) {
			$id_facility = mysql_insert_id();
			$_msg = "New bookable facility has been added!";
		} else
			$_msg = "Bookable facility data has been updated!";
	} else
		$_msg = "Can no save facility data !";

	redirect($submod_url.'&act=view&id='.$id_facility, $_msg);
	
	
} else if (isset($_POST['delete'])) {
	$id_location = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=facility&sub=facility&act=del&type='.$_type.'&id=' . $id_location);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($id_location > 0) {
	$data_item = bookable_facility_info($id_location);
    $caption = 'Edit Existing Facility';
} else {
    $data_item['description'] = null;
    $data_item['id_facility'] = 0;
    $data_item['id_location'] = 0;
    $data_item['id_term'] = 0;
    $caption = 'Create New Facility';
}
$period_terms = period_term_list(true);
$bookable_facilities = bookable_facility_rows(1, 500);
$existing = array();
foreach($bookable_facilities as $rec)
	$existing[$rec['id_location']] = $rec['id_facility'];


$facilities = get_location_list();
$available_facilities = array('0' => '* select a location');
foreach($facilities as $id_location => $location_name)
	if (!isset($existing[$id_location]))
		$available_facilities[$id_location] = $location_name;

?>
<div class="submod_wrap">
	<div class="submod_title"><h4>Add / Edit a Facility for Booking</h4></div>
	<div class="submod_links"> 
	<a class="button" href="<?php echo $submod_url?>&act=list">Back to the List</a>
	</div>
</div>
<div class="clear"></div>

<form method="POST" id="frm">
<input type="hidden" name="id_facility" value="<?php echo $data_item['id_facility']?>">
<input type="hidden" name="save" value="" > 

<div style="width:450px; margin: 20px auto">
<div class="header"><?php echo $caption?></div>
<table style="width: 100%; margin: 0 0; padding: 0 0; border-spacing: 0" class="itemlist" >
<tr valign="top">
  <td width=100>Facility Name</td>
  <td>
    <?php echo build_combo('id_location', $available_facilities, $data_item['id_location']); ?>
    <?php
        //if ($data_item['id_location']>0) echo '<button type="button" onclick="enable_location()">Change</button>'; 
    ?>
	<br><span class="field-note info">Select from available facilities</span>
  </td>
 </tr>

<tr valign="top">
  <td>Period Term</td>
  <td><?php echo build_combo('id_term', $period_terms,  @$data_item['id_term'])?></td>
 </tr>
 <tr valign="top" class="">
  <td>Description</td>
  <td>  
    <textarea name="description" rows=3 cols=40><?php echo @$data_item['description']?></textarea>
  </td>
 </tr>
<tfoot>
<tr valign="top" >
  <th colspan=2 style="padding: 5px 5px">
	
	<button  type="button" id="save" >Save</button>
	<button  type="button" id="cancel" >Cancel</button>
</th>
  </tr>
</tfoot>
</table>
</div>
</form>
<br/>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
<script type="text/javascript">

$('#save').click(function(){
	var location = $('#id_location').val();
	if (location > 0){
	  var frm = $('#frm').get(0) ;
	  frm.save.value = 1;
	  frm.submit();
	} else 
		alert('Please select a facility!');
});
 
$('#cancel').click(function (){
    location.href="./?mod=booking&sub=facility&act=list";
 });

function enable_location()
{
    $('#id_location').removeAttr('disabled');
}
$('#btn_manage_period_terms').click(function(){
    location.href="./?mod=booking&sub=period&act=term_list";
});

</script>

