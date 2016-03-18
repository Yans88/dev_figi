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
if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
    $history = machrec_get_history($_id);
    $id_machine = $history['id_machine'];
    $this_time = date(' H:i:s');
    $period_from = convert_date($_POST['period_from'], 'Y-m-d').$this_time;
    $period_to = convert_date($_POST['period_to'], 'Y-m-d').$this_time;
    // save history
    $query = "REPLACE INTO machine_history(id_history, id_machine, vendor_name, period_from, period_to, charge, include_spareparts, reference_no, remark) 
              VALUES ($_id, '$id_machine', '$_POST[service_vendor]', '$period_from', '$period_to', '$_POST[charge]', '$_POST[spareparts_included]',
              '$_POST[reference_no]', '$_POST[remark]')";
    mysql_query($query);
    //echo mysql_error().$query;
    // sum of each charge
    $query = "SELECT SUM(charge) FROM machine_history WHERE id_machine = '$id_machine'";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $total_charge = $rec[0];
    // update machine info
    $query = "UPDATE machine_info SET  total_charge = '$total_charge' WHERE id_machine = $id_machine";
    mysql_query($query);
    //echo mysql_error().$query;
    // update service vendor's contact
    $query = "UPDATE machine_issued_out SET vendor_contact_name = '$_POST[vendor_contact_name]', 
                vendor_contact_no = '$_POST[vendor_contact_no]'
                WHERE id_history = $_id";
    mysql_query($query);

    ob_clean();
    header('Location: ./?mod=machrec&sub=machine&act=view_issue&id=' . $_id);
    ob_end_flush();
    exit;      
       
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

if ($_id > 0){
    /*
    $item_info = get_item_from_machine_id($_id);
    $item_info['opr_manual'] = preg_match('/MANUAL/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electric'] = preg_match('/ELECTRIC/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electronic'] = preg_match('/ELECTRONIC/i', $item_info['operation_type']) ? 'checked' : null;
    */
    $history = machrec_get_history($_id);
    $history['spareparts_included'] = ($history['include_spareparts'] == 1) ? 'Spare parts included' : 'Spare parts not included';
    $item_info = get_item_from_machine_id($history['id_machine']);
    $item_info['opr_manual'] = preg_match('/MANUAL/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electric'] = preg_match('/ELECTRIC/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electronic'] = preg_match('/ELECTRONIC/i', $item_info['operation_type']) ? 'checked' : null;
}

?>

<h4>Machine Maintenance Issuance (Edit)</h4>
<form method="post">
<input type="hidden" name="items" id="items" value="">
<table  class="maintenance_table" cellpadding=2 cellspacing=1>
<tr valign="top">
    <th colspan=2 align="center"> Machine Info   </th>
