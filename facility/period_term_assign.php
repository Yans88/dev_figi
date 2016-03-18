<?php
if (!defined('FIGIPASS')) exit;

$id_facility = isset($_GET['id']) ? $_GET['id'] : 0;
if (empty($id_facility))
	redirect('./?mod=facility', 'Facility does not found!');
if (!empty($_POST['save'])){
	
	$id_term = isset($_POST['id_term']) ? $_POST['id_term']:0;
	if ($id_term>0){
		$query = "INSERT INTO facility_period_map(id_facility, id_term) VALUE ($id_facility, $id_term)";
		if (mysql_query($query)){
			$_msg = 'Period term has been assigned to selected facility!';
		} else
			$_msg  = 'Failed to assign period term for facility!';
		
		$url = '?mod=facility&sub=period&act=timesheet_list&id='.$id_term;
	} else{
		$_msg  = 'Period Term is not selected!';
		$url = '?mod=facility&sub=period&act=term_assign&id='.$id_facility;
	}
	//error_log(mysql_error().$query);
	redirect($url, $_msg);
	
} 

$facility = get_facility($id_facility);
$terms = array('0' => 'Select the period term')+period_term_list();

?>
<div style="text-align: left">
<h3>Manage Period Terms :: Assign to Facility</h3>

<form method="post" id="frm_period_term">
<input type="hidden" name="id_term" value="<?php echo $edit['id_term']?>">
<table style="" class="tbl_period_term_edit">
<tr><td width=70>Facility</td><td><?php echo $facility['facility_no']?></td></tr>
<tr><td>Term</td><td><?php echo build_combo('id_term', $terms)?></td></tr>
<tr><td colspan=2 class="center">
	<button type="button" name="cancel"> Cancel </button>
	<button type="button" name="save"> Assign </button>
</td></tr>
</table>
</form>
<script>
	$('button[name=save]').click(function(){
	  	var v = $('#id_term option:selected').val();
		if (v<=0){
			alert('Please select a period term to assign to the facility!');
			return;
		}
		$(this.form).append('<input type="hidden" name="save" value=1>');
		$(this.form).submit();
	});

	$('button[name=cancel]').click(function(){
		location.href = './?mod=facility&act=view&id=<?php echo $id_facility?>';
	});


</script>
</div>
