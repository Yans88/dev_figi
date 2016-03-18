<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_delete  && !$i_can_update ) { // can recommend
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$request = get_condemned_issue($_id);
$need_recommendation = REQUIRE_CONDEMNED_APPROVAL;

if (isset($_POST['recommend']) && ($_POST['recommend'] == 1)){

    $recommended_by = USERID;
    $recommendation_date = date('Y-m-d H:i:s');
    $recommendation_remark = mysql_real_escape_string($_POST['remark']);
    $query = "UPDATE condemned_issue SET recommended2_by = '$recommended_by', recommendation2_datetime = '$recommendation_date', 
                recommendation2_remark = '$recommendation_remark', issue_status = 'RECOMMENDED2' 
                WHERE id_issue = '$_id' ";
    mysql_query($query);
	//echo mysql_error().$query;
    if (mysql_affected_rows()>0){        
        // store signature
        $query = "UPDATE condemned_signature SET recommendation2_signature = '$_POST[signature]' WHERE id_issue = '$_id' ";
        mysql_query($query);
        //echo mysql_error().$query;
        
        // save exception if any
        if (!empty($_POST['exceptions'])){
            $values = array();
            foreach($_POST['exceptions'] as $id_item)
                $values[] = "($_id, $id_item)";
            if (count($values)>0){
                $query = "INSERT INTO condemned_item_exception VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo $query.mysql_error();
            }
        }
		
		// avoid refreshing the page
        goto_view($_id, 'RECOMMENDED2');
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
$recommended_by = $user_info['full_name'];
$recommendation_date = date('j-M-Y H:i');

$issue_signature = get_condemned_signature($_id, 'issue');

if ($request['issue_status'] == REJECTED) 
	$caption = 'Rejected Issue Approval (Re-Approving)';
else
	$caption = 'Issue Pending (Recommendation)';

?>
<div class="condemned recommend">
<h4 style="color: #fff">
    <?php echo $caption . '<br/> Transaction No. ' . $transaction_prefix.$request['id_issue']; ?>
</h4>
<table cellpadding=3 cellspacing=1 class="condemnview" id="condemn_issue">
  <tr valign="top" align="left">
    <th align="left" width=130>Prepared By</td>
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
        </td>
  </tr>  
</table>
  <table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left">
      <th align="left" width=130>Recommended by</th>
      <th align="left"><?php echo $request['recommended_by']?></th>
      <th align="left" width=200>Signature</th>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Recommendation</td>
      <td align="left"><?php echo $request['recommendation_datetime']?></td>
      <td rowspan=2><img class='signature' src="<?php echo get_condemned_signature($_id, 'recommendation')?>"></td>
    </tr>
    <tr valign="top">  
      <td align="left">Remark</td>
      <td align="left"><?php echo $request['recommendation_remark']?></td>    
    </tr>
  </table>

<?php
	if (($request['issue_status'] == RECOMMENDED) || ($request['issue_status'] == REJECTED)) {
    $exception_list = get_item_exception_by_condemned_in_table($_id, true);
?>	
  <form method="post">
  <input type="hidden" name="recommendation_date" value="<?php echo $recommendation_date?>">
  <input type="hidden" name="recommend" value="0">
  <input type="hidden" name="signature" value="">
  <table cellpadding=3 cellspacing=1 class="condemnview" >
    <tr align="left">
      <th align="left" colspan=2>Recommendation by Director</th>
      <th align="left" width=200></th>
    </tr>
    <tr align="left" class="alt">
      <td align="left" width=130>Exceptions</td>
      <td align="left" colspan=2><?php echo $exception_list?></td>
    </tr>
    <tr align="left">
      <td align="left" width=130>Recommended by</td>
      <td align="left" colspan=2><?php echo $recommended_by?></td>
    </tr>
    <tr valign="top" class="alt">  
      <td align="left">Date/Time of Recommendation</td>
      <td align="left"><?php echo $recommendation_date?></td>
      <td rowspan=3 valign="middle">Signature:<br/>
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
    <a class="button" href="javascript:void(0)" id="a_recommend">Submit</a>&nbsp;
  </div>
  </form>
  <br/>
  <script type="text/javascript" src="./js/signature.js"></script>
  <script type="text/javascript">
$('#a_recommend').click(function(){
    if (document.forms[0].remark.value == ''){
        alert('Please fill in the remark!');
        return false;
    }
    if (isCanvasEmpty){
        alert('Please put signature to proceed!');
        return false;
    }
    var ok = confirm('Are you sure recommend this item condemned request?');
    if (!ok)
        return false;

    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    document.forms[0].recommend.value=1;
    document.forms[0].submit();
    return true;
  });
  </script>
<?php  
	}
?>
</div>