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
    $item_info = get_item_from_machine_id($_id);
}

?>

<h4>Machine History Record</h4>
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
        <td align="left"><?php echo $item_info['location_name']?></td>    
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
        <td align="left"><?php echo ucfirst(strtolower($item_info['operation_type']))?></td>    
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
    <th colspan=2 align="center"> Maintenance History   </th>
</tr>
<tr valign="top">
    <td colspan=2 width=800 >
    <table width="100%" cellpadding=2 cellspacing=1 class="history_list" >
      <tr valign="middle">
        <th colspan=3>Service Vendor</th>
        <th colspan=4>Maintenance Details</th>
        <th rowspan=3>Remarks on vendor's service</th>
        <th rowspan=3 width=40>&nbsp;</th>
      </tr>
      <tr>
        <th colspan=2>Period</th>
        <th rowspan=2 width=130>Name</th>
        <th rowspan=2 width=50>Service Charge ($)</th>
        <th colspan=2>Spare parts included?</th>
        <th rowspan=2 width=60>Service Reference No.</th>
        </tr>
      <tr>
        <th width=75>From</th>
        <th width=75>To</th>
        <th width=35>Yes</th>
        <th width=35>No</th>
        </tr>
<?php
    $total_records = machrec_count_history($_id);
    if ($total_records > 0) {
        $cnt = 1;
        $rs = machrec_get_histories($_id);
        while ($rec = mysql_fetch_assoc($rs)) {
            if (!empty($rec['mh_vendor_name']))
                $rec['vendor_name'] = $rec['mh_vendor_name'];
            $rec['spareparts_included_yes'] = ($rec['include_spareparts'] == 1) ? 'v' : '';
            $rec['spareparts_included_no'] = ($rec['include_spareparts'] != 1) ? 'v' : '';
            $class_name = (($cnt++ % 2) == 0) ? 'class="alt"' : 'class="normal"';
            $edit_link = '';
            if ($i_can_update)
                $edit_link = '<a href="?mod=machrec&sub=machine&act=edit_issue&id='.$rec['id_history'].'" title="edit"><img class="icon" src="images/edit.png" alt="edit" ></a>';
            echo <<<ROWS
    <tr $class_name>
        <td align="center">$rec[period_from]</td>
        <td align="center">$rec[period_to]</td>
        <td>$rec[vendor_name]</td>
        <td align="center">$rec[charge]</td>
        <td align="center">$rec[spareparts_included_yes]</td>
        <td align="center">$rec[spareparts_included_no]</td>
        <td align="center">$rec[reference_no]</td>
        <td>$rec[remark]</td>
        <td>
            <a href="?mod=machrec&sub=machine&act=view_issue&id=$rec[id_history]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
            $edit_link
        </td>
    </tr>
ROWS;


        }
    } else
        echo "<tr><td colspan=9 align='center'>Machine doesn't has any record</td></tr>";
?>
        
    </table>
    </td>
</tr>
<?php
    if ($i_can_update) {
?>
<tr>
    <td colspan=2 valign="middle" align="right">
    <a class="button" href="?mod=machrec&sub=machine&act=print&id=<?php echo $item_info['id_machine']?>" target="printpreview">Print Preview</a>
    <a class="button" href="?mod=machrec&sub=machine&act=issue&id=<?php echo $item_info['id_machine']?>">Send for Repair</a>
    </td>
</tr>
<?php
    } // i can update
?>
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
		$.post("machrec/suggest_service_agent.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""}, function(data){
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