</tr>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" class="normal">  
        <td align="left" width=100>Asset No</td>
        <td align="left"><?php echo $item_info['asset_no']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Serial No</td>
        <td align="left"><?php echo $item_info['serial_no']?></td>
      </tr>  
      <tr valign="top" class="normal">  
        <td align="left">Location</td>
        <td align="left"><?php echo $item_info['location']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Brand</td>
        <td align="left"><?php echo $item_info['brand_name']?></td>
      </tr>  
      <tr valign="top" class="normal">  
        <td align="left">Model No</td>
        <td align="left"><?php echo $item_info['model_no']?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Vendor</td>
        <td align="left"><?php echo $item_info['vendor_name']?></td>    
      </tr>  
      <tr valign="middle" class="normal">  
        <td align="left">Country of Manufacture</td>
        <td align="left"> <?php echo $item_info['country_of_manufacture']?>   </td>    
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
        <td align="left"><?php echo $item_info['operation_type']?>        </td>    
      </tr>
      <tr valign="top">  
        <td align="left">Defect Description</td>
        <td align="left"><?php echo $item_info['defect_description']?></td>    
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Current Total Service Charge</td>
        <td align="left">$<?php echo $item_info['total_charge']?></td>    
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
        <td align="left" width=120>Service Vendor</td>
        <td align="left">
            <input type="text" id="service_vendor" name="service_vendor" autocomplete="off" width=30 value="<?php echo $history['service_vendor_name']?>" 
            onKeyUp="suggestServiceAgent(this, this.value);"  onBlur="fillServiceAgent('service_vendor', this.value);" >
            <div class="suggestionsBox" id="suggestionsServiceAgent" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsListServiceAgent"> &nbsp; </div>
            </div>        
        </td>
        <td rowspan=5 valign="bottom" >
        <table class="pic_table" cellpadding=3 cellspacing=1 width="100%" >
        <tr><th colspan=2 align="center" class="pic left top right">Contact Info</th></tr>
        <tr>
            <td class="pic left" width=80>Name:</td>
            <td class="pic right"><input name="vendor_contact_name" value="<?php echo $history['vendor_contact_name']?>"></td>
        </tr>
        <tr class="alt">
        <td class="pic left ">Contact No.</td>
        <td class="pic right "><input name="vendor_contact_no" value="<?php echo $history['vendor_contact_no']?>"></td>
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
        </td>      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Period</td>
        <td align="left">
            <input type="text" name="period_from" id="period_from" size=14 value="<?php echo $history['period_from']?>">
            <a id="button_period_from" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_period_from').click(
			  function(e) {
				$('#period_from').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
        </script>
        &nbsp; to &nbsp;
            <input type="text" name="period_to" id="period_to" size=14 value="<?php echo $history['period_to']?>">
            <a id="button_period_to" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_period_to').click(
			  function(e) {
				$('#period_to').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
        </script>
        
        </td>

      </tr>  
      <tr valign="middle">  
        <td align="left">Service Charge ($)</td>
        <td align="left"><input type="text" name="charge" id="charge" size=6 value="<?php echo $history['charge']?>" >
	 &nbsp; Spare parts included? &nbsp; 
            <input type="radio" name="spareparts_included" id="spareparts_included_yes" value="1" <?php echo ($history['include_spareparts']==1)?'checked':''?> >Yes &nbsp;
            <input type="radio" name="spareparts_included" id="spareparts_included_no" value="0" <?php echo ($history['include_spareparts']==0)?'checked':''?>>No
        </td>
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Service Reference No.</td>
        <td align="left"><input type="text" name="reference_no" id="reference_no" size=8 value="<?php echo $history['reference_no']?>" ></td>
      </tr>  
      <tr valign="middle" >  
        <td align="left">Remarks on vendor's service</td>
        <td align="left">
            <textarea name="remark" id="remark" rows=5 cols=36><?php echo $history['remark']?></textarea>
        </td>
      </tr>  

    </table>
    </td>
</tr>

<tr>
    <td colspan=2 valign="middle" align="right"><a class="button" onclick="return submit_issue()" >Submit</a>
    </td>
</tr>
</table>
<Input type="hidden" name="issue">
<Input type="hidden" name="issue_signature">
<Input type="hidden" name="agent_pic_signature">
</form>
<br/><br/>

<script type="text/javascript"  >

var department = '<?php echo $dept ?>';
var need_approval = '<?php echo $need_approval ?>';

function fillCountry(id, thisValue, onclick) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsCountry').fadeOut();", 100);
}

function suggestCountry(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsCountry').fadeOut();
	} else {
		$.post("machrec/suggest_country.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestionsCountry').fadeIn();
				$('#suggestionsListCountry').html(data);
			}
		});
	}
}

function fill_loc(id, thisValue) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsLoc').fadeOut();", 100);
}

function suggest_loc(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsLoc').fadeOut();
	} else {
		$.post("item/suggest_location.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestionsLoc').fadeIn();
				$('#suggestionsListLoc').html(data);
			}
		});
	}
}

function fillServiceAgent(id, thisValue, onclick) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsServiceAgent').fadeOut();", 100);
}

function suggestServiceAgent(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsServiceAgent').fadeOut();
	} else {
		$.post("machrec/suggest_service_vendor.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""}, function(data){
			if(data.length >0) {
				$('#suggestionsServiceAgent').fadeIn();
				$('#suggestionsListServiceAgent').html(data);
			}
		});
	}
}

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
        /*
    if ((need_approval == 1) && (isCanvasEmpty)){
        alert('Please sign-in for issuer and agent pic!');
        return false;
    }
    if  (need_approval == 1) {
        var cvs = document.getElementById('imageView');
        frm.agent_pic_signature.value = cvs.toDataURL("image/png");
    }
    */
    var ok = confirm('Are you sure update maintenance info?');
    if (!ok)
        return false;
    frm.issue.value = 1;
    frm.submit();
    return false;
}

$('#edit_item').focus();

</script>