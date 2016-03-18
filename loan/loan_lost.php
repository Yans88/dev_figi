<?php
if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$request = get_request($_id);
$need_approval = ($request['without_approval'] == 0);
$request_items = get_request_items($_id);

$loaned_items = get_request_items($_id);
$returned_items = get_returned_items($_id);
$item_list = build_returned_item_list($loaned_items, $returned_items);

$item_ids = array();
foreach ($returned_items as $id => $rec){
    if ($rec['status'] == 'LOST' && $rec['process'] == 'NONE')
        $item_ids[] = $rec['id_item'];
}

if (isset($_POST['reporting']) && ($_POST['reporting'] == 1)){
    
    $report_remark = mysql_escape_string($_POST['remark']);
    $query = "REPLACE INTO loan_lost(id_loan, reported_by, report_date, report_remark, report_sign) 
              VALUES ($_id, '$_POST[reported_by]', now(), '$report_remark', '$_POST[report_signature]')";
    mysql_query($query);
        
	// update item's status in loan_return_item
    if (count($item_ids)>0){
        $query = "UPDATE loan_return_item SET process = 'DONE' 
                  WHERE id_loan = $_id  AND id_item IN (" . implode(',', $item_ids) . ")";
        mysql_query($query);
        
    }
	
	save_attachment($_id);
    // sending notification
    //send_loosing_item_notification($_id);
    // avoid refreshing the page
    goto_view($_id, RETURNED);    
}


$users = get_user_list();  
$approved_by = !empty($request['approved_by']) ? $users[$request['approved_by']] : 0;
$approve_sign = get_signature($_id, 'approve');
$admin_name = $users[USERID];

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);  
$users = get_user_list();  
$process = array_merge($process, $returns);
/*

$issue_sign = '<img src="'.get_signature($_id, 'issue').'" width=200 height=80>';
$loan_sign = '<img src="'.get_signature($_id, 'loan').'" width=200 height=80>';
$query = "SELECT li.*, date_format(loan_date, '$format_date_only') as loan_date, 
          date_format(return_date, '$format_date_only') as return_date, department_name 
          FROM loan_out li 
          LEFT JOIN department d ON d.id_department = li.id_department 
          WHERE id_loan = $_id";
$rs = mysql_query($query);
//echo mysql_error().$query;
if (mysql_num_rows($rs)>0){
    $issue = mysql_fetch_assoc($rs);
}
*/
$issue = get_request_out($_id);
//$accessories = build_accessories_list($_id);

?>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>

<script type="text/javascript">
function submit_return(){
    var frm = document.forms[0];
    if (frm.reported_by.value == ''){
        alert('Please fill in who is report losing items!');
        return false;
    }
/*
    if (frm.received_by.value == ''){
        alert('Please fill in who receive the item!');
        return false;
    }
*/
    if (isCanvasEmpty){
        alert('Please sign-in for reporter!');
        return false;
    }
    var cvs = document.getElementById('imageView');
    frm.report_signature.value = cvs.toDataURL("image/png");
    frm.reporting.value = 1;
    frm.submit();
    return false;
}
</script>
<h4>Report Loaned Items Lost</h4>
<form method="post"  enctype="multipart/form-data">
<input type="hidden" name="deleted_images" id="deleted_images" value=''>
<input type="hidden" name="deleted_attachments" id="deleted_attachments" value=''>

<table  class="loanview return" cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
    <td>
<?php
    $issue = array_merge($issue, $process);
    //$returns = array_merge($returns, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

if ($need_approval){
    display_issuance_process_approval($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process_approval($returns, $signs); 
} 
else {
    display_issuance_process($issue, $signs); 
    echo '</td></tr><tr><td>';
    display_return_process($returns, $signs); 
}
?>
    </td>
</tr>
<tr><td>
    <table width="100%" cellpadding=3 cellspacing=1>
		<tr valign="top"><th colspan=4>Lost Report</th></tr>
		<tr valign="top">
                    <td>Reported By</td>
                    <td><input type="text" name="reported_by" size=20 value="<?php echo $process['returned_by_name']?>"></td>
                    <td>Signature</td>
                    <td rowspan=2>
                    <div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
                        <div class="m-signature-pad--body">
							<canvas id="imageView" height=80 width=200></canvas>
							<div style="text-align: right;position: relative;top: -80px;">
								<a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
							</div>
						</div>
						</div>
                    </td>
                </tr>
		<tr valign="top" class="alt">
                    <td>Description</td>
                    <td><textarea name="remark" rows=3></textarea></td>
               </tr>
            <tr>
			<td colspan=2>Police Report Documents</td>
            </tr>
            <tr class="alt">
			<td colspan=4>
          <div id="imagelist" class="content">
            <div id="thumbs" class="navigation">
<?php
    $attachments = array();
    //if ($_id > 0) $attachments = get_invoice_attachments($_id);
    $active =  ' class="active" ';
    if (count($attachments) > 0){
      echo '<ul class="attachments" >';
      foreach ($attachments as $attachment){
          $href = './?mod=item&act=get_invoice_attachment&name=' .urlencode($attachment['filename']);
          echo '<li id="att'.$attachment['id_attach'].'"><a href="javascript:void(0)" onclick="delete_attacment('.$attachment['id_attach'].')"><img src="images/delete.png"></a> <a href="'.$href.'" rel="lightbox" >';
          echo $attachment['filename'].'</a></li>';
          $active = null;
      }
      echo '</ul>';
    } else
        echo '-- document is not available! --';
?>
            </div>
        </div>
        <div class="clear"></div>
      <br/>
        Add scanned document, click button below: <input type="file" id="fattachment1" name="fattachment[]" class="multi max-5 accept-gif|jpg|jpeg|png|pdf|xls|doc|ppt|xlsx|docx|pptx" >
        <div id="fattachment-list"></div>
        <script type="text/javascript" language="javascript">
        $(function(){ // wait for document to load 
         $('#fattachment').MultiFile({ 
          list: '#fattachment-list'
         }); 
        });
    </script>
 			
			</td>
            
		</tr>
        </table>
    </td>
</tr>
<tr>
    <td align="right" valign="middle" colspan=2>
    <input type="image" onclick="return submit_return()" src="images/submit.png" />    
    </td>
</tr>
</table>
<input type="hidden" name="report_signature">
<input type="hidden" name="reporting">
</form>
<br/><br/>

<script type="text/javascript" src="js/signature.js"></script>