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

$no = 1;

$users = get_user_list();  
$process = get_request_process($_id);
// print_r($process);
$issue = get_request_out($_id);
$signs = get_expendable_signatures($_id);
?>
<h4>Items on Loan</h4>
<table  class="loanview issue" cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request);?></td></tr>
<tr valign="top"><td><?php  display_issuance($issue);?></td></tr>
<tr>
  <td>
<?php
    $issue = array_merge($issue, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];


    display_issuance_process($issue, $signs); 

?>
  </td>
<tr>
<tr valign="middle">
  <td colspan=2 align="right" >            
    <!-- <a class="button" href='./?mod=loan&sub=loan&act=lost&id=<?php echo $_id?>'>Item is Lost</a> &nbsp; -->
<?php

// if ( (USERGROUP == GRPADM) && !SUPERADMIN && (USERDEPT==$request['id_department'])) {
    echo '<a class="button" href="./?mod=expendable&sub=loan&act=return&id='.$_id.'">Return</a> &nbsp; ';
// }
?>
    <a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a> &nbsp; 
  </td>
</tr>
</table>
&nbsp;
<br/>
<script type="text/javascript">
function print_preview()
{
  var href='./?mod=expendable&sub=loan&act=print_issue&id=<?php echo $_id?>'; 
  var w = window.open(href, 'print_issue');  
}
</script>
