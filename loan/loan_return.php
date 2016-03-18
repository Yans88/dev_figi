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

//error_log(serialize($_POST));
if (isset($_POST['returning']) && ($_POST['returning'] == 1)){
    // store loan-out
    $return_date = date('Y-m-d H:i:s');
    $received_by = FULLNAME;
    $admin_id = USERID;

	$rs = mysql_query("SELECT COUNT(*) FROM loan_return WHERE id_loan = '$id_loan'");
	$row = mysql_fetch_row($rs);
	$return_no = $row[0]+1;

    foreach ($_POST['item_status'] as $id_item => $id_status)
        $item_ids[] = $id_item;
    
	// update item's status 
    $item_status = $_POST['item_status'];
	$no_of_loaned_items = count($request_items);
    $current_return = 0;
    if (count($item_ids)>0){
        $available_items = array();
        $lost_items = array();
        $storage_items= array();
        $faulty_items = array();
        foreach ($item_ids as $id)
            if (isset($item_status[$id])){
                switch ($item_status[$id]){
                case AVAILABLE_FOR_LOAN: $available_items[] = $id; break;
                case STORAGE: $storage_items[] = $id; break;
                case FAULTY: $faulty_items[] = $id; break;
                case LOST: $lost_items[] = $id;
                }
            }
        
        update_status_returned_items($id_loan, $return_date, $available_items, $return_no, AVAILABLE_FOR_LOAN);
        update_status_returned_items($id_loan, $return_date, $storage_items, $return_no, STORAGE);
        update_status_returned_items($id_loan, $return_date, $faulty_items, $return_no, FAULTY, 'Faulty on loan #'.$id_loan);
        update_status_returned_items($id_loan, $return_date, $lost_items, $return_no, LOST, 'Lost on loan #'.$id_loan);
        $current_return = count($available_items)+count($storage_items)+count($faulty_items)+count($lost_items);
    }
	
	$returned_by = mysql_real_escape_string($_POST['returned_by']);
	$return_remark = mysql_real_escape_string($_POST['return_remark']);
	$receive_remark = mysql_real_escape_string($_POST['receive_remark']);
    $query = "INSERT INTO loan_return(id_loan, returned_by, received_by, no_of_returned_item, return_remark, receive_remark, return_date, return_no) 
              VALUE ($id_loan, '$returned_by', '$received_by', $current_return, '$return_remark', '$receive_remark', '$return_date', '$return_no')";
    mysql_query($query);
    //error_log(mysql_error().$query);
    $query = "UPDATE loan_process SET 
              received_by = $admin_id, 
              receive_date = '$return_date', 
              receive_remark = '$_POST[receive_remark]', 
              returned_by = 0, 
              return_date = '$return_date',  
              return_remark = '$_POST[return_remark]' 
              WHERE id_loan = $id_loan";
    mysql_query($query);
    
    $query = "UPDATE loan_signature SET 
              receive_sign = '$_POST[receive_signature]', 
              return_sign = '$_POST[return_signature]' 
              WHERE id_loan = $id_loan";
    mysql_query($query);

	//update request status
    $loan_status = 'RETURNED';
    if (!$need_approval) {
        $no_of_returned_items = count_returned_item($id_loan);
        $is_all_item_returned = $no_of_loaned_items==$no_of_returned_items;
        $loan_status = ($is_all_item_returned) ? 'COMPLETED' : 'PARTIAL_IN';
    }
    $query = "UPDATE loan_request SET status = '$loan_status' WHERE id_loan=$id_loan";
    mysql_query($query);
    
    // sending notification
  	send_returned_item_notification($id_loan, $return_date);
    // avoid refreshing the page
    goto_view($id_loan, RETURNED);    
}


$users = get_user_list();  
$approved_by = !empty($request['approved_by']) ? $users[$request['approved_by']] : 0;
$approve_sign = get_signature($id_loan, 'approve');
$admin_name = $users[USERID];
$process = get_request_process($id_loan);

$issue_sign = '<img src="'.get_signature($id_loan, 'issue').'" width=200 height=80>';
$loan_sign = '<img src="'.get_signature($id_loan, 'loan').'" width=200 height=80>';

$issue = get_request_out($id_loan);
$signs = get_signatures($id_loan);
$process = get_request_process($id_loan);
$returns = get_request_return($id_loan);  
$loaned_items = get_request_items($id_loan);
$returned_items = get_returned_items($id_loan);
$accessories = get_accessories_by_loan($id_loan);
$item_list = loan_item_list($loaned_items, $accessories);
$users = get_user_list();  

$issue['item_list'] = $item_list;
$issue['total_loaned_items'] = count($loaned_items);
$issue['total_returned_items'] = count($returned_items);
$accessories = null;
$quick_loan = ($issue['quick_issue']==1) ? 'Quick' : null;
?>
<form method="post">
<table width="100%"  class="itemlist loan return" cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request); ?></td></tr>
<tr valign="top"><td><?php display_issuance($issue, false, true); ?> </td></tr>
<tr>
    <td>
