<?php
if (!defined('FIGIPASS')) exit;
$statuses = array(
    0 => 'Keep Onloan',
    AVAILABLE_FOR_LOAN => 'Available for Loan',
    STORAGE => 'Storage',
    FAULTY=> 'Faulty',
    LOST => 'Lost'
    );

$id_loan = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$this_time = date('j-M-Y H:i');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$item_ids = array();
$request = get_request($id_loan);
$need_approval = ($request['without_approval'] == 0);
$request_items = get_request_items($id_loan);
$accessories = get_accessories_by_loan($id_loan);
$returned_items = get_returned_items($id_loan);
$returned = array();
foreach ($returned_items as $rec) 
	$returned[$rec['id_item']] = $rec;

  
if (isset($_POST['returning']) && ($_POST['returning'] == 1)){

    $return_date = date('Y-m-d H:i:s');
    $received_by = FULLNAME;
    $admin_id = USERID;
    
	$rs = mysql_query("SELECT COUNT(*) FROM loan_return WHERE id_loan = '$id_loan'");
	$row = mysql_fetch_row($rs);
	$return_no = $row[0]+1;

    foreach ($_POST['item_status'] as $id_item => $id_status)
        $item_ids[] = $id_item;
        
	// update item's status 
    $available_items = array();
    $lost_items = array();
    $faulty_items = array();
    $storage_items = array();
    $item_status = $_POST['item_status'];
	$no_of_loaned_items = count($item_ids);
	$current_return = 0;
    if ($no_of_loaned_items>0){
        foreach ($item_ids as $id)
            if (isset($item_status[$id])){
                switch ($item_status[$id]){
                case AVAILABLE_FOR_LOAN: $available_items[] = $id; break;
                case FAULTY: $faulty_items[] = $id; break;
                case LOST: $lost_items[] = $id; break;
                case STORAGE: $storage_items[] = $id; break;
                }
            }
      	  
        update_status_returned_items($id_loan, $return_date, $available_items, $return_no, AVAILABLE_FOR_LOAN);
        update_status_returned_items($id_loan, $return_date, $storage_items, $return_no, STORAGE);
        update_status_returned_items($id_loan, $return_date, $faulty_items, $return_no, FAULTY, 'Faulty on loan #'.$id_loan);
        update_status_returned_items($id_loan, $return_date, $lost_items, $return_no, LOST, 'Lost on loan #'.$id_loan);
        
		$current_return = count($available_items)+count($faulty_items)+count($storage_items);
    }


	$returned_by = mysql_real_escape_string($_POST['returned_by']);
	$remark = mysql_real_escape_string($_POST['return_remark']);
    $query = "INSERT INTO loan_return(id_loan, returned_by, received_by, no_of_returned_item, return_remark, receive_remark, return_date, return_no) 
              VALUE ($id_loan, '$returned_by', '$received_by', $current_return, '$remark', 'Quick Loan', '$return_date', '$return_no')";
    mysql_query($query);
	//error_log(mysql_error().$query);
 
    $query = "UPDATE loan_process SET 
              received_by = $admin_id, 
              receive_date = '$return_date', 
              receive_remark = NULL, 
              returned_by = 0, 
              return_date = '$return_date',  
              return_remark = '$_POST[return_remark]' 
              WHERE id_loan = $id_loan";
    mysql_query($query);
	//error_log(mysql_error().$query);
    
	$no_of_returned_items = count_returned_item($id_loan);
	$is_all_item_returned = $no_of_loaned_items==$no_of_returned_items;
	$loan_status = ($is_all_item_returned) ? 'COMPLETED' : 'PARTIAL_IN';
	
    // update loan status
	$query = "UPDATE loan_request SET status = '$loan_status' WHERE id_loan=$id_loan";
    mysql_query($query);
	//error_log(mysql_error().$query);

    // update loan return dues date if required 
	if (!empty($_POST['change_return_loan_due_date'])){
		$return_time = strtotime($_POST['update_due_date']);
		$return_date = date('Y-m-d H:i:s', $return_time);
		$query = "UPDATE loan_out SET return_date= '$return_date' WHERE id_loan=$id_loan";
		mysql_query($query);
		//error_log(mysql_error().$query);
	}
    
    // sending notification
    send_returned_item_notification($id_loan);
    // avoid refreshing the page
    goto_view($id_loan, RETURNED);    
}


$users = get_user_list();  
$approved_by = !empty($request['approved_by']) ? $users[$request['approved_by']] : 0;
$approve_sign = get_signature($id_loan, 'approve');
$admin_name = $users[USERID];
$process = get_request_process($id_loan);
$returns = get_request_return($id_loan);


$issue_sign = '<img src="'.get_signature($id_loan, 'issue').'" width=200 height=80>';
$loan_sign = '<img src="'.get_signature($id_loan, 'loan').'" width=200 height=80>';

