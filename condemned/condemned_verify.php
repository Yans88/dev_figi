<?php
if (!defined('FIGIPASS')) exit;

if (USERGROUP!=GRPHOD) { // can verify
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$request = get_condemned_issue($_id);
$need_verification = REQUIRE_CONDEMNED_APPROVAL;

if (isset($_POST['verify']) && ($_POST['verify'] == 1)){

    // verification using approval space
    $verified_by = USERID;
    $verification_date = date('Y-m-d H:i:s');
    $verification_remark = mysql_escape_string($_POST['remark']);
    $query = "UPDATE condemned_issue SET approved_by = '$verified_by', approval_datetime = '$verification_date', 
                approval_remark = '$verification_remark', issue_status = 'APPROVED' 
              WHERE id_issue = '$_id' ";
    mysql_query($query);
	//echo mysql_error().$query;
    if (mysql_affected_rows()>0){        
        // update status of item to be condemned when verified
        $items = get_item_serial_by_condemned($_id);
        $values = array();
        foreach ($items as $id_item => $v){
            if (preg_match('/^[0-9]+$/', $id_item) > 0)
                $values[] = $id_item;
        }

        if (count($values)>0){
            $query = 'UPDATE item SET id_status = ' . CONDEMNED . ', status_update = now() WHERE id_item IN (' . implode(', ', $values) . ')';
            mysql_query($query);
            //echo mysql_error().$query;
        }

        // store signature
        $query = "UPDATE condemned_signature SET approval_signature = '$_POST[signature]' WHERE id_issue = '$_id' ";
        mysql_query($query);
        
        // sending notification
        //send_verified_condemned_notification($_id);
		
		// avoid refreshing the page
        goto_view($_id, 'APPROVED');
        /*
		ob_clean();
		header('Location: ./?mod=condemned&sub=condemned&act=view&id=' . $_id);
		ob_end_flush();
		exit;
              */
    }
}
$item_list = get_item_by_condemned_in_table($_id);;

$user_info = get_user(USERID);
$verified_by = $user_info['full_name'];
$verification_date = date('j-M-Y H:i');

$issue_signature = get_condemned_signature($_id, 'issue');

?>
<div class="condemned approve">
<h4 style="color: #fff">
    Condemn Request Verification
    <br/> Transaction No. <?php echo $transaction_prefix.$request['id_issue']; ?>
</h4>
<?php
$request['item_list'] = $item_list;
display_condemn_issue($request);

display_condemn_recommendation($request);
if (CONDEMNATION_FLOW_TYPE==2){
    $attachment_list = build_condemn_attachment_list($_id);
?>
<img src="images/space.gif" width=1 height=5 border=0>
<table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left"><th align="left">Offline Signatured Documents</th></tr>
    <tr valign="top"><td align="left"><?php echo $attachment_list?></td></tr>    
</table>
<?php
} // flow type =2

if (($request['issue_status']=='RECOMMENDED2') ){
    display_condemn_recommendation2($request);
}
?>
  
  <form method="post">
  <input type="hidden" name="verification_date" value="<?php echo $verification_date?>">
  <input type="hidden" name="verify" value="0">
  <input type="hidden" name="signature" value="">
  <table cellpadding=3 cellspacing=1 class="condemnview" >
    <tr align="left">
      <th align="left" colspan=2>Condemn Verification</th>
      <th align="left" width=200></th>
    </tr>
    <tr align="left">
      <td align="left" width=130>Verified by</td>
      <td align="left" colspan=2><?php echo $verified_by?></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Verification</td>
      <td align="left"><?php echo $verification_date?></td>
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
    <a class="button" href="javascript:void(0)" id="a_verify">Submit</a>&nbsp;
  </div>
  </form>
  <br/>
  <script type="text/javascript" src="./js/signature.js"></script>
  <script type="text/javascript">
$('#a_verify').click(function(){
    if (document.forms[0].remark.value == ''){
        alert('Please fill in the remark!');
        return false;
    }
    if (isCanvasEmpty){
        alert('Please put signature to proceed!');
        return false;
    }
    var ok = confirm('Are you sure verify this item condemned request?');
    if (!ok)
        return false;

    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    document.forms[0].verify.value=1;
    document.forms[0].submit();
    return true;
  });
  </script>
</div>
<br>&nbsp;<br>