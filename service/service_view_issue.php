<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';
$transaction_prefix = TRX_PREFIX_SERVICE;

// get request data
$rec = get_service_request($_id);  

$id_page = get_page_id_by_name('service');
/*
$field_list = get_extra_field_list($rec['id_category'], $id_page);
$field_data = get_extra_data_list($rec['id_category'], $id_page);
$extra_data = null;
$no = 0;
foreach ($field_list as $field){
    $class_name = ($no++ % 2 == 0) ? 'alt' : 'normal';
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

$users = get_user_list();  
$approved_by = isset($users[$rec['approved_by']]) ? $users[$rec['approved_by']] : null;
$approve_sign = get_signature($_id, 'approve');
$admin_name = $rec['issued_by_name'];

/*
//$issue_sign = '<img class="signature" src="'.get_signature($_id, 'issue').'" width=200 height=80>';

$query = "SELECT li.*, date_format(loan_date, '$format_date_only') as loan_date, 
          date_format(return_date, '$format_date_only') as return_date, department_name 
          FROM loan_out li 
          LEFT JOIN department d ON d.id_department = li.id_department 
          WHERE id_loan = $_id";
$rs = mysql_query($query);
//echo mysql_error().$query;
$issue=array();
if (mysql_num_rows($rs)>0){
    $issue = mysql_fetch_assoc($rs);
}
*/
if ($rec['status'] == 'COMPLETED')
	$caption = 'Service Request already Completed';
elseif ($rec['status']=='REJECTED')
	$caption = 'Service Request Rejected';
elseif ($rec['status']=='ISSUED')
	$caption = 'Service Request In-Progress';
elseif ($rec['status']=='APPROVED')
	$caption = 'Service Request Approved';

echo "<h4 class='center'>$caption</h4>";
$rec['extra_data'] = $extra_data;
echo display_service_request($rec);
if ($rec['status']=='REJECTED'){
	$reject = get_service_request_rejected($_id);
	$reject['signature'] = $reject['reject_sign'];
	$reject['rejected_by'] = $reject['rejector_name'];
	echo display_service_rejection($reject);
}
if (REQUIRE_SERVICE_APPROVAL && $rec['status']!='REJECTED'){
	$rec['signature'] = $approve_sign;
	$rec['approved_by'] = $approved_by;
	echo display_service_approval($rec);
}
if ($rec['status'] == 'COMPLETED' || $rec['status'] == 'ISSUED'){
	$out = get_service_out($_id);
	$rec += $out;
	$rec['issued_by'] = $rec['issued_by_name'];
	$rec['signature'] = get_signature($_id, 'issue');//$issue_sign;
	display_service_issuance($rec);
}
if ($rec['status']=='COMPLETED'){
	$rec['returned_by'] = $rec['returned_by_name'];
	$rec['signature'] = get_signature($_id, 'return');//$issue_sign;
	display_service_completion($rec);
}

	echo '<div class="right" style="padding-right:240px;">&nbsp;<br/>';
if ($_mod != 'portal'){
	
	echo '<a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a>';
}
if (($rec['status'] == 'ISSUED') && $i_can_update){
    		echo '<a class="button" title="Manage Service Request" href="./?mod=service&act=completion&id='.$_id.'">Completion</a>';
}
?>
</div>
&nbsp;<br/>
<script type="text/javascript">
function print_preview()
{
  var href='./?mod=service&sub=service&act=print_issue&id=<?php echo $_id?>'; 
  var w = window.open(href, 'print_issue');  
}
</script>
