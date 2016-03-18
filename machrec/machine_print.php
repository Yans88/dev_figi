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

ob_clean();
$style_path = defined('STYLE_PATH') ? STYLE_PATH : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>FiGi Productivity Tools</title>
<link rel="shortcut icon" type="image/x-icon" href="images/figiicon.ico" />
<link rel="stylesheet" href="<?php echo $style_path?>/style_print.css" type="text/css"  />
<script type="text/javascript"  >
function print_it(){
    var btn = document.getElementById("btnprint");
    if (btn){
        btn.style.display = "none";
        print();
    }
}
</script>
<style>
.machineinfo { border-collapse: collapse;  font-size: 12px; }
.machineinfo th {
    border: 1px solid black;
}
.machineinfo td {
    border: 1px solid black;
}
.machineinfo { border-collapse: collapse;   font-size: 12px;  }
.machineinfo th {
    border: 1px solid black;
}
.machineinfo td {
    border: 1px solid black;
}
</style>
</head>
<body>
<div id="contentcenter" align="center" >
    <div id="printout">
        <div id="header"><img src="images/logo_print.png" /></div>

<br/><br/>

<h4>Machine History Record (Print Preview)</h4>
<form method="post">
<input type="hidden" name="items" id="items" value="">
<table  class="maintenance_table" cellpadding=2 cellspacing=1>
<tr valign="top">
    <th colspan=2 align="center"> Machine Info   </th>
</tr>
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="machineinfo" >
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
        <td align="left"> <?php echo $item_info['country_of_manufacture']?>   </td>    
      </tr>
    </table>
    </td>
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="machineinfo" >
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
        <td align="left">Location</td>
        <td align="left"><?php echo $item_info['location']?></td>    
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
    <table width="100%" cellpadding=2 cellspacing=1 class="machineinfo" >
      <tr valign="middle">
        <th colspan=3>Service Vendor</th>
        <th colspan=4>Maintenance Details</th>
        <th rowspan=3>Remarks on vendor's service</th>
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
            $rec['spareparts_included_yes'] = ($rec['include_spareparts'] == 1) ? 'v' : '';
            $rec['spareparts_included_no'] = ($rec['include_spareparts'] != 1) ? 'v' : '';
            $class_name = (($cnt++ % 2) == 0) ? 'class="alt"' : 'class="normal"';
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
    <td colspan=2 valign="middle" align="center">
    <a class="button print" id="btnprint" href="javascript:print_it()">Print This Page</a>
    </td>
</tr>
<?php
    } // i can update
?>
</table>
</form>
<br/><br/>

