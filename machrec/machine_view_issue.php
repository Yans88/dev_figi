<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$today = date('j-M-Y');

$need_approval = defined('MAINTENANCE_OUR_REQUIRE_SIGNATURE') ? MAINTENANCE_OUR_REQUIRE_SIGNATURE : false;

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

if ($_id > 0){
    $history = machrec_get_history($_id);
    $history_out = machrec_get_history_out($_id);
    $history['spareparts_included'] = ($history['include_spareparts'] == 1) ? 'Spare parts included' : 'Spare parts not included';
    $item_info = get_item_from_machine_id($history['id_machine']);
    $item_info['opr_manual'] = preg_match('/MANUAL/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electric'] = preg_match('/ELECTRIC/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electronic'] = preg_match('/ELECTRONIC/i', $item_info['operation_type']) ? 'checked' : null;
}

?>

<h4>Machine Maintenance Issue (View)</h4>
<form method="post">
<input type="hidden" name="items" id="items" value="">
<table  class="maintenance_table" cellpadding=2 cellspacing=1>
<tr valign="top">
    <th colspan=2 align="center"> Machine Info   </th>
</tr>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top">  
        <td align="left" width=100>Asset No</td>
        <td align="left"><?php echo $item_info['asset_no']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Serial No</td>
        <td align="left"><?php echo $item_info['serial_no']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Brand</td>
        <td align="left"><?php echo $item_info['brand_name']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Model No</td>
        <td align="left"><?php echo $item_info['model_no']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Vendor</td>
        <td align="left"><?php echo $item_info['vendor_name']?></td>    
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Country of Manufacture</td>
        <td align="left"><?php echo $item_info['country_of_manufacture']?></div>        
        </td>    
      </tr>
    </table>
    </td>
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top">  
        <td align="left" width=110>Date of Purchase</td>
        <td align="left"><?php echo $item_info['date_of_purchase']?> </td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Purchase Price</td>
        <td align="left"><?php echo $item_info['cost']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Warranty Period</td>
        <td align="left"><?php echo $item_info['warranty_periode']?> </td>    
      </tr>      
      <tr valign="top" class="alt">  
        <td align="left">How Operated</td>
        <td align="left"><?php echo ucfirst(strtolower($item_info['operation_type']))?> </td>    
      </tr>
      <tr valign="top">  
        <td align="left">Location</td>
        <td align="left"><?php echo $item_info['location_name']?></td>    
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Current Total Service Charge</td>
        <td align="left"><?php echo $item_info['total_charge']?></td>    
      </tr>  
      </table>
      </td>
</tr>
<tr valign="top">
    <th colspan=2 align="center"> Maintenance Info   </th>
</tr>
<tr valign="top">
    <td colspan=2>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="middle">  
        <td align="left" width=175>Service Vendor</td>
        <td align="left"><?php echo $history['service_vendor_name']; if ($history['repair_type'] =='internal') echo '&nbsp; [Internal Repair]';?></td>
        </td>      
    </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Period</td>
        <td align="left"><?php echo $history['period_from']?>
        &nbsp; to &nbsp; <?php echo $history['period_to']?>
        </td>

      </tr>  
      <tr valign="middle">  
        <td align="left">Service Charge ($)</td>
        <td align="left"><?php echo $history['charge']?> &nbsp; (<?php echo $history['spareparts_included']?>)
        </td>
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Reference Service No.</td>
        <td align="left"><?php echo $history['reference_no']?></td>
      </tr>  
      <tr valign="top" >  
        <td align="left">Remarks on vendor's service</td>
        <td align="left"><?php echo $history['remark']?>        </td>
      </tr>  

    </table>
    </td>
</tr>
<tr valign="top"><th colspan=2 align="center"> Issued-Out Info  </th></tr>
<tr valign="top">
    <td width="50%">
        <table class="pic_table" cellpadding=1 cellspacing=1 width="100%" >
        <tr><th colspan=2>Issued By</th></tr>
        <tr>
            <td width=100>Name:</td>
            <td ><?php echo $history_out['issued_by']?></td>
        </tr>
        <tr class="alt">
            <td>Issued Date</td>
            <td><?php echo $history_out['issued_date']?></td>
        </tr>   
    </table>
    </td>
    <td width="50%">
        <table class="pic_table" cellpadding=1 cellspacing=1 width="100%" >
        <tr><th colspan=2>Received By</th></tr>
        <tr>
            <td width=100>Name:</td>
            <td ><?php echo $history_out['vendor_contact_name']?></td>
        </tr>
        <tr class="alt">
            <td>Contact No</td>
            <td><?php echo $history_out['vendor_contact_no']?></td>
        </tr>
 <?php

if ($need_approval) {

?>
       <tr>
        <td class="pic left bottom">Signatures</td>
        <td class="pic right bottom"><img class="signature" src="<?php echo machrec_get_issue_signature($_id)?>" /></td>
        </tr>
<?php
    } // approval
?>    
        </table>
    </td>
</tr>
<tr>
    <td colspan=2 valign="middle" align="right">
        <a href="?mod=machrec&sub=machine&act=return&id=<?php echo $history['id_history']?>" class="button">Repair Completed</a>
        <a href="?mod=machrec&sub=machine&act=view&id=<?php echo $item_info['id_machine']?>" class="button">Back to Maintenance List</a>
    </td>
</tr>
</table>
<Input type="hidden" name="issue">
<Input type="hidden" name="issue_signature">
<Input type="hidden" name="vendor_contact_signature">
</form>
<br/><br/>

<script type="text/javascript"  >

function submit_issue()
{
    var frm = document.forms[0]
    var items_val = $('#items').val();
    /*
    if (frm.name.value == ''){
        alert('Please fill in Loan Out to!');
        return false;
    }
    if (frm.nric.value == ''){
        alert('Please fill in NRIC!');
        return false;
    }
    */
    if ((need_approval == 1) && (isCanvasEmpty || isCanvas2Empty)){
        alert('Please sign-in for issuer and agent pic!');
        return false;
    }
    var ok = confirm('Are you sure proceed this Maintenance Issued-Out?');
    if (!ok)
        return false;
    if  (need_approval == 1) {
        var cvs = document.getElementById('imageView');
        frm.issue_signature.value = cvs.toDataURL("image/png");
        cvs = document.getElementById('imageView2');
        frm.loan_signature.value = cvs.toDataURL("image/png");    
    }
    frm.issue.value = 1;
    frm.submit();
    return false;
}

$('#edit_item').focus();

</script>
