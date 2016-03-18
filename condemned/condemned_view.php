<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;


if (!empty($_POST)){
    $_id = isset($_POST['id']) ? $_POST['id'] : 0;
    if (isset($_FILES['fattachment']) && count($_FILES['fattachment']) > 0){
        for ($i = 0; $i < count($_FILES['fattachment']['name']); $i++){
            $filesize = $_FILES['fattachment']['size'][$i];
            $filename = $_FILES['fattachment']['name'][$i];
            $filetemp = $_FILES['fattachment']['tmp_name'][$i];
            $errorcode = $_FILES['fattachment']['error'][$i];
            
            if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                $data_raw = base64_encode(file_get_contents($filetemp));
                $query  = "INSERT INTO condemned_attachment(id_issue, filename, data) ";
                $query .= "VALUES('$_id', '$filename', '$data_raw')";
                mysql_query($query);
            }
        }
    }
    echo '<script>location.href="./?mod=condemned&act=view&id='.$_id.'";</script>';
    return;
}

$request = get_condemned_issue($_id);
if (empty($request)){
    echo '<script type="text/javascript">';
    echo 'alert("Data with id:# ' . $_id . ' is not found!");';
    echo 'location.href="./?mod=condemned";';
    echo '</script>';
    return;
}

$need_approval = REQUIRE_CONDEMNED_APPROVAL;

$item_list = get_item_by_condemned_in_table($_id);;

if ($request['issue_status'] == 'APPROVED') 
    $caption = 'Request Approved (In-Process)';
elseif ($request['issue_status'] == 'REJECTED') 
    $caption = 'Request Rejected (View)';
elseif ($request['issue_status'] == 'RECOMMENDED') 
    $caption = 'Recommended Condemned Items';
elseif ($request['issue_status'] == 'CONDEMNED') 
    $caption = 'Condemned Items';
elseif ($request['issue_status'] == 'DISPOSED') 
    $caption = 'Disposed Items';
else {
    if ($need_approval)
        $caption = 'Request Pending Approval (View)';
    else
        $caption = 'Pending Request (View)';
}

?>

<h4 style="color: #fff">
    <?php echo $caption . '<br/> Transaction No. ' . $transaction_prefix.$request['id_issue']; ?>
</h4>
<?php
$request['item_list'] = $item_list;
display_condemn_issue($request);

//$users = get_user_list();
if ($need_approval){ // request created as approval type
  
    if (preg_match('/RECOMMENDED|APPROVED|CONDEMNED|DISPOSED/', $request['issue_status'])) {
        display_condemn_recommendation($request);
        
        if (CONDEMNATION_FLOW_TYPE==2){
            $attachment_list = build_condemn_attachment_list($_id);       
            $notify_btn = null;
            if (($attachment_list != null) && (RECOMMENDED==$request['issue_status']))
                $notify_btn = '<a id="notify_btn" class="button">Notify HoD</a>';
?>
  <table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left"><th align="left">Offline Signatured Documents</th></tr>
    <tr valign="top"><td align="left"><?php echo $attachment_list?></td></tr>    
<?php
            if (USERGROUP==GRPADM){
                echo '<tr valign="top"><td align="left"><a id="attach_btn" href="#attachment_dialog" class="button">Add Attachment</a> &nbsp; '.$notify_btn.'</td></tr>';
            } // Admin
?>
</table>
<?php
        } // flow type =2
    
        if ((preg_match('/RECOMMENDED2|APPROVED|CONDEMNED|DISPOSED/', $request['issue_status'])) && ((defined('ENABLE_SECOND_RECOMMENDATION') && ENABLE_SECOND_RECOMMENDATION))){
            display_condemn_recommendation2($request);
        }
    } // recommended
    if (preg_match('/APPROVED|CONDEMNED|DISPOSED/', $request['issue_status'])) {
        if (CONDEMNATION_FLOW_TYPE==2)
            display_condemn_verification($request);
        else
            display_condemn_approval($request);
    } // approved
} // approval type 
if ($request['issue_status'] == REJECTED) {
    display_condemn_rejection($request);
} //rejected

