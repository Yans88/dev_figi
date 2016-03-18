<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_serialno = isset($_POST['serial_no']) ? $_POST['serial_no'] : null;
$_msg = null;
$today = date('j-M-Y H:i');

$request = get_condemned_issue($_id);
$need_approval = REQUIRE_CONDEMNED_APPROVAL;

if (isset($_POST['condemn']) && ($_POST['condemn'] == 1)){    
    $items = get_item_serial_by_condemned($_id);
    if (count($items) > 0) { // selected item found
        // create condemned issue
        $condemned_by = USERID;
        $condemned_date = date('Y-m-d H:i:s');
        $condemned_remark = mysql_real_escape_string($_POST['remark']);
        $query = "UPDATE condemned_issue SET condemned_by = '$condemned_by', condemn_datetime = '$condemned_date', 
                    condemn_remark = '$condemned_remark', issue_status = 'CONDEMNED' 
                  WHERE id_issue = '$_id' ";
        mysql_query($query);
            
        if (mysql_affected_rows()>0){
            $no = 1;
            // store disposal info
            $disposal_date = date('Y-m-d', strtotime($_POST['disposal_date']));
            $disposal_cost = mysql_real_escape_string($_POST['disposal_cost']);
            $disposal_method = mysql_real_escape_string($_POST['disposal_method']);
            $disposal_reference = mysql_real_escape_string($_POST['disposal_reference']);
            $vendor_name = mysql_real_escape_string($_POST['vendor_name']);
            $vendor_address = mysql_real_escape_string($_POST['vendor_address']);
            $contact_person = mysql_real_escape_string($_POST['contact_person']);
            $contact_number = mysql_real_escape_string($_POST['contact_number']);
            $query = "REPLACE INTO disposal_info(id_issue, disposal_method, disposal_date, disposal_cost,
                        disposal_reference, vendor_name, vendor_address, contact_person, contact_number)
                      VALUE ($_id, '$disposal_method', '$disposal_date', '$disposal_cost', '$disposal_reference',
                      '$vendor_name', '$vendor_address', '$contact_person', '$contact_number')";
            mysql_query($query);
            //echo $query.mysql_error();
            
            // store signature
            $query = "UPDATE condemned_signature SET condemn_signature = '$_POST[signature]' WHERE id_issue = '$_id' ";
            mysql_query($query);
            
            // save attachment if any
            if (!empty($_POST['deleted_attachments'])){
                $deleted_attachments = mysql_real_escape_string($_POST['deleted_attachments']);
                $query = 'DELETE FROM disposal_file WHERE id_file IN (' . $deleted_attachments . ')';
                mysql_query($query);
            }

            if (isset($_FILES['fattachment']) && count($_FILES['fattachment']) > 0){
                for ($i = 0; $i < count($_FILES['fattachment']['name']); $i++){
                    $filesize = $_FILES['fattachment']['size'][$i];
                    $filename = $_FILES['fattachment']['name'][$i];
                    $filetemp = $_FILES['fattachment']['tmp_name'][$i];
                    $errorcode = $_FILES['fattachment']['error'][$i];

                    if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){
                        $data = base64_encode(file_get_contents($filetemp));
                        $query  = "INSERT INTO disposal_file(id_issue, filename, data, doctype) ";
                        $query .= "VALUE ('$_id', '$filename', '$data', 0)";
                        mysql_query($query);
                        //echo mysql_error();
                    }
                }
            }
            
            
            goto_view($_id, 'CONDEMNED');
        }
    } else $_msg = 'There is no item selected !';
}

$condemned_by = FULLNAME;
$condemned_date = $today;

$item_list = get_item_by_condemned_in_table($_id);;
$users = get_user_list();


?>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>

<form method="post"  enctype="multipart/form-data">
<input type="hidden" name="items" id="items" value="">
<input type="hidden" name="iditems" id="iditems" value="">
<input type="hidden" name="condemn">
<input type="hidden" name="signature" value="">
<input type="hidden" name="deleted_attachments" id="deleted_attachments" value=''>

<h4 style="color: #fff">Condemnation
    <?php echo '<br/> Transaction No. ' . $transaction_prefix.$request['id_issue']; ?>
