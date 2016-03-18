<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}


$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$today = date('j-M-Y');
//print_r($_POST);
if (isset($_POST['issue']) && ($_POST['issue'] == 1)){
    $done_date = convert_date($_POST['date_of_service_done'], 'Y-m-d H:i:s');
    // update request status, date_of_service_done stored in loan_end
    $query = "UPDATE loan_request 
                SET status = 'COMPLETED', end_loan = '$done_date' 
                WHERE id_loan=$_id";
    mysql_query($query);
    //echo mysql_error().$query;
    // update loan process, who render & complete the service
    $admin_id = USERID;
	if (REQUIRE_SERVICE_APPROVAL)
		$query = "UPDATE loan_process SET 
				  issued_by = $admin_id, 
				  issue_date = now(), 
				  issue_remark = '$_POST[issue_remark]' 
				  WHERE id_loan = $_id";
	else
		$query = "INSERT INTO loan_process(id_loan, issued_by, issue_date, issue_remark) 
				  VALUES($_id, $admin_id, now(), '$_POST[issue_remark]')";
    mysql_query($query);
    // keep signature
	if (REQUIRE_SERVICE_APPROVAL)
		$query = "UPDATE loan_signature SET 
				  issue_sign = '$_POST[issue_signature]' 
				  WHERE id_loan = $_id";
	else
		$query = "INSERT INTO loan_signature(id_loan, issue_sign)
                      VALUES($_id, '$_POST[issue_signature]')";
    mysql_query($query);
		
    // avoid refreshing the page
    ob_clean();
    header('Location: ./?mod=service&act=view_issue&id=' . $_id);
    ob_end_flush();
    exit;
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

// get request data
$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y %H:%i') as start_loan, date_format(end_loan, '%d-%b-%Y %H:%i') as end_loan, 
           date_format(request_date, '%d-%b-%Y %H:%i') as request_date, lr.id_category, 
           user.full_name, category_name, quantity, remark, status, nric, contact_no,  
		   approved_by, date_format(approval_date, '$format_date') as approval_date, approval_remark, 
           issued_by, date_format(issue_date, '$format_date') as issue_date, issue_remark, 
           loaned_by, date_format(loan_date, '$format_date') as loan_date, loan_remark
           FROM loan_request lr 
           LEFT JOIN user ON requester = user.id_user 
           LEFT JOIN category ON lr.id_category = category.id_category 
		   LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan 
           WHERE lr.id_loan = $_id  ";

$rs = mysql_query($query);
//echo mysql_error().$query;
if ($rs && (mysql_num_rows($rs)>0))
  $rec = mysql_fetch_assoc($rs);
  
$id_page = get_page_id_by_name('service');
$field_list = get_extra_field_list($rec['id_category'], $id_page);
$field_data = get_extra_data_list($rec['id_category'], $id_page);
$extra_data = null;
$no = 0;
foreach ($field_list as $field){
    $class_name = ($no++ % 2 != 0) ? 'alt' : 'normal';
    $extra_data =<<<ROW
<tr class='$class_name'>
    <td>$field[field_name]</td>
    <td>{$field_data[$field['id_field']]}</td>
</tr>
ROW;
}

$users = get_user_list();  
$approved_by = @$users[$rec['approved_by']];
$approve_sign = get_signature($_id, 'approve');
$admin_name = USERNAME;

$issue = array();
if ($rec['status'] == 'COMPLETED'){
    $issue_sign = '<img src="'.get_signature($_id, 'issue').'" width=200 height=80>';
    $loan_sign = '<img src="'.get_signature($_id, 'loan').'" width=200 height=80>';
    $query = "SELECT li.*, date_format(loan_date, '$format_date_only') as loan_date, 
              date_format(return_date, '$format_date_only') as return_date 
              FROM loan_out li WHERE id_loan = $_id";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_num_rows($rs)>0){
        $issue = mysql_fetch_assoc($rs);
    }
} else {
    $issue['name'] = $rec['full_name'];
    $issue['nric'] = $rec['nric'];
    $issue['contact_no'] = $rec['contact_no'];
    $issue['location'] = null;
    $issue['id_department'] = 0;
    $issue['loan_date'] = $rec['start_loan'];
    $issue['return_date'] = $rec['end_loan'];
    $issue['basic_accessories'] = null;    
    $issue['serial_no'] = null;    
    $rec['loan_date'] = $today . date(' H:i');    
    $rec['issue_date'] = $today . date(' H:i');    
    $rec['issued_by'] = USERNAME;    
}
//$department_combo = build_department_combo($issue['id_department']);
$date_of_service_done = date('d-M-Y H:i');

