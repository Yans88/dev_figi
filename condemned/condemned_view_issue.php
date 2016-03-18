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
$no = 1;
foreach ($request_items as $item)
  $items[] = ($no++) . ". <a href='./?mod=item&act=view&id=$item[id_item]'>$item[asset_no] ($item[serial_no])</a>";
$item_list = implode("<br/>\r\n", $items);

$users = get_user_list();  
$process = get_request_process($_id);
//print_r($process);
$issue = get_request_out($_id);
$signs = get_signatures($_id);

?>
<h4>Request already Loaned Out (View)</h4>
<table  class="loanview issue" cellpadding=2 cellspacing=1>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=3 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" width=130 >No</td>
        <th align="left"><?php echo $transaction_prefix.$request['id_loan']?></th>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date/Time of Request</td>
        <td align="left"><?php echo $request['request_date']?></td>
      </tr>
      <tr valign="top">  
        <td align="left">Requestor</td>
        <td align="left"><?php echo $request['requester']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Loan/Service Dates</td>
        <td align="left"><?php echo $request['start_loan']?> - <?php echo $request['end_loan']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Category</td>
        <td align="left"><?php echo $request['category_name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Quantity</td>
        <td align="left"><?php echo $request['quantity']?></td>    
      </tr>  
      <tr valign="top">  
        <td align="left">Requestor Remarks</td>
        <td align="left"><?php echo $request['remark']?></td>    
      </tr>
    </table>
    </td>
    <td >
    <table width="100%" cellpadding=3 cellspacing=1 class="issue" >
      <tr valign="top" align="left">
        <th align="left" width=130>Item Serial No.</td>
        <th align="left"><div id="seriallist"><?php echo $item_list?></div></th>
      </tr>  
      <tr valign="top">  
        <td align="left">Category</td>
        <td align="left"><?php echo $request['category_name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Basic Accessories Included</td>
        <td align="left"><?php echo $issue['basic_accessories']?></td>
      <tr valign="top">  
        <td align="left">Loan Out to</td>
        <td align="left"><?php echo $issue['name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC</td>
        <td align="left"><?php echo $issue['nric']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><?php echo $issue['contact_no']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Location</td>
        <td align="left"><?php echo $issue['location']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Department</td>
        <td align="left"><?php echo $issue['department_name']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Projected Date <br/>(Sign Out)</td>
        <td align="left"><?php echo $issue['loan_date']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Projected Date <br/>(To be Returned)</td>
        <td align="left"><?php echo $issue['return_date']?></td>    
      </tr>  
    </table>
    </td>
</tr>
<tr>
  <td colspan=2>
    <table width="100%" cellpadding=3 cellspacing=1>
<?php

if ($need_approval){

?>
    <tr valign="top">
        <th>&nbsp;</th>
        <th width=200 align="center">Approved By</th>
        <th width=200 align="center">Issued By</th>
        <th width=200 align="center">Loaned By</th>
    </tr>
    <tr valign="top">
        <td>Name</td>
        <td><?php echo $users[$process['approved_by']]?></td>
        <td><?php echo $users[$process['issued_by']]?></td>
        <td><?php echo $issue['name']?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>Date/Time Signature</td>
        <td><?php echo $process['approval_date']?></td>
        <td><?php echo $process['issue_date']?></td>
        <td><?php echo $process['loan_date']?></td>
    </tr>
    <tr valign="top">
        <td>Remarks</td>
        <td><?php echo $process['approval_remark']?></td>
        <td><?php echo $process['issue_remark']?></td>
        <td><?php echo $process['loan_remark']?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>Signatures</td>
        <td><img class='signature' src="<?php echo get_signature($_id, 'approve')?>"></td>
        <td><img class='signature' src="<?php echo get_signature($_id, 'issue')?>"></td>
        <td><img class='signature' src="<?php echo get_signature($_id, 'loan')?>"></td>
    </tr>
<?php

} 
else {

?>
    <tr valign="top">
        <th>&nbsp;</th>
        <th width=200>&nbsp;</th>
        <th width=200>Issued By</th>
        <th width=200>Loaned By</th>
    </tr>
    <tr valign="top">
        <td>&nbsp;</td>
        <td>Name</td>
        <td><?php echo @$users[$process['issued_by']]?></td>
        <td><?php echo $issue['name']?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>&nbsp;</td>
        <td>Date/Time Signature</td>
        <td><?php echo $process['issue_date']?></td>
        <td><?php echo $process['loan_date']?></td>
    </tr>
    <tr valign="top">
        <td>&nbsp;</td>
        <td>Remarks</td>
        <td><?php echo $process['issue_remark']?></td>
        <td><?php echo $process['loan_remark']?></td>
    </tr>
    <tr valign="top" class="alt">
        <td>&nbsp;</td>
        <td>Signatures</td>
        <td><img src="<?php echo get_signature($_id, 'issue')?>" class="signature"></td>
        <td><img src="<?php echo get_signature($_id, 'loan')?>" class="signature"></td>
    </tr>
<?php
}
?>
    </table>
  </td>
<tr>
<tr valign="middle">
  <td colspan=2 align="right" >            
        <button onclick="print_preview()" type="button"><img src="images/print.png"> Print Preview</button> 
<?php
if ( (USERGROUP == GRPADM) && !SUPERADMIN) {
    echo '<button type="button" onclick="location.href=\'./?mod=loan&sub=loan&act=return&id='.$_id.'\'"><img src="images/undo.png" >Return Loan</button>';
}
?>
  </td>
</tr>
</table>
<script>
function print_preview()
{
  var href='./?mod=loan&sub=loan&act=print_issue&id=<?php echo $_id?>'; 
  var w = window.open(href, 'print_issue');  
}
</script>