<?php
print_r($_POST);

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
   
        $this_time = date(' H:i:s');
        $period_from = convert_date($_POST['period_from'], 'Y-m-d').$this_time;
        $period_to = convert_date($_POST['period_to'], 'Y-m-d').$this_time;
        
        $query = "INSERT INTO machine_history(id_machine, agent_name, period_from, period_to, cost_per_annum, include_spareparts, number_of_servicing, comment) 
                  VALUES ($_id, '$_POST[service_agent]', '$period_from', '$period_to', '$_POST[cost]', '$_POST[spareparts_included]',
                  '$_POST[number_of_servicing]', '$_POST[comment]')";
        mysql_query($query);
        echo mysql_error();
        if (mysql_affected_rows()>0){            
            $id_history = mysql_insert_id();
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
        echo mysql_error();
            
            }
            // store issuer info
	    $issued_by = USER_FULLNAME;
            $query = "INSERT INTO machine_issued_out(id_history, issued_by, issued_date, agent_pic_name, agent_pic_contact_no)
                        VALUES($id_history, '$issued_by', now(), '$_POST[agent_pic_name]', '$_POST[agent_pic_contact_no]')";
            mysql_query($query);
	} 
	else 
		$_msg = 'There is no item selected !';
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

/*
if ( ($need_approval && ($item_info['status'] != APPROVED)) || 
     (!$need_approval && ($item_info['status'] != PENDING)) ){
    goto_view_issue($_id);
}
*/
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
        <td align="left">
            <input type="text" id="country_of_manufacture" name="country_of_manufacture" autocomplete="off"  size=30 
            onKeyUp="suggestCountry(this, this.value);"  onBlur="fillCountry('country_of_manufacture', this.value);" >
            <div class="suggestionsBox" id="suggestionsCountry" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsListCountry"> &nbsp; </div>
            </div>        
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
        <td align="left">
            <input type="checkbox" name="how_operated" value="manual">Manual
            <input type="checkbox" name="how_operated" value="electric">Electric
            <input type="checkbox" name="how_operated" value="electronic">Electronic
        </td>    
      </tr>
      <tr valign="top">  
        <td align="left">Location</td>
        <td align="left">
            <input type="text" id="location" name="location" size=28 autocomplete="off" 
            onKeyUp="suggest_loc(this, this.value);" onBlur="fill_loc('location', this.value);">
			<div class="suggestionsBox" id="suggestionsLoc" style="display: none; z-index: 500;"> 
				<div class="suggestionList" id="suggestionsListLoc"> &nbsp; </div>
			</div>            
        </td>    
      </tr>  

      </table>
      </td>
</tr>
<tr valign="top">
    <th colspan=2 align="center"> Maintenance History   </th>
</tr>
<tr valign="top">
    <td colspan=2>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="middle">  
        <td align="left" width=120>Service Agent</td>
        <td align="left">
            <input type="text" id="service_agent" name="service_agent" autocomplete="off" width=30
            onKeyUp="suggestServiceAgent(this, this.value);"  onBlur="fillServiceAgent('service_agent', this.value);" >
            <div class="suggestionsBox" id="suggestionsServiceAgent" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsListServiceAgent"> &nbsp; </div>
            </div>        
        </td>
        <td rowspan=5 valign="bottom" >
        <table class="pic_table" cellpadding=3 cellspacing=1 width="100%" >
        <tr><th colspan=2 align="center" class="pic left top right">PIC Info</th></tr>
        <tr>
            <td class="pic left" width=80>Name:</td>
            <td class="pic right"><input name="agent_pic_name"></td>
        </tr>
        <tr class="alt">
        <td class="pic left ">Contact No.</td>
        <td class="pic right "><input name="agent_pic_contact_no"></td>
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
        <td align="left">Cost ($) (per annum)</td>
        <td align="left"><input type="text" name="cost" id="cost" size=8 >
	 &nbsp; Spare parts included? &nbsp; 
            <input type="radio" name="spareparts_included" id="spareparts_included_yes" value="1" >Yes &nbsp;
            <input type="radio" name="spareparts_included" id="spareparts_included_no" value="0" >No
        </td>
      </tr>  
      <tr valign="middle" class="alt">  
        <td align="left">Number of servicing <br/>(per annum)</td>
        <td align="left"><input type="text" name="number_of_servicing" id="number_of_servicing" size=8 ></td>
      </tr>  
      <tr valign="middle" >  
        <td align="left">Comments on firm's service</td>
        <td align="left">
            <textarea name="comment" id="comment" rows=4 cols=40></textarea>
        </td>
      </tr>  

    </table>
    </td>
</tr>

<tr>
    <td colspan=2 valign="middle" align="right"><input type="image" onclick="return submit_issue()" src="images/submit.png" >
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