$caption = 'Service Completion';
echo <<<TEXT
<script>
function loaned_by_update(out_to){
    var loaned_by = document.getElementById('loanedby');
    loaned_by.innerHTML = out_to.value;
}

function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("suggest_serial_no.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}


</script>
<h4>$caption</h4>
<form method="post">
TEXT;

$rec['extra_data'] = $extra_data;
echo display_service_request($rec);

if (REQUIRE_SERVICE_APPROVAL){
	$rec['signature'] = $approve_sign;
	$rec['approved_by'] = $approved_by;
	echo display_service_approval($rec);
}	
$fold_btn = '<div class="foldtoggle"><a id="btn_service_completion_form" rel="open" href="javascript:void(0)">&uarr;</a></div>';
echo <<<TEXT2
    <table width=400 cellpadding=2 cellspacing=1 class="service_table detail issuance" >
      <tr valign="top" align="left"><th align="left" colspan=2>Service Completion $fold_btn</th></tr>  
	  <tbody  id="service_completion_form">
      <tr valign="top" align="left">
        <td align="left" width=150>Completed by</td>
        <td align="left">$rec[issued_by]</td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date of Service Done</td>
        <td align="left"><input type=text name=date_of_service_done id=date_of_service_done size=15 value="$date_of_service_done">
            <input type="image" id="button_date_of_service_done" src="images/cal.jpg" alt="[calendar icon]"/>
            <script>
			$('#button_date_of_service_done').click(
			  function(e) {
				$('#date_of_service_done').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
            </script>
        </td>    
      </tr>
      <tr valign="top" >  
        <td align="left">Remark</td>
        <td align="left"><textarea name=issue_remark cols=32 rows=3>$rec[issue_remark]</textarea></td>    
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">Signature</td>
        <td align="left">
            <div id="container" style="width:201px">
                    <canvas id="imageView" height=80 width=200></canvas>
                    <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                    <a href="javascript:ResetSignature()" class="button clearsign" title="Clear signature space">X</a>
                    </div>
            </div>
        </td>    
      </tr>
	  </tbody>
      <tfoot>
      <tr><td colspan=2>
        <br/> 
        <div style="text-align:right; width:95%" >
        <a class="button" title="Process Service Request" id="btn_issue"
            href="javascript:void(0)">Process</a>
        </div>
        <br>
        </td></tr>
      </tfoot>
    </table>
	<script>
	$('#btn_service_completion_form').click(function (e){
		toggle_fold(this);
	});
	</script>
<Input type=hidden name=issue>
<Input type=hidden name=issue_signature>
</form>
<script>
//loaned_by_update(document.getElementById('refname'));
$('#btn_issue').click(function(e){
    var frm = document.forms[0]
    if (isCanvasEmpty){
        alert('Please sign-in to proceed!');
        return false;
    }
    var ok = confirm('Are you sure to proceed with Service Preparation?');
    if (!ok)
        return false;
    var cvs = document.getElementById('imageView');
    frm.issue_signature.value = cvs.toDataURL("image/png");
    frm.issue.value = 1;
    frm.submit();
    return true;
});

</script>
<script type="text/javascript" src="./js/signature.js"></script>
TEXT2;

?>
