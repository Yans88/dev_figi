<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$repair_type = isset($_POST['repair_type']) ? $_POST['repair_type'] : null;
$_msg = null;
$today = date('j-M-Y');
$admin_name = FULLNAME;

$need_approval = defined('MAINTENANCE_REQUIRE_SIGNATURE') ? MAINTENANCE_REQUIRE_SIGNATURE : false;
if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
   
        $id_machine = $_id;
        $this_time = date(' H:i:s');
        $period_from = convert_date($_POST['period_from'], 'Y-m-d').$this_time;
        $period_to = convert_date($_POST['period_to'], 'Y-m-d').$this_time;
        // update machine info
        $operation_type = implode(',', $_POST['how_operated']);
        // save history
        $query = "INSERT INTO machine_history(id_machine, vendor_name, period_from, period_to, charge, include_spareparts, reference_no, remark, repair_type, fault_reference ) 
                  VALUES ($_id, '$_POST[service_agent]', '$period_from', '$period_to', '$_POST[charge]', '$_POST[spareparts_included]',
                  '$_POST[reference_no]', '$_POST[remark]', '$_POST[repair_type]', '$_POST[fault_reference]')";
        mysql_query($query);
        //echo mysql_error().$query;
        if (mysql_affected_rows()>0){            
            $id_history = mysql_insert_id();
            
            $query = "SELECT SUM(charge) FROM machine_history WHERE id_machine = '$id_machine'";
            $rs = mysql_query($query);
            $rec = mysql_fetch_row($rs);
            $total_charge = $rec[0];
        
            $query = "UPDATE machine_info SET country_of_manufacture = '$_POST[country_of_manufacture]',
                        operation_type = '$operation_type', total_charge = '$total_charge' 
                        WHERE id_machine = $_id";
            mysql_query($query);
            //echo mysql_error().$query;
            
            // update item's status
            // get id_item
            $query = "SELECT id_item FROM machine_info WHERE id_machine = $_id";
            $rs = mysql_query($query);
            if ($rs && mysql_num_rows($rs)>0){
                $rec = mysql_fetch_row($rs);
                $id_item = $rec[0];
                $query = "UPDATE item SET status_update = now(), 
                          id_status = '".UNDER_SERVICE."' WHERE id_item = $id_item";
                mysql_query($query);         
                {
                /*
                // get cart id if any
                $query = "SELECT id_cart FROM mobile_cart_item WHERE id_item = '$id_item'";
                $rs = mysql_query($query);
                if ($rs && mysql_num_rows($rs)>0){
                    $rec = mysql_fetch_row($rs);
                    $id_cart = $rec[0];
                    if ($id_cart>0){
                        $query = "UPDATE mobile_cart SET cart_status = '".UNDER_SERVICE."' 
                                    WHERE id_cart ='$id_cart'";
                        mysql_query($query);
                        // update all member status of the cart
                        $query = "SELECT id_item FROM mobile_cart_item WHERE id_cart = '$id_cart' AND id_item != '$id_item'";
                        $rs = mysql_query($query);
                        $cart_items = array();
                        if ($rs && mysql_num_rows($rs)>0)
                            while ($rec = mysql_fetch_row($rs))
                                $cart_items[] = $rec[0];                        
                        if (count($cart_items)>0){
                            $query = "UPDATE item SET id_status = '".UNDER_SERVICE."', status_update = now()  
                                        WHERE id_item in (" . implode(',', $cart_items) . ")";
                            mysql_query($query);
                        }
                    }
                }
                */
                }
            }
            // store issuer info
            $issued_by = FULLNAME;
            $query = "INSERT INTO machine_issued_out(id_history, issued_by, issued_date, vendor_contact_name, vendor_contact_no)
                        VALUES($id_history, '$issued_by', now(), '$_POST[vendor_contact_name]', '$_POST[vendor_contact_no]')";
            mysql_query($query);
            // store signature
            $query = "INSERT INTO machine_issued_out_signature(id_history, vendor_contact_signature)
                        VALUES($id_history, '$_POST[vendor_contact_signature]')";
            mysql_query($query);
            
            ob_clean();
			if (!empty($_POST['fault_reference']) && isset($_SESSION['CURRENT_FAULT_NO']) &&
				($_POST['fault_reference'] ==$_SESSION['CURRENT_FAULT_NO']))
				unset($_SESSION['CURRENT_FAULT_NO']);
            header('Location: ./?mod=machrec&sub=machine&act=view&id=' . $_id);
            ob_end_flush();
            exit;      
           
        } 
        else 
            $_msg = 'There is no item selected !';
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

