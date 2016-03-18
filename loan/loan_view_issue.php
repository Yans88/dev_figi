<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$key_import = !empty($_POST['btn_import']) ? $_POST['btn_import'] : 0;



if ($key_import == 'Upload'){
	$target_dir = $root_path."loan/aup/";
	$target_file = $target_dir . basename($_FILES["aup"]["name"]);
	$target_file_db = "loan/aup/".basename($_FILES["aup"]["name"]);
	$filename = basename($_FILES["aup"]["name"]);
	if (move_uploaded_file($_FILES["aup"]["tmp_name"], $target_file)) {	
		$query = "delete from loan_out_aup where id_loan = $_id";	
		mysql_query($query);
		$query = "replace into loan_out_aup(id_loan, path_aup, filename) values ($_id, '$target_file_db', '$filename')";
		mysql_query($query);
	}
}



$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$request = get_request($_id);
$need_approval = ($request['without_approval'] == 0);
$request_items = get_request_items($_id);
$accessories = get_accessories_by_loan($_id);
$item_list = loan_item_list($request_items, $accessories);
$users = get_user_list();  
$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$issue['chk'] = $issue['checklist'];//get_checklist($_id);

$parent_info = get_parent_info($request['id_user']);
$issue['parent_name'] = $parent_info['father_name'];

$issue['total_loaned_items'] = count($request_items);
$issue['id_category'] = $request['id_category'];
$issue['students_loan'] = $request['students_loan'];
$issue['parent_info'] = $parent_info;

//$issue['total_returned_items'] = count($returned_items);
$quick_loan = ($issue['quick_issue']==1) ? 'Quick' : null;
$aup = get_aup($_id);

?>
<table width="100%" class="itemlist issue" >
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];

if ($need_approval){
    display_issuance_process_approval($issue, $signs); 
} 
else {
    display_issuance_process($issue, $signs); 
}
?>
  </td>
</tr>
<?php 
if ($request['status'] == 'PARTIAL_IN'){
	$returns = get_request_return($_id);  
	echo '<tr><td>';
    display_return_process($returns, $signs, false, $process); 
	echo '</td></tr>';
	
}
if (!empty($issue['chk'])){ 
?>
<!-- 13052015 add by hansen for point 23 --> 
<tr valign="top"><td><?php display_checklist($issue);?></td></tr>
<!-- End of add by hansen for point 23 -->




<?php
} // chk
if ($issue['quick_issue']!=1){
?>
<tr>
    <td colspan=2 valign="middle">
    <table cellpadding=2 cellspacing=1 >
        <tr>
            <td width="100%"><div class="note" id="issue_note" ><?php echo $messages['loan_issue_note']?></div></td>
        </tr>
    </table>
    </td>
</tr>
<?php
}

if(!empty($aup)){
	$aup_file = $aup['path_aup'];
?>
<tr>
    <td colspan=2>   
        <tr>	
		
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;			
			<strong>AUP Filename : </strong><a href="<?php echo $aup_file;?>"><?php echo $aup['filename'];?></a>
			</td>
        </tr>
    
    </td>
</tr>
<?php } ?>

<tr class=alt id="aup_upl">
    <td colspan=2 valign="">
    <table cellpadding=2 cellspacing=1 align="left">
        <tr class=alt>
            <td width="100%">
			<div class="" id="" >
			
			<form method="POST" enctype="multipart/form-data">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="file" name="aup" id="aup" value="Select AUP file">
				<input type="submit" name="btn_import" id="btn_import" value="Upload" > 
			</form>
			</div></td>
        </tr>
    </table>
    </td>
</tr>



<tr valign="middle">
  <td colspan=2 align="right" >            
    <!-- <a class="button" href='./?mod=loan&sub=loan&act=lost&id=<?php echo $_id?>'>Item is Lost</a> &nbsp; -->
<?php

if ( (USERGROUP == GRPADM) && !SUPERADMIN && (USERDEPT==$request['id_department'])) {
	
	if($issue['students_loan'] > 0){
		echo ' <a class="button" href="?mod=loan&sub=aup_template&id='.$_id.'">Generate AUP</a>&nbsp;&nbsp;
		<a id="signed_aup" class="button">Upload signed AUP</a>&nbsp;&nbsp;';
	}	
	if ($issue['quick_issue']==1)
		echo '<a class="button" href="./?mod=loan&sub=quick_loan_return&id='.$_id.'">Return</a> &nbsp; ';
	else
		echo '<a class="button" href="./?mod=loan&sub=loan&act=return&id='.$_id.'">Return</a> &nbsp; ';
}
?>
    <a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a> &nbsp; 
  </td>
</tr>
</table>
<script type="text/javascript">
$('#aup_upl').hide();
$('#signed_aup').click(function(){
	$('#aup_upl').toggle();
});
function print_preview()
{
  var href='./?mod=loan&sub=loan&act=print_issue&id=<?php echo $_id?>'; 
  var w = window.open(href, 'print_issue');  
}
</script>

