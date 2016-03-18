<?php
if (!defined('FIGIPASS')) exit;


$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

$edit['term'] = null;
$edit['id_term'] = 0;
$edit['active'] = 0;
$edit['valid_from'] = null;
$edit['valid_from_display'] = null;
$edit['valid_to'] = null;
$edit['valid_to_display'] = null;
if (!empty($_POST['edit'])){
	$edit = period_term_get($_POST['edit']);
} 
if (!empty($_POST['save'])){
	
	$term = mysql_real_escape_string($_POST['term']);
	$valid_from  = convert_date($_POST['valid_from']);
	$valid_to  = convert_date($_POST['valid_to']);
	$active = isset($_POST['active']) ? $_POST['active'] : 0;
	$id_term = isset($_POST['id_term']) ? $_POST['id_term']:0;
	$modified_by = USERID;
	if ($id_term==0)
		$query = "INSERT INTO facility_period_term(id_term, term, valid_from, valid_to, active, modified_by, modified_on) VALUE 
					($id_term, '$term', '$valid_from', '$valid_to', $active, $modified_by, now())";
	else
		$query = "REPLACE INTO facility_period_term(id_term, term, valid_from, valid_to, active, modified_by, modified_on) VALUE 
					($id_term, '$term', '$valid_from', '$valid_to', $active, $modified_by, now())";
	if (mysql_query($query)){
		if ($id_term == 0)
			$id_term = mysql_insert_id();
		if ($id_term > 0)
			$_msg = 'Period term information has been saved sucessfully';
		else
			$_msg  = 'Failed to save period term information!';
	} else
		$_msg  = 'Failed to save period term information!';

	//error_log(mysql_error().$query);
	$url = $current_url;//'?mod=facility&sub=period&act=term_list';
	redirect($url, $_msg);
	
} else 
if (!empty($_POST['dele'])){
	$query = "DELETE FROM facility_period_term WHERE id_term = $_POST[id_term]";
	if (mysql_query($query)){
		$ok = mysql_affected_rows();
		if ($ok) $_msg = 'Selected period term has been deleted sucessfully!';
		else $_msg = 'Error occurred when delete period term!';
			
		$url = $current_url;//'?mod=facility&sub=period&act=term_list';
		redirect($url, $_msg);
	}
}

$start = 0;
$limit = RECORD_PER_PAGE;
if ($_page > 0) $start = ($_page-1) * $limit;

$total_item = period_term_count();
$total_page = ceil($total_item/$limit);
$data = period_term_rows($start, $limit);

?>
<div style="text-align: left">
<h3>Manage Period Terms</h3>

<form method="post" id="frm_period_term" style="display: none">
<input type="hidden" name="id_term" value="<?php echo $edit['id_term']?>">
<table style="" class="tbl_period_term_edit">
<tr><td width=70>Term</td><td><input type="text" name="term" value="<?php echo $edit['term']?>"></td></tr>
<tr>
	<td>Valid From</td>
	<td>
		<input type="text" name="valid_from" id="valid_from" value="<?php echo $edit['valid_from_display']?>">
		<span class="field-note">Note: Must start on Monday</span>
	</td>
	</tr>
<tr>
	<td>Valid To</td>
	<td>
		<input type="text" name="valid_to" id="valid_to" value="<?php echo $edit['valid_to_display']?>">
		<span class="field-note">Note: Must end on Sunday</span>
	</td>
</tr>
<tr><td>Active</td><td><input type="checkbox" name="active" value=1 <?php echo ($edit['active']==1)?'checked':null?> ></td></tr>
<tr><td colspan=2 class="center">
<?php 
/*
if (!empty($edit['id_term']))
	echo '<button type="button" name="cancel"> Cancel </button>';
*/
?>
	<button type="button" name="cancel"> Cancel </button>
	<button type="reset" name="reset"> Reset </button>
	<button type="button" name="save"> Save </button>
</td></tr>
</table>
</form>
<form method="post" id="frm_update_period_term">
<input type="hidden" name="id_term" value=0>
</form>
<script>
	var oneDay = 24*60*60*1000;
	var dateformat = "%e-%b-%Y";
	var rangeConv = new AnyTime.Converter({format:dateformat});
	/*
	$("#rangeToday").click( function(e) {
	  $("#valid_from").val(rangeConv.format(new Date())).change(); } );
	$("#rangeClear").click( function(e) {
	  $("#valid_from").val("").change(); } );
	*/
	var toDay = new(Date);
	$("#valid_from").AnyTime_picker({format:dateformat, /*earliest: toDay */});
	$("#valid_from").change(
		function(e) {
		  try {
			var fromDay = rangeConv.parse($("#valid_from").val()).getTime();
			/*
			// checking if start on Monday = 1
			var c = new Date(fromDay);
			if (c.getDay()!=1){
				alert('Valid from must start on Monday!');
				//$("#valid_from").focus();
				return;
			}
			*/
			var dayLater = new Date(fromDay+oneDay);
			dayLater.setHours(0,0,0,0);
			var fiveDaysLater = new Date(fromDay+(6*oneDay));
			fiveDaysLater.setHours(23,59,59,999);
			$("#valid_to").
			  AnyTime_noPicker().
			  removeAttr("disabled").
			  val(rangeConv.format(fiveDaysLater)).
			  AnyTime_picker( {
				earliest: dayLater,
				format: dateformat,
				//latest: fiveDaysLater
				} );
			}
		  catch(e) {
			$("#valid_to").val("").attr("disabled","disabled");
			}
		  } 
	  );
