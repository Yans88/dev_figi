<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$request = get_condemned_issue($_id);
if (empty($request)){
	echo '<script type="text/javascript">';
	echo 'alert("Data with id:# ' . $_id . ' is not found!");';
	echo 'location.href="./?mod=condemned";';
	echo '</script>';
	return;
}

$need_approval = REQUIRE_CONDEMNED_APPROVAL;

$request_items = get_item_serial_by_condemned($_id);
$items = array();
$no = 1;
foreach ($request_items as $id => $item)
  $items[] = ($no++) . ". <a href='./?mod=item&act=view&id=$id'>$item</a>";
$item_list = implode("<br/>\r\n", $items);

if ($request['issue_status'] == APPROVED) 
	$caption = 'Request Approved (In-Process)';
elseif ($request['issue_status'] == REJECTED) 
	$caption = 'Request Rejected (View)';
else {
    if ($need_approval)
        $caption = 'Request Pending Approval (View)';
    else
        $caption = 'Pending Request (View)';
}

?>

<h4><?php echo $caption?></h4>
<?php
if ($request['issue_status'] != 'PENDING'){
?>
<table  class="condemned_table" cellpadding=2 cellspacing=1 id="condemnation">
<tr valign="top">
    <td width="50%">
<?php
}
?>
        <table cellpadding=3 cellspacing=1 class="condemnview" >
          <tr valign="top" align="left">
            <th align="left" width=130>Transaction No.</td>
            <th align="left"><?php echo $transaction_prefix.$request['id_issue']?></th>
          </tr>  
          <tr valign="top" class="alt">  
            <td align="left">Date/Time of Issuance</td>
            <td align="left"><?php echo $request['issue_datetime']?></td>
          </tr>  
          <tr valign="top">  
            <td align="left">Issued By</td>
            <td align="left"><?php echo $request['issued_by_name']?></td>
          </tr>  
          <tr valign="top" class="alt">  
            <td align="left">Remark</td>
            <td align="left"><?php echo $request['issue_remark']?></td>    
          </tr>
            <tr valign="top">  
              <td align="left">Signature</td>
              <td align="left"><img class='signature' src="<?php echo get_condemned_signature($_id, 'issue')?>"></td>
            </tr>
          <tr valign="top" align="left">
                <th align="left" colspan=2>Item Asset / Serial No. to be condemned</td>
          </tr>  
          <tr valign="top" align="left">
                <td align="left" colspan=2><div id="seriallist"><?php echo $item_list?></div></td>
          </tr>  
        </table>
<?php
if ($request['issue_status'] != 'PENDING'){
?>
    </td>
    <td width="50%">

<?php
}
$users = get_user_list();
if ($need_approval){ // request created as approval type
  
  if ($request['issue_status'] == APPROVED) {
?>
  <table cellpadding=3 cellspacing=1 class="condemnview approved" >
    <tr align="left">
      <th align="left" colspan=2>Approval</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Approval</td>
      <td  align="left"><?php echo $request['approval_datetime']?></td>
    </tr>
    <tr align="left">
      <td align="left" width=130>Approved by</td>
      <td align="left"><?php echo $users[$request['approved_by']]?></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Remark</td>
      <td  align="left"><?php echo $request['approval_remark']?></td>    
    </tr>
    <tr valign="top">  
      <td align="left">Signature</td>
      <td align="left"><img class='signature' src="<?php echo get_condemned_signature($_id, 'approval')?>"></td>
    </tr>
  </table>
  <br/>
<?php
  } // approved
} // approval type 
  if ($request['issue_status'] == REJECTED) {
    //$request = get_request_rejection($_id);
?>
  <table cellpadding=3 cellspacing=1 class="condemnview rejected" >
    <tr align="left">
      <th align="left" colspan=2>Rejection</th>
    </tr>
    <tr valign="top" class="alt" width=130>  
      <td align="left">Date/Time Rejection</td>
      <td  align="left"><?php echo $request['approval_datetime']?></td>
    </tr>
    <tr align="left">
      <td align="left">Rejected by</td>
      <td align="left"><?php echo $users[$request['approved_by']]?></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Remark</td>
      <td  align="left"><?php echo $request['approval_remark']?></td>    
    </tr>
    <tr valign="top">  
      <td align="left">Signature</td>
      <td align="left"><img class='signature' src="<?php echo get_condemned_signature($_id, 'approval')?>"></td>
    </tr>
  </table>
  <br/>
<?php
	} //rejected
?>
<?php
if ($request['issue_status'] != 'PENDING'){
?>
    </td>
    </tr>
</table>
<?php
}
?>
<div class="condemnview footer">
    <a  onclick="print_preview()"><img src="images/print.png"></a> &nbsp; 

<?php

if (!SUPERADMIN) { // non superadmin
    if (USERGROUP == GRPADM) { 
        if (!$need_approval || ($need_approval && ($request['issue_status'] == APPROVED)))	{ 
            if (!$need_approval) {
                if ($request['issue_status'] == PENDING ){
?>
        <input type="image" value="Reject" src="images/reject.png" 
        onclick="location.href='./?mod=condemned&sub=condemned&act=reject&id=<?php echo $_id?>'">
        <input type="image" value="Condemn" src="images/condemn.png" 
        onclick="location.href='./?mod=condemned&sub=condemned&act=condemn&id=<?php echo $_id?>'">
<?php
                } 
                else if ($request['issue_status'] == REJECTED) {
        /*
?>        
        <input type="image" value="Approve" src="images/approve.png" 
        onclick="location.href='./?mod=condemned&sub=condemned&act=approve&id=<?php echo $_id?>'">
<?php
    */
                }
            } // !$need_approval
            else {
                if ($request['issue_status'] == APPROVED){
                    echo <<<TEXTAPPROVED
        <input type="image" value="Condemn" src="images/condemn.png" 
        onclick="location.href='./?mod=condemned&sub=condemned&act=condemn&id=$_id'">
TEXTAPPROVED;
            }
        }
      }  // admin can make issue
    } // user is admin
  else if ((USERGROUP == GRPHOD) && ($need_approval && ($request['issue_status'] == PENDING))) { 
?>
        <input type="image" value="Reject" src="images/reject.png" 
        onclick="location.href='./?mod=condemned&sub=condemned&act=reject&id=<?php echo $_id?>'">
        <input type="image" value="Approve" src="images/approve.png" 
        onclick="location.href='./?mod=condemned&sub=condemned&act=approve&id=<?php echo $_id?>'">
  
<?php
  } // hod can only approve or reject
} // non-superadmin
?>

</div>
<br/>
<script type="text/javascript">
function approve(){
    location.href = "./?mod=condemned&sub=condemned&act=approve&id=<?php echo $_id?>" ;
    return false;
}
function  print_preview(){
  window.open("./?mod=condemned&sub=condemned&act=print_issue&id=<?php echo $_id?>", 'print_preview');
}

</script>
<br/>&nbsp;