$issue = get_request_out($id_loan);
$signs = get_signatures($id_loan);
$old_due_date = $issue['return_date'];

$item_list = loan_item_list($request_items, $accessories);
$issue['item_list'] = $item_list;
$issue['total_loaned_items'] = count($request_items);

?>

<style>
tr.selected td {color: #E45;}
.error {color: #E45; }
ol { margin: 0; padding: 0}
.scan {font-size: 14pt; font-weight: bold; padding: 7px 5px; width: 200px}
.hide { display: none; }
</style>

<h4>Quick Loan Return Form</h4>
<form method="post" id="ql_form">
<input type="hidden" name="returning">
<input type="hidden" name="returned" id="returned">
<table  width="100%" class="itemlist loan view return" cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request); ?></td></tr>
<tr valign="top"><td><?php display_issuance($issue, false, true); ?> </td></tr>
<tr>
    <td>
<?php
    $issue = array_merge($issue, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

    display_issuance_process($issue, $signs); 

    if ($request['status'] == PARTIAL_IN){
		
		$process['quick_issue'] = $issue['quick_issue'];
		display_return_process($returns, $signs, false, $process); 
    }
?>
        <table width="100%" cellpadding=2 cellspacing=1  >
        <tr valign="top">
        <th colspan=4>Return-In Details</th>
        </tr>
        <tr valign="top">
        <td colspan=4><?php build_item_list_for_return($request_items, $returned_items, 'statusopt'); ?></td>
        </tr>
        <tr valign="top">
            <th >Loan Return</th>
            <th width=200 ></th>
            <th width=206 >Returned By</th>
            <th width=206 >Received By</th>
        </tr>
        <tr valign="top">
            <td ></td>
            <td>Name</td>
            <td><input type="text" name="returned_by" size=22 value="<?php echo $issue['name']?>"></td>
            <td><?php echo FULLNAME?></td>
        </tr>
        <tr valign="top" class="alt">
            <td ></td>
            <td>Date/Time Signature</td>
            <td><?php echo $this_time?></td>
            <td><?php echo $this_time?></td>
        </tr>
        <tr valign="top">
            <td ></td>
            <td>Remarks</td>
            <td colspan=2><textarea name="return_remark" cols=55 rows=3></textarea></td>
        </tr>
        <tr valign="top" class="alt">
            <td colspan=2><input type="checkbox" name="change_return_loan_due_date" id="change_return_loan_due_date"> <label for="change_return_loan_due_date">Change Return Loan Due Date</label> </td>
			<td colspan=2 align="left">
				<div id="due_date_input" style="display: none">
				<input type="text" name="update_due_date" id="update_due_date" size=18 value="<?php echo $old_due_date?>">
				<a id="button_update_due_date" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
				<script>
				$('#button_update_due_date').click(
				  function(e) {
					$('#update_due_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
					e.preventDefault();
				  }
				  );
				</script>
				</div>
			</td>
		  </tr>  
        </table>
    </td>
</tr>
<tr>
    <td align="right" valign="middle" colspan=2>
		<div style="float: left; padding: 5px 10px;"><a href="javascript: open_scan()" class="button" id="btn_scan" style="padding: 15px 15px; font-size: 12pt; margin-top: 5px;cursor: pointer "> Scan Item </a></div>
		<div align="right"><button type="button" id="btn_submit" style="padding: 2px 15px; font-size: 11pt; margin-top: 4px;cursor: pointer; margin-right: 10px "> Submit </button> </div>
    </td>
</tr>
</table>

</form>

<div id="dialog_nric" class="dialog ui-helper-hidden">
	<div style="text-align: center">
		<p>Scan NRIC .... </p>
		<p><input type="text" class="scan" name="scan_nric" id="scan_nric" ></p>
		<p id="dn_msg"></p>
		<br>
	</div>
</div>

<div id="dialog_item" class="dialog ui-helper-hidden">
	<div style="text-align: center">
		<p>Scan items' asset no ...</p>
		<p><input type="text" class="scan" name="scan_asset" id="scan_asset" ></p>
		<p><ol class="asset_list" id="di_msg"></ol></p>
		<br>
	</div>
</div>


<br>&nbsp;<br>
<br>&nbsp;<br>

<script>

var asset_len = <?php  echo ASSETNO_LENGTH?>;
var nric_len = <?php  echo NRIC_LENGTH?>;

$('#change_return_loan_due_date').change(function(){
	if (this.checked){
		$('#due_date_input').show();
		$('#button_update_due_date').trigger('click');
	} else {
		$('#due_date_input').hide();
	}
});

$('#btn_submit').click(function(event){
    var frm = document.forms[0];
    if (frm.returned_by.value == ''){
        alert('Please fill in who return the item!');
        return false;
    }
	var selected_items = 0;
	$('.statusopt').each(function(){
		if ($(this).val() > 0) selected_items++;
	});
	if (selected_items==0){
		alert('Please select item to be returned!');
		return false;
	}
    frm.returning.value = 1;
    frm.submit();
	event.preventDefault();	
});

$('#scan_asset').keyup(function(event){
	var len = $(this).val().length;
	if (len==asset_len || event.keyCode == '13'){
		event.preventDefault();
		select_item(); 
	}
});

var dialog_item =
	$('#dialog_item').dialog({
		modal: true, width: 400, 
		autoOpen: false, 
		buttons:  {
			'Return This': select_item,
			'Done': function() { 
				$('#scan_asset').val(''); 
				dialog_nric.dialog('open'); 
				dialog_item.dialog('close'); 
				}
		},
		title: 'Scan Item'});
	
var dialog_nric =
	$('#dialog_nric').dialog({
		modal: true, width: 400, 
		title: 'Scan NRIC',
		autoOpen: false, 
		buttons:  {
			'Continue': continue_quick_return,
			'Cancel': function() { $('#scan_nric').val(''); dialog_nric.dialog('close'); }
		},
		close: function(){
			//if ($('#scan_nric').val().length<3)
			//	location.href = '?mod=loan';	
		}
		});


function load_customer(nric)
{
    $.post("loan/get_user.php", {nric: ""+nric+""}, function(data){
         
        if(data.length >0) {
			var user = JSON.parse(data);
			$('#dn_msg').html(user.full_name);
			$('input[name=returned_by]').val(user.full_name);
			$(":button:contains('Continue')").focus();
			
        } else {
			$('#dn_msg').html('NRIC is not recognized!!');
		}
    });
}


$('#scan_nric').keyup(function(event){
	var nric = $(this).val();
	if (nric.length>=nric_len || event.keyCode == '13'){
		event.preventDefault();
		//continue_quick_return();
		load_customer(nric);
	}
});

$('#chk_all_location').click(function(){
	if(this.checked){
		$('.chk_my_loc').each(function(){
			this.checked = true;
		});
		$('.my_location').attr('disabled', false);
		$('.all_location').attr('disabled', false);
	}else{
		$('.chk_my_loc').each(function(){
			this.checked = false;
			$('.my_location').attr('disabled', true);
			$('.all_location').attr('disabled', true);
		});
	}
});

$('#all_location').change(function(){
	var id = $('#all_location option:selected').val();
	$('.my_location').val(id);
});

$('.chk_my_loc').click(function(){
	var id = $(this).get(0).id;
	var _id = id.split('_');
	if(this.checked){		
		$('.my_loc_'+_id[2]).attr('disabled', false);
	}else{
		$('.my_loc_'+_id[2]).attr('disabled', true);
	}
});

$('#chk_all_status').click(function(){
	if(this.checked){
		$('.chk_my_status').each(function(){
			this.checked = true;
		});
		$('.statusopt').attr('disabled', false);
		$('.all_statusopt').attr('disabled', false);
	}else{
		$('.chk_my_status').each(function(){
			this.checked = false;
			$('.statusopt').attr('disabled', true);
			$('.all_statusopt').attr('disabled', true);
		});
	}
});


$('.all_statusopt').change(function(){
	var id = $('.all_statusopt option:selected').val();
	$('.statusopt').val(id);
});

$('.chk_my_status').click(function(){
	var id = $(this).get(0).id;
	var _id = id.split('_');;
	if(this.checked){		
		$('.my_stats_'+_id[2]).attr('disabled', false);
	}else{
		$('.my_stats_'+_id[2]).attr('disabled', true);
	}
});


function select_item(){
	var asset_no = $('#scan_asset').val();
	
	if (asset_no.length>0){
		var o = $('#row-'+asset_no);
		//alert(o)
		if (o.length==0) o = $('.sn-'+asset_no);
		if (o.length>0){
			if (o.hasClass('selected'))
				alert(asset_no+' has been selected!.');
			else {
				$('#di_msg').append('<li>'+asset_no+' will be returned.</li>');
				$(":button:contains('Done')").focus();
			}

			o.addClass('selected');
			//o.find('td').css('color', '#F88');
			var s = o.find('select option[value=6]');
			s.attr('selected', true);
			var returned_items = $('#returned').val();
			if (returned_items)
				returned_items.concat(',', asset_no);	
			else
				returned_items = asset_no;	
			$('#returned').val(returned_items);
		} else
			alert(asset_no+' is not in the list or may be has been returned!.');
	}
	$('#scan_asset').val('');

}

function continue_quick_return() { 
	var nric = $('#scan_nric').val();

	dialog_nric.dialog('close');
	dialog_item.dialog('close');
	$('textarea[name=return_remark]').focus();
}

$('#additem').click(function(){
	//dialog_item.dialog('open');
});

function open_scan(){
	dialog_item.dialog('open');
	$('#scan_asset').focus();
}
open_scan();
</script>
