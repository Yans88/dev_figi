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
if (empty($request)){
	echo '<script>alert("Loan with ID: #' . $_id . ' is not found!")</script>';
	echo '<script>location.href="./?mod=loan&status=returned";</script>';
	return;
}
$need_approval = ($request['without_approval'] == 0);
$request_items = get_request_items($_id);
$no = 1;
foreach ($request_items as $item)
  $items[] = ($no++) . ". $item[asset_no] ($item[serial_no])";
$item_list = implode("<br/>\r\n", $items);

$process = get_request_process($_id);
$issue = get_request_out($_id);
$signs = get_signatures($_id);
$returns = get_request_return($_id);  
$users = get_user_list();  



?>

<h4>Item Lost (View)</h4>
<form method="post">
<table  class="loanview return"  cellpadding=2 cellspacing=1>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" width=130 >No</td>
        <th align="left">
            <?php 
            echo $transaction_prefix.$request['id_loan'];
            if ($request['long_term'] == 1)
                echo ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';
        ?>
        </th>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date/Time of Request</td>
        <td align="left"><?php echo $request['request_date']?></td>
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
    <td>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
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
        <td align="left"><?php echo $issue['location_name']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Department</td>
        <td align="left"><?php echo $issue['department_name']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Date (Sign Out)</td>
        <td align="left"><?php echo $issue['loan_date']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Date (To be Returned)</td>
        <td align="left"><?php echo $issue['return_date']?></td>    
      </tr>  
    </table>
    
  </td>
</tr>
<tr>
  <td colspan=2>
    <table width="100%" cellpadding=2 cellspacing=1 class="process">
<?php
if ($need_approval){
?>
<tr valign="top">
    <th>&nbsp;</th>
    <th width=200>Approved By</th>
    <th width=200>Issued By</th>
    <th width=200>Loaned By</th>
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
    <td><img src="<?php echo $signs['approve_sign']?>" class='signature'></td>
    <td><img src="<?php echo $signs['issue_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['loan_sign']?>"  class="signature"></td>
</tr>
<tr valign="top">
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th width=200>Reported By</th>
    <th width=200>Received By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td></td>
    <td><?php echo $returns['returned_by']?></td>
    <td><?php echo $returns['received_by']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td></td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td></td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td></td>
    <td><img src="<?php echo $signs['return_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['receive_sign']?>" class="signature"></td>
</tr>

<?php
} else {
?>
<tr valign="top">
    <th rowspan=5>&nbsp;</th>
    <th width=200></th>
    <th width=200>Issued By</th>
    <th width=200>Loaned By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $users[$process['issued_by']]?></td>
    <td><?php echo $issue['name']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $process['issue_date']?></td>
    <td><?php echo $process['loan_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><?php echo $process['issue_remark']?></td>
    <td><?php echo $process['loan_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td><img src="<?php echo $signs['issue_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['loan_sign']?>" class="signature"></td>
</tr>
<tr valign="top">
    <th rowspan=5>&nbsp;</th>
    <th width=200></th>
    <th width=200>Reported By</th>
    <th width=200>Received By</th>
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $returns['returned_by']?></td>
    <td><?php echo $returns['received_by']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td><img src="<?php echo $signs['return_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['receive_sign']?>" class="signature"></td>
</tr>
<?php
}
?>
<tr valign="top" class="normal">
    <td></td>
    <td>Attachments</td>
	<td colspan=2>
          <div id="imagelist" class="content">
            <div id="thumbs" class="navigation">
<?php
    $attachments = array();
    if ($_id > 0) 
		$attachments = get_lost_attachments($_id);
    $active =  ' class="active" ';
    if (count($attachments) > 0){
      echo '<ol class="attachments" >';
      foreach ($attachments as $attachment){
          $href = './?mod=loan&act=get_lost_attachment&name=' .urlencode($attachment['filename']);
          echo '<li id="att'.$attachment['id_attachment'].'"><a target="attachment" href="'.$href.'" rel="lightbox" >';
          echo $attachment['filename'].'</a></li>';
          $active = null;
      }
      echo '</ol>';
    } else
        echo '--attachment is not available!--';
?>
            </div>
        </div>
        <div class="clear"></div>
      <br/>
<!--
        Add attachment, click button below: <input type="file" id="fattachment1" name="fattachment[]" class="multi max-5 accept-gif|jpg|jpeg|png|pdf|xls|doc|ppt|xlsx|docx|pptx" >
        <div id="fattachment-list"></div>
        <script type="text/javascript" language="javascript">
        $(function(){ // wait for document to load 
         $('#fattachment').MultiFile({ 
          list: '#fattachment-list'
         }); 
        });
-->
    </script>
	</td>
	</tr>
    </table>
    </td>
</tr>
<tr>    
  <td colspan=2 align="right">
<?php
/*
if ( (USERGROUP == GRPHOD) && $i_can_delete && $need_approval) {
    echo '<button type="button" onclick="location.href=\'./?mod=loan&sub=loan&act=acknowledge&id='.$_id.'\'"><img src="images/notes.png" > Acknowledge Returned Items</button>';
}
*/
?>
  </td>
</tr>
</table>
<br/>&nbsp;
