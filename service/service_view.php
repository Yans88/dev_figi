<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$transaction_prefix = TRX_PREFIX_SERVICE;
$required_approval = REQUIRE_SERVICE_APPROVAL;

$rec = get_service_request($_id);  

$id_page = get_page_id_by_name('service');
/*
$field_list = get_extra_field_list($rec['id_category'], $id_page);
$field_data = get_extra_data_list($rec['id_category'], $id_page);
$extra_data = null;
$no = 0;
foreach ($field_list as $field){
    $class_name = ($no++ % 2 != 0) ? 'alt' : 'normal';
    if (strtoupper($field['field_type']) == 'BOOLEAN')
        $value = ($field_data[$field['id_field']] == '1') ? 'Yes' : 'No';
    else
        $value = $field_data[$field['id_field']];
    $extra_data .=<<<ROW
<tr class='$class_name'>
    <td>$field[field_name]</td>
    <td>$value</td>
</tr>
ROW;
}
*/
$extra_data = get_extra_form($rec['id_category'], $id_page);

$quantity = 'none';// ($rec['category_type'] == 'SERVICE') ? 'none' : $rec['quantity'];

if ($rec['status'] == APPROVED) 
  $caption = 'Approved Service Request';
elseif ($rec['status'] == REJECTED) {
  $caption = 'Rejected Service Request';
  
  $query = "SELECT lr.*, user.full_name rejector_name FROM loan_reject lr
			LEFT JOIN user ON user.id_user = lr.rejected_by 
			WHERE id_loan = $_id";
  $rs = mysql_query($query);
  if ($rs) $reject = mysql_fetch_assoc($rs);
} else
  $caption = 'Submitted Service Request';

echo '<h4 class="center">'.$caption.'</h4>';
$rec['extra_data'] = $extra_data;
echo display_service_request($rec);

if ($rec['status'] == REJECTED) {
	$reject = get_service_request_rejected($_id);
	$rec = $reject;
	$rec['signature'] = $reject['reject_sign'];  
	$rec['rejected_by'] = $reject['rejector_name'];  
	echo display_service_rejection($rec);
} else
if (!$required_approval){
    if ($rec['status'] == 'PENDING'){
    	echo '<br/>';
    	if ($i_can_delete)
    		echo '<a class="button" title="Reject Service Request" href="./?mod=service&act=unapprove&id='.$_id.'">Reject</a>';
    	if ($i_can_update)
    		echo '<a class="button" title="Manage Service Request" href="./?mod=service&act=issue&id='.$_id.'">Manage</a>';
    }
} 
else { // approval type
	if ($rec['status'] == 'APPROVED') {   
		$users = get_user_list();
		$rec['signature'] = get_signature($_id, 'approve');
		$rec['approved_by'] = $users[$rec['approved_by']];
		display_service_approval($rec);
		if ($i_can_update){
?>
    <div style="width:500px; text-align: right">&nbsp;<br/>
    <a class="button" title="Back to Service Request list"
        href="./?mod=service&act=list">Cancel</a>
    <a class="button" title="Manage Service Request"
        href="./?mod=service&act=issue&id=<?php echo $_id?>">Manage</a>
  </div><br/>
  
<?php
		}
	} // without approval, automatic approved 
}
?>
<br/>&nbsp;<br/>