</h4>
<?php
$request['item_list'] = $item_list;
display_condemn_issue($request);

if ($need_approval){ // request created as approval type  
    if (preg_match('/RECOMMENDED|APPROVED|CONDEMNED|DISPOSED/', $request['issue_status'])) {
        display_condemn_recommendation($request);
        if (CONDEMNATION_FLOW_TYPE==2){
            $attachment_list = build_condemn_attachment_list($_id);
?>
  <table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left"><th align="left">Offline Signatured Documents</th></tr>
    <tr valign="top"><td align="left"><?php echo $attachment_list?></td></tr>    
</table>
<?php
        } // flow type =2
        if (($request['issue_status']=='RECOMMENDED2') ){
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
?>
<table cellpadding=3 cellspacing=1 class="condemnview approved" >
<tr align="left">
  <th align="left" colspan=3>Condemnation</th>
</tr>
<tr align="left" class="alt">
  <td align="left" width=130>Disposal</td>
  <td align="left" colspan=2>
    <table class="disposal">
    <tr>
        <td>Method</td>
        <td>: <?php echo build_combo('disposal_method', $disposal_methods)?></td>
        <td>Date</td>
        <td>: <input name="disposal_date" id="disposal_date" size=12 value="<?php echo date('d-M-Y')?>">
            <a id="btn_disposal_date" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
            $('#btn_disposal_date').click(
              function(e) {
                $('#disposal_date').AnyTime_noPicker().AnyTime_picker({format: "%d-%b-%Y"}).focus();
                e.preventDefault();
              } );
            </script>        
        </td>
    </tr>
    <tr>
        <td>Reference no.</td>
        <td>: <input name="disposal_reference" name="disposal_reference" ></td>
        <td>Cost</td>
        <td>: <input name="disposal_cost" name="disposal_cost" size=12></td>
    </tr>
    </table>
    </td>
</tr>

<tr align="left">
  <td align="left">Name of Vendor</td>
  <td align="left" colspan=2><input name="vendor_name" name="vendor_name" size=30></td>
</tr>
<tr align="left" valign="top" class="alt">
  <td align="left">Address of Vendor</td>
  <td align="left" colspan=2><textarea cols=40 rows=2 name="vendor_address" name="vendor_address" ></textarea></td>
</tr>
<tr align="left">
  <td align="left">Contact Person</td>
  <td align="left" colspan=2>
  Name: <input name="contact_person" name="contact_person" > 
  Number: <input name="contact_number" name="contact_number" >
  </td>
</tr>
<tr align="left" class="alt">
  <td align="left" width=130>Attachments</td>
  <td align="left" colspan=2>
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
<tr align="left">
  <td align="left">Condemned by</td>
  <td align="left" colspan=2><?php echo $condemned_by?></td>
</tr>
<tr valign="top" class="alt">  
  <td align="left">Date/Time of Condemnation</td>
  <td align="left"><?php echo $condemned_date?></td>
  <td width=200 rowspan=2>Signature<br/>
            <div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
  </td>
</tr>
<tr valign="top">  
  <td align="left">Remarks</td>
  <td align="left"><textarea name="remark" rows=2 cols=55></textarea></td>    
</tr>
<tr>
    <td colspan=3 valign="middle" align="right">
        <a class="button" id="condemn_btn" title="Submit Condemnation" href="javascript:void(0)">Submit Condemnation</a>
    </td>
</tr>
</table>
</form>
  <br/>
<br>&nbsp;<br>
<br/><br/>
<script type="text/javascript" src="./js/signature.js"></script>
<script type="text/javascript"  >

var department = '<?php echo $dept ?>';

$('#condemn_btn').click(function (e){
    var frm = document.forms[0]
    
    var ok = confirm('Are you sure condemned these items?');
    if (!ok)
        return false;
        
    var cvs = document.getElementById('imageView');
    document.forms[0].signature.value = cvs.toDataURL("image/png");
    
    frm.condemn.value = 1;
    frm.submit();
    return false;
});
</script>

<style>
table.disposal tr { background-color: transparent; }
table.disposal td { background-color: transparent; }
</style>
