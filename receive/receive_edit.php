<?php
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$do_number = isset($_POST['do_number']) ? $_POST['do_number'] : null;
$inv_numb = isset($_POST['inv_numb']) ? $_POST['inv_numb'] : null;
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : null;
$id_location = isset($_POST['id_location']) ? $_POST['id_location'] : null;
$qty = isset($_POST['qty']) ? $_POST['qty'] : null;
$nric_for_whom = isset($_POST['nric_for_whom']) ? $_POST['nric_for_whom'] : null;
$nric_received = NRIC;
$now = date('Y-m-d H:i:s');
$save = isset($_POST['save']) ? $_POST['save'] : null;

$location_list = get_location_list();
if (count($location_list) == 0)
    $location_list[0] = '--- no location available! ---';

if($save){	
	$query = "insert into receive(do_number,invoice_number,company_name,nric_received,id_location, qty, nric_for_whom,date_received) ";
	$query .= " value('$do_number','$inv_numb','$company_name','$nric_received','$id_location','$qty','$nric_for_whom','$now') ";
	//print_r(mysql_error().$query);	
	$ok_save = mysql_query($query);
	if($ok_save){
		$id_receive = mysql_insert_id();
		$data['id_receive'] = $id_receive;
		if(!empty($_items)){
			foreach($_items as $myItem){
				$query = "insert into receive_item(id_receive,serial_no) ";
				$query .= " value('$id_receive','$myItem') ";
				mysql_query($query);				
			}
		}
		send_receive_alert($data);
		redirect('./?mod=receive');
	}
}

?>

<script>
function fill(id, thisValue) {
	$('#for_whom').val(thisValue);
	$('#nric_for_whom').val(id);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("receive/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#suggestions').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({top:pos.top, left:pos.left});
			}
		});
	}
}

</script>

<style>#id_location{width:243px;}</style>
<br/>
<form id="frm_edit" method="post" autocomplete="off">
<input type="hidden" id="id_student" name="id_student" value=0>
<table  class="tbl_edit student" style="">
<tr><th class="center" colspan=8>Receive Form</th></tr>
<tr>
	<td>DO Number</td><td><input type="text" name="do_number" id='do_number' size="30px">
	</td>
</tr>
<tr>
	<td>Invoice Number</td><td><input type="text" name="inv_numb" id="inv_numb" size="30px"></td>
</tr>
<tr>
	<td>Company Name</td><td><input type="text" name="company_name" id="company_name" size="30px"></td>
</tr>
<tr>
	<td>Location of stored</td>
	<td>
	 <select name="id_location" id="id_location">
		<?php echo build_option($location_list );?>
     </select>
	</td>
</tr>
<tr>
	<td>Quantity</td><td><input type="text" name="qty" id="qty" size="5px"></td>	
</tr>
<tr>
	<td>For Whom</td>
	<td>
	<input type="hidden" name="nric_for_whom" id="nric_for_whom" value="">
	<input type="text" id="for_whom" name="for_whom" class="for_whom" size=30 value="" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('for_whom', this.value);" required>
	 <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
	</td>	
</tr>
<tr>
	<td colspan=2>Description of item receive : </td>
	
</tr>
<tr>
	<td>Serial number</td>
	<td><input type="text" name="serial_numb" id="serial_numb" size="27px">&nbsp;
	 <a href="javascript:void(0)" onclick="display_list()"><img class="icon" src="images/add.png"></a></td>
</tr>
<tr><td colspan=2>
<table width="100%" id="receive_item" class="itemlist receive_item">
</table>
</td>
</tr>
<tr>
	<th colspan=2 class="center">
		<input type="button" name="cancel" id="cancel" value=" Cancel" >
		<input type="submit" name="save" id="save" value=" Save " >
	</th>
	
</tr>
</table>
</form>

<script>

$('#cancel').click(function(){
	var href = "./?mod=receive";
	window.location.href = href;
});

function display_list()
{
    var text = '';
    var cols = '';
	var items = $('#serial_numb').val();
	
    //var recs = items.split(',');
    if (items != ''){
		text = '<tr><td width=113>&nbsp;</td><td> - '+ items+'<input type="hidden" name="items[]" value="'+items+'"></td></tr>';
    } else{
        text = '--- no item specified ---';
	}

    $('#receive_item').append(text);
}

$('#edit').click(function(){
	$('#frm_edit').submit();
});

</script>