<style>a{text-decoration:none;}</style>
<?php
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$nric = NRIC;
$total_item = count_receive($nric, 0);
$total_page = ceil($total_item/$_limit);
$id_receive = isset($_POST['id_receive']) ? $_POST['id_receive'] : null;
$save_aknow = isset($_POST['save_aknow']) ? $_POST['save_aknow'] : null;
if ($_page > $total_page) $_page = 1;

if ($_page > 0)	$_start = ($_page-1) * $_limit;

if($save_aknow == 'save_aknow' && !empty($id_receive)){
	$query = "update receive set acknowledge=1 where id_receive='$id_receive'";
	mysql_query($query);
}


$myreceive = get_myreceive($nric);
$receive = get_item_receive($myreceive['id_receive']);
$cnt_myreceive = count($myreceive);
?>

<form id="frm_aknowledge" method="post" style="display:none;">
<input type="hidden" id="id_receive" name="id_receive" value="">
<input type="hidden" id="save_aknow" name="save_aknow" value="">
</form>


	<div class="submod_wrap">
  <div class="submod_title"><h4>Receive List</h4></div>
  <div class="submod_links">
	<a class='button fancybox fancybox.iframe' href='./?mod=receive&sub=receive&act=edit'>Add new receive</a>
	<!--<a class='button fancybox fancybox.iframe' href='./?mod=student&act=import'>Import Student</a>-->
	
  </div>
</div>
<div class="clear"></div>
	<table class='itemlist' cellpadding=3 cellspacing=1 width='100%'>
		<tr>
			<th width=40>No</th>
			<th>Date received</th>
			<th>DO Number</th>			
			<th>Invoice Number</th>
			<th>Company name</th>
			<th>Location</th>
			<th>Received by</th>
			<th>For whom</th>
			<th>Acknowledge</th>
			<th width=60>Action</th>
		</tr>

<?php 
	$data_row = get_receive($nric, 0, $_start, $_limit);
	$fullnames = getUsers();
	
	$counter = 0 + $_start;
	while($data = mysql_fetch_array($data_row)){
		$counter++;
		$date=date_create($data['date_received']);
		$row_class = ($counter % 2 == 0) ? 'alt' : '';
		$_do_number = $data['do_number'];
		$_inv_numb = $data['invoice_number'];
		$_company_name = $data['company_name'];
		$nric_for_whom = $data['nric_for_whom'];
		$for_whom = $fullnames[$nric_for_whom]['full_name'];		
		$receivedBy = $fullnames[$data['nric_received']]['full_name'];
		$_location = $data['location_name'];
		$_date_received = date_format($date,"d-M-Y H:i");
		if($data['acknowledge'] > 0){
			$acknowledge = 'Yes';
		}else{
			$acknowledge = 'No';
		}
		if($data['acknowledge'] == 0 && $nric_for_whom == $nric){
			$acknowledge_button = "<a title='Acknowledge' class='ack_btn' id='ack-$data[id_receive]'> <img class='icon' src='images/ok.png' alt='Acknowledge'> </a>";
		}
		//$edit_button = "<a href='#Edit' title='Edit' class='edit_btn' id='edit-$data[id_receive]' > <img class='icon' src='images/edit.png' alt='delete'> </a>";
		//$delete_button = "<a href='#Delete' id='del-$data[id_receive]' class='del_btn'  title='Delete'> <img class='icon' src='images/delete.png' alt='delete'> </a>";
		$view_button = "<a href='./?mod=receive&sub=receive&act=view&id=$data[id_receive]' title='View' class='info_view_btn'><img class='icon' src='images/loupe.png' alt='view'> </a>";
		echo "
			<tr class='$row_class'>
				<td class='right'>$counter. &nbsp; </td>
				<td>".$_date_received."</td>
				<td>".$_do_number."</td>
				<td>".$_inv_numb."</td>
				<td>".$_company_name."</td>
				<td>".$_location."</td>
				<td>".$receivedBy."</td>
				<td>".$for_whom."</td>
				<td align=center>".$acknowledge."</td>
				<td align='center'>".$acknowledge_button." ".$view_button." </td>
			</tr>";
	}
