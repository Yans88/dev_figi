<?php



$_limit = RECORD_PER_PAGE;

$_start = 0;

$_page = isset($_GET['page']) ? $_GET['page'] : 1;



$filters = array();

if (!empty($_POST['class']))

	$filters['class'] = $_POST['class'];

$total_item = count_student($filters);

$total_page = ceil($total_item/$_limit);

if ($_page > $total_page) $_page = 1;

if ($_page > 0)	$_start = ($_page-1) * $_limit;



?>



	<table class='itemlist' cellpadding=3 cellspacing=1 width='100%'>

		<tr>

			<th width=40>No</th><th>Full Name</th><th width=80>NRIC</th><th>Email</th><th width=60>Class</th><th width=60>Reg. No</th><th width=100>Parent Info</th><th width=60>Action</th>

		</tr>

<?php 



	$data_row = get_students($filters, $_start, $_limit);

	$counter = 0 + $_start;

	

	while($data = mysql_fetch_array($data_row)){

	$counter++;

	$row_class = ($counter % 2 == 0) ? 'alt' : '';

	$delete_button = "<a href='#Delete' id='del-$data[id_student]' class='del_btn'  title='Delete'> <img class='icon' src='images/delete.png' alt='delete'> </a>";

	

	$_full_name = $data['full_name'];

	$_nric = $data['nric'];

	$_email = $data['email'];

	$_class = $data['class'];

	$_status = $data['active'];

	$edit_button = "<a href='#Edit' title='Edit' class='edit_btn' id='edit-$data[id_student]' > <img class='icon' src='images/edit.png' alt='delete'> </a>";

	if($data['active'] == 0) { $status = "Inactive"; } else { $status = "Active";}
	$action_info = check_parentInfo($data['id_student']);
	if($action_info > 0){ 
		if(SUPERADMIN){
			$button_info = "<a href='#Edit_StudentInfo' title='Edit' class='info_edit_btn' id='edit-$data[id_student]' > <img class='icon' src='images/edit.png' alt='edit'> </a>";
		}
			$button_info_view = "<a href='#View_StudentInfo' title='View' class='info_view_btn' id='view-$data[id_student]' > <img class='icon' src='images/loupe.png' alt='view'> </a>";
		
	} else {
		if(SUPERADMIN){
			$button_info = "<a href='#Edit_StudentInfo' title='Add' class='info_edit_btn' id='edit-$data[id_student]' > <img class='icon' src='images/add.png' alt='add'> </a>";
			$button_info_view = "";
		} else {
		
			$button_info_view = "<a href='#' title='Contact your Super Administrator to add this step'> <img class='icon' src='images/add.png' alt='add'> </a>";
		}
		
	}

	echo "

	

		<tr class='$row_class'>

			<td class='right'>$counter. &nbsp; </td>
			<td>".$data['full_name']."</td>
			<td>".$data['nric']."</td>
			<td>".$data['email']."</td>
			<td class='center'>".$data['class']."</td>
			<td class='center'>".$data['register_number']."</td>
			<td class='center'>
			
				$button_info_view $button_info
				
			</td>
			<td align='center'>".$delete_button."  ".$edit_button." </td>

		</tr>

	

	";

	}



?>

		<tr>

			<td colspan=8 class="center border-top pagination">

			<?php

				echo make_paging($_page, $total_page, './?mod=student&act=list&page=');

			?>

			</td>

		</tr>

	</table>

	

<div id="div_delete" style="display: none; width: 300px; height: 70px;font-weight: bold">

<form id="frm_del" method="post">

<input id="id_student"  name="id_student" value=0 type="hidden">

<h4 style="color: #000" class="center">Delete Confirmation.</h4> 

<p class="center">Do you sure  delete the student?</p>

<p class="center">

<button type="button" id="yes_del" name="confirm_delete_yes" value="yes"> Yes, Confirm! </button>

<button type="button" id="not_del" name="confirm_delete_not" value="not"> Cancel  </button>

</p>

<p class="center" id="delete_progress" style="margin-top: 10px;display: none">processing....</p>

</form>

<script>

$('#not_del').click(function(){

	if ($(this).text()=='Close')

		parent.location.reload();

	parent.jQuery.fancybox.close();



});

$('#yes_del').click(function(){

	$('#delete_progress').show();

	var id=$('#frm_del').find('input[name=id_student]').val();

	if (!$('#frm_del').hasClass('submitted')){

		$('#frm_del').addClass(' submitted');

		$.post('./?mod=student&act=del', {id_student: id, dele: 1}, function(data){

			if (data.length>0){

				var msg = 'Student data failed to be deleted!';

				if ('DELETERESULT:OK'==data) msg = 'Student data has been deleted!';	

				$('#delete_progress').html(msg);

				$('#yes_del').hide();

				$('#not_del').text('Close');

			} else

			$('#delete_progress').hide();

		});

	}

});

</script>

</div>



<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>

<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />



<script>

	

	function getById(id, reg_no, full_name, nric, email, classes, status){

		

		document.getElementById("id_student").disabled = false ;

		document.getElementById("id_student").value = id ;

		document.getElementById("register_number").value = reg_no ;

		document.getElementById("full_name").value = full_name ;

		document.getElementById("nric").value = nric ;

		document.getElementById("email").value = email ;

		document.getElementById("class").value = classes ;

		document.getElementById("edit").disabled = false ;

		document.getElementById("add").disabled = true ;

		

		if(status==1){

			document.getElementById("active").checked = true;

			//alert(status);

		} else {

			document.getElementById("inactive").checked = true;

			//alert(status);

		} 

	

	}

	$(document).ready(function() {

		$('.fancybox').fancybox({padding: 5, width: 440, height: 290});

	});



	$('.edit_btn').click(function (){

		var cols = this.id.split('-');

		var id = cols[1];

		var url = './?mod=student&act=edit&id='+id;

		$.fancybox({href: url, type: 'iframe', padding: 5, width: 440, height: 290});

	});



	$('.del_btn').click(function (){

		var cols = this.id.split('-');

		var id = cols[1];

		//var url = './?mod=student&act=edit&id='+id;

		$('#frm_del').find('input[name=id_student]').val(id);

		$.fancybox({href: '#div_delete', padding: 5, width: 440, height: 290});

	});

	$('.info_edit_btn').click(function (){

		var cols = this.id.split('-');

		var id = cols[1];

		var url = './?mod=student&act=edit_info&id='+id;

		$.fancybox({href: url, type: 'iframe', padding: 5, width: 440, height: 330});

		

	});
	
	$('.info_view_btn').click(function (){

		var cols = this.id.split('-');

		var id = cols[1];

		var url = './?mod=student&act=info_list&id='+id;

		$.fancybox({href: url, type: 'iframe', padding: 5, width: 400, height: 330});

		

	});
</script>
	

