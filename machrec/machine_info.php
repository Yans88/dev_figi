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

if (isset($_POST['save']) && ($_POST['save'] == 1)){    
   
        $this_time = date(' H:i:s');
        // update machine info
        $operation_type = implode(',', $_POST['how_operated']);
        
        $query = "REPLACE INTO machine_info(id_machine, id_item, operation_type, country_of_manufacture, defect_description, defect_date) 
                  VALUES ($_id, '$_POST[id_item]', '$operation_type', '$_POST[country_of_manufacture]', '$_POST[defect_description]', now() )";
        mysql_query($query);
        //echo mysql_error().$query;
        if (mysql_affected_rows()>1){            
            
            ob_clean();
            header('Location: ./?mod=machrec&sub=machine&act=view&id=' . $_id);
            ob_end_flush();
            exit;      
           
        } else if (mysql_affected_rows()>0){            
            $_id = mysql_insert_id();
            
            ob_clean();
            header('Location: ./?mod=machrec&sub=machine&act=issue&id=' . $_id);
            ob_end_flush();
            exit;      
           
        } 
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
} else {
    if ($_GET['by'] == 'serial_no')
        $item_info = get_item_by_serial($_GET['value']);
    else
        $item_info = get_item_by_asset($_GET['value']);
    $item_info['country_of_manufacture'] = null;
    $item_info['opr_electric'] = null;
    $item_info['opr_electronic'] = null;
    $item_info['opr_manual'] = null;
    $item_info['defect_description'] = null;
}

?>

<h4>Edit/Create New Machine Info</h4>
<form method="post">
<input type="hidden" name="id_item" value="<?php echo $item_info['id_item']?>">
<table  class="maintenance_table" cellpadding=2 cellspacing=1>
<tr valign="top">
    <th colspan=2 align="center"> Machine Info   </th>
</tr>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" class="normal">  
        <td align="left" width=120>Asset No</td>
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
        <td align="left">
            <input type="text" id="country_of_manufacture" name="country_of_manufacture" autocomplete="off"  size=30  value="<?php echo $item_info['country_of_manufacture']?>" 
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
            <input type="checkbox" name="how_operated[]" value="manual" <?php echo $item_info['opr_manual']?> >Manual
            <input type="checkbox" name="how_operated[]" value="electric" <?php echo $item_info['opr_electric']?> >Electric
            <input type="checkbox" name="how_operated[]" value="electronic" <?php echo $item_info['opr_electronic']?> >Electronic
        </td>    
      </tr>
      <tr valign="top">  
        <td align="left">Defect Descrpition</td>
        <td align="left"><textarea name="defect_description" rows=4 cols=30><?php echo $item_info['defect_description']?></textarea>
        </td>    
      </tr>  
      </table>
      </td>
</tr>
<tr>
    <td colspan=2 valign="middle" align="right"><button type="submit" name="save" value=1 > Save  Machine Info </button>
    </td>
</tr>
</table>
</form>
<br/><br/>

<script type="text/javascript"  >

var department = '<?php echo $dept ?>';

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