<?php
    $issue = array_merge($issue, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

if ($need_approval){
    display_issuance_process_approval($issue, $signs, false); 
display_return_process_approval($returns, $signs); 
?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist" >
<tr valign="top"> <th colspan=4>Return-In Details </th> </tr>
<tr valign="top">
<td colspan=4><?php build_item_list_for_return($request_items, $returned_items); ?></td>
</tr>
<tr valign="top">
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th align="center">Returned By</th>
    <th align="center">Received By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td>&nbsp;</td>
    <td><input type="text" name="returned_by" size=20 value="<?php echo $issue['name']?>"></td>
    <td><?php echo FULLNAME?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td>&nbsp;</td>
    <td><?php echo $this_time?></td>
    <td><?php echo $this_time?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td>&nbsp;</td>
    <td><textarea name="return_remark" rows=3></textarea></td>
    <td><textarea name="receive_remark" rows=3></textarea></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td>&nbsp;</td>
    <td>
        <div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
    <td>
        <div id="signature-pad2" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView2" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>

</tr>
<?php

} 
    else {
    display_issuance_process($issue, $signs); 
    display_return_process($returns, $signs, false, $process); 

?>
<table width="100%" cellpadding=2 cellspacing=1 class="itemlist">
<thead>
<tr valign="top">
    <th colspan=4>Loan Return-in Details
    <!--<div class="foldtoggle"><a id="btn_loan_return" rel="open" href="javascript:void(0)">&uarr;</a></div>-->
    </th>
</tr>
</thead>
<tbody id="loan_return">
<tr valign="top">
<td colspan=4><?php build_item_list_for_return($request_items, $returned_items, 'statusopt'); ?></td>
</tr>

<tr valign="top">
    <th rowspan=5></th>
    <th width=200 align="center"></th>
    <th width=206 align="center">Returned By</th>
    <th width=206 align="center">Received By</th>

</tr>
<tr valign="top">
    <td>Name</td>
    <td><input type="text" name="returned_by" style="width: 200px" value="<?php echo $issue['name']?>"></td>
    <td><?php echo FULLNAME?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $this_time?></td>
    <td><?php echo $this_time?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td ><textarea name="return_remark"  style="width: 200px" cols=22 rows=3></textarea></td>
    <td ><textarea name="receive_remark" style="width: 200px" cols=22 rows=3></textarea></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td>
		<div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
	</td>
    <td>
		<div id="signature-pad2" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView2" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
</tr>
<?php

}
?>
	</tbody>
        </table>
    </td>
</tr>
<tr>
    <td align="right" valign="middle" colspan=2>
		<div align="right"><img id="btn_submit" src="images/submit.png" style="padding: 5px 15px; font-size: 12pt; margin-top: 5px;cursor: pointer "/> 
    </td>
</tr>
</table>
<input type="hidden" name="receive_signature">
<input type="hidden" name="return_signature">
<input type="hidden" name="returning">
</form>
<br/><br/>
<?php if ($issue['quick_issue']!=1){ ?>
<script type="text/javascript" src="js/signature.js"></script>
<script type="text/javascript" src="js/signature2.js"></script>
<?php } ?>

<script type="text/javascript">
$('#btn_submit').click(function(){
    
    var frm = document.forms[0];
    if (frm.returned_by.value == ''){
        alert('Please fill in who return the item!');
        return false;
    }
    //check for status return
    var selected = 0;
    $('.statusopt').each(function(idx, elm){
        if(elm.value>0) selected++;
    });
    if (selected==0){
        alert('You must set status of an item that will be returned.');
        return false;
    }
    
<?php if ($issue['quick_issue']!=1){ ?>

    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var cvs = document.getElementById('imageView');
    frm.return_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.receive_signature.value = cvs.toDataURL("image/png");    
<?php } ?>
    frm.returning.value = 1;
    frm.submit();
    return false;
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
	var lengths = 0;
	var cnt_chk_my_locs = $('input[class="chk_my_loc"]').length;
	var cnt_chk_my_loc = $(".chk_my_loc:checked").length;
	if(cnt_chk_my_loc != cnt_chk_my_locs){
		$('#chk_all_location').attr('checked', false);
		$('.all_location').attr('disabled', true);
	}else{
		$('#chk_all_location').attr('checked', true);
		$('.all_location').attr('disabled', false);
	}
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
	var _id = id.split('_');
	var cnt_chk_my_statuses = $('input[class="chk_my_status"]').length;
	var cnt_chk_my_status = $(".chk_my_status:checked").length;
	
	if(cnt_chk_my_status != cnt_chk_my_statuses){
		$('#chk_all_status').attr('checked', false);
		$('.all_statusopt').attr('disabled', true);
	}else{
		$('#chk_all_status').attr('checked', true);
		$('.all_statusopt').attr('disabled', false);
	}
	
	if(this.checked){		
		$('.my_stats_'+_id[2]).attr('disabled', false);
	}else{
		$('.my_stats_'+_id[2]).attr('disabled', true);
	}
});

</script>

