<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_invoice_no = !empty($_POST['invoice_no']) ? $_POST['invoice_no'] : null;

$_msg = null;
$today = date('j-M-Y');
$rec = array();
$info['invoice'] =  null;
$number_of_items = 0;
$item_list = null;
$invoices = array();
if (!empty($_POST['selectInvoice'])){
	// find exact invoice
	$query  = "SELECT count(invoice)
			   FROM item 
			   WHERE invoice = '$_invoice_no' ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_row($rs);
		$number_of_items = $rec[0];
	}
	if ($number_of_items == 0) { //invoice is not available, search similar text
		$query  = "SELECT DISTINCT invoice, date_format(date_of_purchase, '%d-%b-%Y') as date_of_purchase, 
					count(id_item) num_of_items 
				   FROM item 
				   WHERE invoice like '%$_invoice_no%' 
				   GROUP BY invoice ";
		$rs = mysql_query($query);
		while ($rec = mysql_fetch_assoc($rs))
			$invoices[] = $rec;
	}
	
} 
$caption = 'Generate Payment Schedule';
echo <<<TEXT
<script>

function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("payment/suggest_invoice_no.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

</script>
<h4>$caption</h4>
<form method="post">

<style>
#suggestions { 
	text-align: left;
	left: 0px;
	margin: 0px 0px 0px 0px;
	width: 130px;
	background-color: #062312;
	-moz-border-radius: 7px;
	-webkit-border-radius: 5px;
	border: 1px solid #062312;
	font-size: 10pt;
}
#suggestionsList ul{
	margin: 1px 0 0 -35px;
	list-style-type: none;
}

</style>
<table id="search_invoice">
<tr valign="top">
	<td align="right">PO / Invoice No.: </td>
	<td align="left">
	<input type=text id="invoice_no" size=16 name="invoice_no" value="$_invoice_no" onKeyUp="suggest(this, this.value);" onBlur="fill('invoice_no', this.value);">
	<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
		<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
	</div>
		<!--img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" /-->
	</td>
	<td align="left">
	<button type="submit" name="selectInvoice" value="1">Search Invoice</button>
	</td>
</tr>
</table>
TEXT;

if ($number_of_items > 0){ // found equal invoice_no
	$query  = "SELECT * FROM item 
			   WHERE invoice = '$_invoice_no' ";
	$rs = mysql_query($query);
	$rec = mysql_fetch_assoc($rs);
	ob_clean();
	header('Location: ./?mod=payment&act=edit&invoice_no='.$rec['invoice']);
	ob_end_flush();
	exit;
} else if (count($invoices) > 0){
	$item_count = count($invoices);
echo <<<INVOICE
<input type="hidden" name="invoice_no" value="$_invoice_no" >
<h4>Found about $item_count Invoices matches search text '$_invoice_no'.</h4>
<table  class="invoice_table" border=0 cellpadding=2 cellspacing=1 width=500>
  <tr valign="top" align="center">
	<th>Invoice No.</th>
	<th width=130 >Date of Purchase</th>
	<th width=130 >Number of Items</th>
	<th >Action</th>
  </tr>
INVOICE;
	foreach ($invoices as $rec){
		echo <<<ROW
  <tr valign="top" align="left">
	<td align="left">$rec[invoice]</td>
	<td align="center">$rec[date_of_purchase]</td>
	<td align="center">$rec[num_of_items]</td>
	<td align="center"><a href="./?mod=payment&act=edit&invoice_no=$rec[invoice]">Create Schedule</a></td>
  </tr>  
ROW;
	}
echo '</table>';

} else { // show searching info

}
?>
</form>