<?php
	if (!empty($edit['id_term'])){
		echo "var fromDay = rangeConv.parse($('#valid_from').val()).getTime();\r\n";
		echo "var dayLater = new Date(fromDay+oneDay);\r\n";
		echo "dayLater.setHours(0,0,0,0);\r\n";
		echo "$('#valid_to').AnyTime_picker({format:dateformat, earliest: dayLater});\r\n";
	}
?>
	  $('#valid_to').change(function(){
	  	var v = $(this).val();
		if (v.length>0)
			$('button[name=save]').removeAttr('disabled');
		else
			$('button[name=save]').attr('disabled', true);
	  });
	$('button[name=save]').click(function(){
	  	var v = $('input[name=term]').val();
		if (v.length==0){
			alert('Please enter term name!');
			return;
		}
	  	v = $('#valid_from').val();
		if (v.length>0){
			var time = rangeConv.parse($("#valid_from").val()).getTime();
			var c = new Date(time);
			if (c.getDay()!=1){
				alert('Valid from must start on Monday!');
				$("#valid_from").focus();
				return;
			}
			 time = rangeConv.parse($("#valid_to").val()).getTime();
			var c = new Date(time);
			if (c.getDay()!=0){
				alert('Valid from must start on Sunday!');
				$("#valid_to").focus();
				return;
			}


			$(this.form).append('<input type="hidden" name="save" value=1>');
			$(this.form).submit();
		} else 
			alert('Please set the valid period for the term!');
	});
	$('button[name=cancel]').click(function(){
		$('#frm_period_term').hide();
	});


function edit(id_term){
	var f = $('#frm_update_period_term').get(0);
	$(f).append('<input type="hidden" name="edit" value='+id_term+'>');
	f.submit();
}

function dele(id_term, title){
	var f = $('#frm_update_period_term').get(0);
	if (confirm('Do you sure delete the period term "'+title+'"?')){
		f.id_term.value = id_term;
		$(f).append('<input type="hidden" name="dele" value=1>');
		f.submit();
	}
}

function add(){
	var id_term = $('input[name=id_term]').val();
	if (id_term > 0)
		location.href = '<?php echo $current_url?>';
	$('#frm_period_term').show();
}
<?php
	if (!empty($edit))
		echo "$('#frm_period_term').show();\r\n";
?>
</script>

<div class="right" style="padding: 2px"><a href="javascript:add()">add period term</a></div>
<?php
 
if ($total_item > 0){
?>
<table width="100%" cellpadding=2 cellspacing=1 class="facility_table tbl_period_term_list" >
<tr>
  <th width=30>No</th>
  <th >Term</th>
  <th width=80>Valid From</th>
  <th width=80>Valid To</th>
  <th width=80>Status</th>
  <th >Modified By </th>
  <th width=130>Modified On</th>
  <th width=80>Action</th>
</tr>
<?php
$row = 0;
foreach ($data as $rec){
	$row++;
	$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
	$link  = '<a href="'.$mod_url.'&sub=period_timesheet&id='.$rec['id_term'].'" alt="view period timesheet"><img class="icon" src="images/script.png"></a> ';
	//if ($i_can_update)
		$link .= '<a href="javascript:edit('.$rec['id_term'].')" alt="edit"><img class="icon" src="images/edit.png"></a> ';
	if ($i_can_delete)
		$link .= '<a href="javascript:dele('.$rec['id_term'].',\''.$rec['term'].'\')" alt="delete" ><img class="icon" src="images/delete.png"></a>';
	//$link .= ' | <a href="./?mod=facility&sub=schedule&act=view&id='.$rec['id_facility'].'">schedule</a>';
	$status = (!empty($rec['active'])) ? 'Active' : 'Not Active';
	echo <<<ROW
<tr $class>
	<td class="right">$row </td>
	<td >$rec[term]</td>
	<td class="center">$rec[valid_from_display]</td>
	<td class="center">$rec[valid_to_display]</td>
	<td class="center">$status</td>
	<td >$rec[modified_by_name]</td>
	<td class="center">$rec[modified_on_display]</td>
	<td class="center">$link</td>
</tr>

ROW;
	}

echo '<tr ><td colspan=8 class="pagination">';
echo make_paging($_page, $total_page, './?mod=facility&sub=period&page=');
//echo '<div class="exportdiv"><a href="./?mod=facility&sub=facility&act=list&do=export" class="button">Export Data</a></div>';
echo '</td></tr>';
        
} else
	echo '<tr ><td colspan=8 class="pagination error">Data is not available!.</td></tr>';
?>
</table>
<br/>
