<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_delete  && !$i_can_update ) { // can approve
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$request = get_condemned_issue($_id);
$need_approval = REQUIRE_CONDEMNED_APPROVAL;

if (isset($_POST['approve']) && ($_POST['approve'] == 1)){

    $approved_by = USERID;
    $approval_date = date('Y-m-d H:i:s');
    $approval_remark = mysql_escape_string($_POST['remark']);
    $query = "UPDATE condemned_issue SET approved_by = '$approved_by', approval_datetime = '$approval_date', 
                approval_remark = '$approval_remark', issue_status = 'REJECTED' 
              WHERE id_issue = '$_id' ";
    mysql_query($query);
	//echo mysql_error().$query;
    if (mysql_affected_rows()>0){        
        // store signature
        $query = "UPDATE condemned_signature SET approval_signature = '$_POST[signature]'
                  WHERE id_issue = '$_id'";
        mysql_query($query);
        
        // update item status to available for loan
        $items = get_item_serial_by_condemned($_id);
        $no = 1;
        $values = array();
        foreach ($items as $id_item => $v){
            if (preg_match('/^[0-9]+$/', $id_item) > 0)
                $values[] = $id_item;
        }
            
        if (count($values)>0){
            $query = 'UPDATE item SET id_status = ' . AVAILABLE_FOR_LOAN . ', status_update = now(), status_defect = "'.$approval_remark.'" WHERE id_item IN (' . implode(', ', $values) . ')';
            mysql_query($query);
        }
        
        // sending notification
        send_rejected_condemned_notification($_id);
		
		// avoid refreshing the page
        goto_view($_id, 'REJECTED');
        /*
		ob_clean();
		header('Location: ./?mod=condemned&sub=condemned&act=view&id=' . $_id);
		ob_end_flush();
		exit;
              */
    }
}

$user_info = get_user(USERID);
$approved_by = $user_info['full_name'];
$approval_date = date('j-M-Y H:i');
 
$item_list = get_item_by_condemned_in_table($_id);
$issue_signature = get_condemned_signature($_id, 'issue');

if ($request['issue_status'] == REJECTED) 
	$caption = 'Rejected Issue Approval (Re-Approving)';
else
	$caption = 'Condemned Issue Approval (Rejection)';

?>
<div class="condemned approve">
<h4 style="color: #fff">
    <?php echo $caption . '<br/> Transaction No. ' . $transaction_prefix.$request['id_issue']; ?>
</h4>
<table cellpadding=3 cellspacing=1 class="condemnview" id="condemn_issue">
  <tr valign="top" align="left">
    <th align="left" width=130>Recommended By</td>
    <th align="left"><?php echo $request['issued_by_name']?></th>
    <th align="left" width=200>Signature</th>
  </tr>  
  <tr valign="top" class="alt">  
    <td align="left">Date/Time of Issuance</td>
    <td align="left"><?php echo $request['issue_datetime']?></td>
    <td rowspan=2><img class='signature' src="<?php echo get_condemned_signature($_id, 'issue')?>"></td>
  </tr>  
  <tr valign="top" class="normal">  
    <td align="left">Remark</td>
    <td align="left"><?php echo $request['issue_remark']?></td>    
  </tr>
  <tr valign="top" align="left">
        <th align="left" colspan=3>Item Asset / Serial No. to be condemned</td>
  </tr>  
  <tr valign="top" align="left">
        <td align="left" colspan=3>            
                <?php echo $item_list?>
                <br/>
        </td>
  </tr>  
</table>

<?php
	if (($request['issue_status'] == PENDING) || ($request['issue_status'] == REJECTED)) {
?>	
  <form method="post">
  <input type="hidden" name="approval_date" value="<?php echo $approval_date?>">
  <input type="hidden" name="approve" value="0">
  <input type="hidden" name="signature" value="">
  <table cellpadding=3 cellspacing=1 class="condemnview" >
    <tr align="left">
      <th align="left" width=130>Rejected by</th>
      <th align="left"><?php echo $approved_by?></th>
      <th align="left" width=200>Signature</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Rejection</td>
      <td align="left"><?php echo $approval_date?></td>
      <td rowspan=3>
        <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
      </td>
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td align="left"><textarea name="remark" cols=55 rows=3></textarea></td>    
    </tr>
  </table>
  <div class="condemnview footer">
    <a class="button" href="./?mod=condemned" id="a_cancel">Cancel</a> &nbsp;
    <a class="button" href="javascript:void(0)" id="a_approve">Submit</a>&nbsp;
  </div>
  </form>
  <br/>

  <script type="text/javascript" src="./js/signature.js"></script>
  <script type="text/javascript">
  
$('#a_approve').click(function(){
    if (document.forms[0].remark.value == ''){
        alert('Please fill in the remark!');
        return false;
    }
    if (isCanvasEmpty){
        alert('Please put signature to proceed!');
        return false;
    }
    var ok = confirm('Are you sure reject this item condemned request?');
    if (!ok)
        return false;

    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    document.forms[0].approve.value=1;
    document.forms[0].submit();
    return true;
  });
  </script>
<?php  
	}
?>