if (($request['issue_status'] == 'CONDEMNED') || ($request['issue_status'] == 'DISPOSED')){
    $disposal = get_disposal_info($request['id_issue']);
    if (empty($disposal)){
      $disposal['disposal_method'] = '1';
      $disposal['disposal_date'] = 'n/a';
      $disposal['disposal_cost'] = 'n/a';
      $disposal['disposal_reference'] = 'n/a';
      $disposal['vendor_name'] = 'n/a';
      $disposal['vendor_address'] = 'n/a';
      $disposal['contact_number'] = 'n/a';
      $disposal['contact_person'] = 'n/a';
      
    }
    $attachments = get_disposal_file($request['id_issue']);
    $attachment_list = '-- attachment is not available! --';
    if (count($attachments) > 0){
      $attachment_list = '<script type="text/javascript" src="./js/slimbox2.js"></script>
                            <link rel="stylesheet" href="style/default/slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" />
                            <ul class="attachments" >';
      foreach ($attachments as $attachment){
          $href = './?mod=condemned&act=get_disposal_attachment&name=' .urlencode($attachment['filename']);
          $attachment_list .= '<li id="att'.$attachment['id_file'].'"><a href="'.$href.'" target="lightbox" >' . $attachment['filename'].'</a></li>';
      }
      $attachment_list .= '</ul>';
    } 
    $disposal['attachment_list']=$attachment_list;
    display_condemn_condemnation($request, $disposal);
} // condemned
?>
<br/>
<div class="condemnview footer">
<?php
    if (($request['issue_status'] == 'CONDEMNED') || ($request['issue_status'] == 'DISPOSED')){
        echo '<a  class="button"  id="certificate_btn">Generate Certificate</a> &nbsp; ';
        echo '<a  class="button"  id="item_list_btn">Generate Item List</a> &nbsp; ';
    }
?>
    <a  class="button" id="btnPrintPreview">Print Preview</a> &nbsp; 

<?php

if (!SUPERADMIN) { // non superadmin
    if (USERGROUP == GRPADM) { 
        if (!$need_approval || ($need_approval && (($request['issue_status'] == 'APPROVED') || ($request['issue_status'] == 'DISPOSED'))))	{ 
            if (!$need_approval) {
                if ($request['issue_status'] == 'PENDING' ){
?>
        <a  class="button" id="btnReject">Reject</a> &nbsp; 
        <a  class="button" id="btnCondemn">Condemn</a> &nbsp; 
<?php
                }
            } // !$need_approval
            else {
                if ($request['issue_status'] == 'APPROVED'){
                    echo '<a  class="button" id="btnCondemn">Condemn</a> &nbsp; ';
                }
                if ($request['issue_status'] == 'DISPOSED'){
                    echo '<a class="button" id="btnEditDiposal">Update Disposal Info</a> &nbsp; ';
                }
        }
      } else 
        if ((CONDEMNATION_FLOW_TYPE==2) && ($request['issue_status'] == 'RECOMMENDED')){
            echo '<a  class="button" id="certificate_btn">Generate Certificate</a> &nbsp; ';
            echo '<a  class="button" id="item_list_btn">Generate Item List</a> &nbsp; ';
        }
    } // user is admin
  else if (USERGROUP == GRPHOD) {
    if ($need_approval && ($request['issue_status'] == 'PENDING')) { 
?>
        <a  class="button" id="btnReject">Reject</a> &nbsp; 
        <a  class="button" id="btnRecommend">Recommend</a> &nbsp; 
  
<?php
    } // hod can only approve or reject
    else if ($need_approval && ($request['issue_status'] == 'RECOMMENDED')) { 
        echo '<a  class="button" id="btnVerify">Verify</a> &nbsp; ';
    }
  } // HoD
} // non-superadmin
?>