if ($_id > 0){
    $item_info = get_item_from_machine_id($_id);
    $item_info['opr_manual'] = preg_match('/MANUAL/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electric'] = preg_match('/ELECTRIC/i', $item_info['operation_type']) ? 'checked' : null;
    $item_info['opr_electronic'] = preg_match('/ELECTRONIC/i', $item_info['operation_type']) ? 'checked' : null;
}


$fault_no = isset($_SESSION['CURRENT_FAULT_NO']) ? $_SESSION['CURRENT_FAULT_NO'] : null;
?>

<h4>Machine Maintenance Issuance (Send-out)</h4>
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
        <td align="left"><?php echo $item_info['location_name']?>
        </td>    
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
        <td align="left"><?php echo $item_info['country_of_manufacture']?></td>    
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
        <td align="left"><?php echo $item_info['operation_type']?></td>    
      </tr>
      <tr valign="top">  
        <td align="left">Last Defect</td>
        <td align="left"><?php echo $item_info['defect_date']?>
        </td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Defect Description</td>
        <td align="left"><?php echo $item_info['defect_description']?>
        </td>    
      </tr>  
      </table>
      </td>
</tr>
<?php
    if (empty($repair_type)) {
?>
<tr valign="top">
    <th colspan=2 align="center"> Repair Handling </th>
</tr>
<tr valign="top">
    <td colspan=2 align="center"> 
        &nbsp;<br/>
        <button type="submit" name="repair_type" value="internal">Internal Repair</button>
        &nbsp;
        <button type="submit" name="repair_type" value="external">Send to Vendor</button>
        <br/>&nbsp;
    </td>
</tr>
<?php

    }  else {
    
    
?>

<tr valign="top">
    <th colspan=2 align="center"> Maintenance Info   </th>
</tr>
<tr valign="top">
    <td colspan=2>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="middle">  
        <td align="left" width=110>Service Vendor</td>
        <td align="left">
            <?php 
            if ($repair_type == 'internal') 
                echo 'Internal Repair<input type="hidden" name="service_agent" value="Internal Repair">'; 
            else {
        ?> 
            <input type="text" id="service_agent" name="service_agent" autocomplete="off" width=30 value="<?php echo $item_info['vendor_name']?>" 
            onKeyUp="suggestServiceAgent(this, this.value);"  onBlur="fillServiceAgent('service_agent', this.value);" >
            <div class="suggestionsBox" id="suggestionsServiceAgent" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsListServiceAgent"> &nbsp; </div>
            </div>        
            <?php
        } // vendor input
        ?>
        </td>
        <td rowspan=6 valign="bottom" >
        <table class="pic_table" cellpadding=3 cellspacing=1 width="100%" >
        <tr><th colspan=2 align="center" class="pic left top right">Contact Info</th></tr>
        <tr>
            <td class="pic left" width=80>Name:</td>
            <td class="pic right"><input name="vendor_contact_name" 
        <?php 
            if ($repair_type == 'internal') 
                echo 'value="' . $admin_name . '" readonly '; 
        ?>
        ></td>
        </tr>
        <tr class="alt">
        <td class="pic left ">Contact No.</td>
        <td class="pic right "><input name="vendor_contact_no"></td>
        </tr>
 <?php

if ($need_approval) {

?>
       <tr>
        <td class="pic left bottom">Signatures</td>
        <td class="pic right bottom">
            <div id="container" style="width:201px">
                    <canvas id="imageView" height=80 width=200></canvas>
                    <div style="text-align: right; position: absolute; top: 0; left: 182px;">
                        <a href="javascript:ResetSignature()" class="button clearsign" title="Clear signature space">X</a>
                    </div>
            </div>
        </td>
        </tr>
        <script type="text/javascript" src="./js/signature.js"></script>
<?php
    } // approval
?>    
        </table>
        </td>      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Period</td>
        <td align="left">
            <input type="text" name="period_from" id="period_from" size=14 value="<?php echo $today?>">
            <a id="button_period_from" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_period_from').click(
			  function(e) {
				$('#period_from').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
        </script>
        &nbsp; to &nbsp;
            <input type="text" name="period_to" id="period_to" size=14 value="<?php echo $today?>">
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
        <td align="left"><input type="text" name="charge" id="charge" size=6 value=0 >
	 &nbsp; Spare parts included? &nbsp; 
            <input type="radio" name="spareparts_included" id="spareparts_included_yes" value="1" >Yes &nbsp;
            <input type="radio" name="spareparts_included" id="spareparts_included_no" value="0" >No
        </td>
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Service Ref. No.</td>
        <td align="left"><input type="text" name="reference_no" id="reference_no" size=8 ></td>
      </tr>  
      <tr valign="middle" >  
        <td align="left">Remarks on vendor's service</td>
        <td align="left">
        <?php
        if ($repair_type == 'internal') 
            echo 'Internal Repair Taken <input type="hidden" name="remark" value="Internal Repair Taken">'; 
        else
            echo '<textarea name="remark" id="remark" rows=5 cols=36></textarea>'; 
    ?> 
            
        </td>
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Fault Ref. No.</td>
        <td align="left"><?php echo TRX_PREFIX_FAULT;?>
            <input type="text" name="fault_reference" id="fault_reference" size=10 value="<?php echo $fault_no?>" autocomplete="off" 
            onKeyUp="suggestFaultRef(this, this.value);"  onBlur="fillFaultRef('service_agent', this.value);" >
            <div class="suggestionsBox" id="suggestionsFaultRef" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsListFaultRef"> &nbsp; </div>
            </div>        
            
        </td>
      </tr>  

    </table>
    </td>
</tr>

<tr>
    <td colspan=2 valign="middle" align="right"><input type="image" onclick="return submit_issue()" src="images/submit.png" >
    </td>
</tr>

<Input type="hidden" name="repair_type" value="<?php echo $repair_type ?>" >

<?php
    } // repair_type is set
?>
</table>
<Input type="hidden" name="issue">
<Input type="hidden" name="issue_signature">
<Input type="hidden" name="vendor_contact_signature">
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
		$.post("machrec/suggest_service_agent.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""}, function(data){
			if(data.length >0) {
				$('#suggestionsServiceAgent').fadeIn();
				$('#suggestionsListServiceAgent').html(data);
			}
		});
	}
}

function fillFaultRef(id, thisValue, onclick) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsFaultRef').fadeOut();", 100);
}

function suggestFaultRef(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestionsFaultRef').fadeOut();
	} else {
		$.post("machrec/suggest_fault_reference.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""}, function(data){
			if(data.length >0) {
				$('#suggestionsFaultRef').fadeIn();
				$('#suggestionsListFaultRef').html(data);
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
    if ((need_approval == 1) && (isCanvasEmpty)){
        alert('Please sign-in for issuer and agent pic!');
        return false;
    }
    var ok = confirm('Are you sure proceed this Maintenance Issued-Out?');
    if (!ok)
        return false;
    if  (need_approval == 1) {
        var cvs = document.getElementById('imageView');
        frm.vendor_contact_signature.value = cvs.toDataURL("image/png");
    }
    frm.issue.value = 1;
    frm.submit();
    return false;
}

$('#edit_item').focus();

</script>