?>

		<tr>

			<td colspan=10 class="center border-top pagination">

			<?php

				echo make_paging($_page, $total_page, './?mod=receive&act=list&page=');

			?>

			</td>

		</tr>

	</table>
	
	
	<div class="clear"></div><br/><br/>
	<table class='itemlist' cellpadding=3 cellspacing=1 width='100%'>
		<tr>
			<th width=40>No</th>
			<th>Date received</th>
			<th>DO Number</th>			
			<th>Invoice Number</th>
			<th>Company name</th>
			<th>Location</th>
			<th>Received by</th>
			<th>For whom</th>
			<th>Acknowledge</th>
			<th width=60>Action</th>
		</tr>

<?php 
	$total_item = count_receive($nric, 1);
	$total_page = ceil($total_item/$_limit);
	if ($_page > $total_page) $_page = 1;
	if ($_page > 0)	$_start = ($_page-1) * $_limit;
	$data_row = get_receive($nric, 1, $_start, $_limit);
	$fullnames = getUsers();
	
	$counter = 0 + $_start;
	while($data = mysql_fetch_array($data_row)){
		$counter++;
		$date=date_create($data['date_received']);
		$row_class = ($counter % 2 == 0) ? 'alt' : '';
		$_do_number = $data['do_number'];
		$_inv_numb = $data['invoice_number'];
		$_company_name = $data['company_name'];
		$_for_whom = $fullnames[$data['nric_for_whom']]['full_name'];
		$_receivedBy = $fullnames[$data['nric_received']]['full_name'];
		$_location = $data['location_name'];
		$_date_received = date_format($date,"d-M-Y H:i");
		if($data['acknowledge'] > 0){
			$acknowledge = 'Yes';
		}else{
			$acknowledge = 'No';
		}
		
		//$edit_button = "<a href='#Edit' title='Edit' class='edit_btn' id='edit-$data[id_receive]' > <img class='icon' src='images/edit.png' alt='delete'> </a>";
		//$delete_button = "<a href='#Delete' id='del-$data[id_receive]' class='del_btn'  title='Delete'> <img class='icon' src='images/delete.png' alt='delete'> </a>";
		$view_button = "<a href='./?mod=receive&sub=receive&act=view&id=$data[id_receive]' title='View' class='info_view_btn'><img class='icon' src='images/loupe.png' alt='view'> </a>";
		echo "
			<tr class='$row_class'>
				<td class='right'>$counter. &nbsp; </td>
				<td>".$_date_received."</td>
				<td>".$_do_number."</td>
				<td>".$_inv_numb."</td>
				<td>".$_company_name."</td>
				<td>".$_location."</td>
				<td>".$_receivedBy."</td>
				<td>".$_for_whom."</td>
				<td align=center>".$acknowledge."</td>
				<td align='center'>".$view_button." </td>
			</tr>";
	}
?>

		<tr>

			<td colspan=10 class="center border-top pagination">

			<?php

				echo make_paging($_page, $total_page, './?mod=receive&act=list&page=');

			?>

			</td>

		</tr>

	</table>
<script>
$('.ack_btn').click(function(){
	var id = $(this).get(0).id.substr(4);
	$('#id_receive').val(id);
	$('#save_aknow').val('save_aknow');
	$('#frm_aknowledge').submit();	
});

function acknowledge(){
	//var id_receive = $('#id_receive').val();
	$('#save_aknow').val('save_aknow');
	$('#frm_aknowledge').submit();
}


var dialog_item =
	$('#dialog_item').dialog({
		modal: true, width: 450, 
		autoOpen: true, 
		buttons:  {
			//'Add Item': '',
			'Acknowledge': acknowledge
		},
		title: 'Receive Form'});

</script>
	