</div>
<br>&nbsp;<br>
<?php 
/* 
<style>
ul#certificate_option li { list-style: none;}
table.disposal tr { background-color: transparent; }
table.disposal td { background-color: transparent; }
table.disposal td.data { min-width: 100px}
div.data { min-width: 100px; display: inline-block}

</style>
<div id="certificate_dialog" style="width: 400px; display: none">
<strong>Generate Certificate Options</strong>
<ul id="certificate_option">
<!--li><input type="checkbox" name="sign" value="sign">Include Signatures</li-->
<!--li><input type="checkbox" name="ref" value="ref">Insert Reference No.</li-->
<li><input type="checkbox" name="dl" value="dl">Download as PDF</li>
</ul>
    <button id="certificate_btn" type="button">Get Certificate</a> &nbsp; 
    <button id="item_list_btn" type="button">Get Item List</a> &nbsp;
</div>
*/
?>
<form method="get" id="certificate_form" target="condemn_certificate">
<input type="hidden" name="mod" value="condemned">
<input type="hidden" name="sub" value="condemned">
<input type="hidden" name="act" value="certificate">
<input type="hidden" name="id" value="<?php echo $_id?>">
</form>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<div id="attachment_dialog" style="width: 400px; display: none">
<strong>Attach Documents</strong>
<form method="post" id="attachment_form" enctype="multipart/form-data">
<input type="hidden" name="mod" value="condemned">
<input type="hidden" name="sub" value="condemned">
<input type="hidden" name="act" value="attachment">
<input type="hidden" name="id" value="<?php echo $_id?>">

<br><input type="file" id="fattachment1" name="fattachment[]" class="multi max-5" ><!--  accept-gif|jpg|jpeg|png|pdf|xls|doc|ppt|xlsx|docx|pptx -->
<br>
<br><div id="fattachment-list"></div>
<script type="text/javascript" language="javascript">
    $(function(){ // wait for document to load 
     $('#fattachment').MultiFile({ 
      list: '#fattachment-list'
     }); 
    });
</script>        
<br><button id="attach_done" type="button">Done</a> &nbsp; 
<img id="loading" src="images/loading.gif" style="display: none">
</form>
</div>

<br/>

<script type="text/javascript" src="./js/jquery.fancybox.pack.js?v=2.0.6"></script>
<link rel="stylesheet" type="text/css" href="./style/default/jquery.fancybox.css?v=2.0.6" media="screen" />
<script type="text/javascript">

$('#btnPrintPreview').click(function(){
  window.open("./?mod=condemned&sub=condemned&act=print_issue&id=<?php echo $_id?>", 'print_preview');
});

$('#btnReject').click(function(){
  location.href='./?mod=condemned&sub=condemned&act=reject&id=<?php echo $_id?>';
});

$('#btnRecommend').click(function(){
  location.href='./?mod=condemned&sub=condemned&act=recommend&id=<?php echo $_id?>';
});

$('#btnApprove').click(function(){
  location.href='./?mod=condemned&sub=condemned&act=approve&id=<?php echo $_id?>';
});

$('#btnCondemn').click(function(){
  location.href='./?mod=condemned&sub=condemned&act=condemn&id=<?php echo $_id?>';
});

$('#btnEditDiposal').click(function(){
  location.href='./?mod=condemned&sub=condemned&act=disposal&id=<?php echo $_id?>';
});

$('#me').fancybox({'hideOnContentClick': true});

$('#certificate_btn').click(function  (e){
    var t = new Date();
    //$('#certificate_form [name="act"]').val('certificate');
    $('#certificate_form').attr('target', t.getTime());
    $('#certificate_form').submit();
});

$('#item_list_btn').click(function  (e){
    var t = new Date();
    
    $('#certificate_form [name="act"]').val('schedule');
    $('#certificate_form').attr('target', t.getTime());
    $('#certificate_form').submit();
});

$('#attach_btn').fancybox({'hideOnContentClick': true});

$('#attach_done').click(function  (e){    $('#attachment_form').submit(); });

$('#notify_btn').click(function  (e){    
    if (confirm('Are you confirm notify HoD if the document is ready?')){
        var url = './?mod=condemned&act=notify-hod&id=<?php echo $_id?>';
        $.get(url, function(data){
          if (data == 'OK') {           
            alert('Notification message has been sent to HoD');
          } else {
            alert('Failure on sending message to HoD');
            }
        });
    }
});

$('#btnVerify').click(function(){
  location.href='./?mod=condemned&act=verify&id=<?php echo $_id?>';
});


</script>
<br/>&nbsp;
