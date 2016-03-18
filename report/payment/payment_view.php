<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = !empty($_GET['id']) ? $_GET['id'] : null;
$_invoice_no = !empty($_GET['invoice_no']) ? $_GET['invoice_no'] : null;

$_msg = null;
$today = date('j-M-Y');
$info = array();
$invoice = array();
$number_of_items = 0;
$item_list = null;
$invoice['invoice'] =  null;
$invoice['duration'] = 0;
$invoice['frequency'] = 0;
$invoice['reminder_lead_time'] = 0;
$invoice['remarks'] = 'n/a';
$invoice['emails'] = 'n/a';
$invoice['date_of_purchase'] = null;

if (!empty($_invoice_no) || !empty($_id)){
	// get payment info, based on invoice_no
	$dtf = '%d-%b-%Y';
	$query  = "SELECT *, date_format(date_of_purchase, '$dtf') date_of_purchase, 
				date_format(first_date_of_payment, '$dtf') first_date_of_payment,
				date_format(next_date_of_payment, '$dtf') next_date_of_payment,
				date_format(last_date_of_payment, '$dtf') last_date_of_payment 
				FROM payment  ";
	if (!empty($_invoice_no))
		$query .= " WHERE invoice_no = '$_invoice_no' ";
	else if (!empty($_id))
		$query .= " WHERE id_payment = '$_id' ";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	if ($rs && (mysql_num_rows($rs)>0)){
		$invoice = mysql_fetch_assoc($rs);
		$_invoice_no = $invoice['invoice_no'];
		$invoice['invoice'] = $invoice['invoice_no'];
		$_id = $invoice['id_payment'];
	}
	// get items within the invoice_no
	$query  = "SELECT invoice, date_format(date_of_purchase, '%d-%b-%Y') as date_of_purchase, serial_no   
			   FROM item 
			   WHERE invoice = '$_invoice_no' ";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs)>0)){
		$item_list .= '<tr><th align="center" width=35>No</th><th align="center">Serial No.</th></tr>';
		$cnt = 1;
		while ($rec = mysql_fetch_assoc($rs)){
			$items[] = $rec['serial_no'];
			if ($invoice['invoice'] == null)
				$invoice['invoice'] = $rec['invoice'];
			if ($invoice['date_of_purchase'] == null)
				$invoice['date_of_purchase'] = $rec['date_of_purchase'];
			$class_name = ($cnt % 2 == 0) ? 'alt' : 'normal';
			$item_list .=  '<tr align="left" class="'.$class_name.'">
							<td align="right">'.($cnt++).'. </td>
							<td align="left">'.$rec['serial_no'].'</td>
							</tr>';
			
		}
		$number_of_items = count($items);		
	}
	if (empty($invoice['remarks'])) $invoice['remarks'] = 'n/a';
	if (empty($invoice['emails'])) $invoice['emails'] = 'n/a';
}

$caption = 'View Payment Schedule';
echo <<<TEXT
<script>

</script>
<h4>$caption</h4>
TEXT;

if (!empty($invoice)){

echo <<<INVOICE
<table  class="invoice_table" border=0 cellpadding=2 cellspacing=1 width=700>
<tr valign="top">
   <td width=450>
    <table width="100%" cellpadding=2 cellspacing=1 class="invoice_table cellform" >
      <tr align="left">
        <th align="left" width=130>Invoice No.</th>
        <th align="left" >$invoice[invoice]</th>
	  </tr>
      <tr align="left">
        <td align="left">Date of Purchace</td>
        <td align="left" >$invoice[date_of_purchase]</td>
      </tr>  
      <tr class="alt">  
        <td align="left">Duration</td>
        <td align="left">$invoice[duration]</td>
	  </tr>
      <tr>  
        <td align="left">Frequency</td>
        <td align="left">$invoice[frequency]</td>
      </tr>  
      <tr class="alt">  
        <td align="left">Last Payment Date</td>
        <td align="left">$invoice[last_date_of_payment]</td>
      </tr>  
      <tr>  
        <td align="left">Next Payment Date</td>
        <td align="left">$invoice[next_date_of_payment]</td>
      </tr>  
      <tr class="alt">  
        <td align="left">Reminder Lead Time (No. of days before)</td>
        <td align="left">$invoice[reminder_lead_time]</td>
      </tr>  
      <tr>  
        <td align="left">Remarks</td>
        <td align="left">$invoice[remarks]</td>
      </tr>  
      <tr class="alt">  
        <td align="left">Email Address for Notification</td>
        <td align="left">$invoice[emails]</td>    
      </tr>  
    </table>
    </td>
    <td>
    <table width="100%" cellpadding=2 cellspacing=1 class="invoice_table" >
      <tr align="left">
        <th align="left">Number of Items.</th>
        <th align="center">$number_of_items</th>
	  </tr>
      <tr>
	  <tr><td colspan=2  align="center">
		  <div  style="overflow: auto; height:400px">
		  <table width="100%" cellpadding=1 cellspacing=1 class="invoice_table">
		  $item_list
		  </table>
		  </div>
	  </td></tr>	  
    </table>
    </td>
</tr>
</table>

INVOICE;

}
?>
<br/>&nbsp;
