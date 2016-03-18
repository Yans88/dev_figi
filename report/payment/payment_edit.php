<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
require 'payment_util.php';

$_id = !empty($_GET['id']) ? $_GET['id'] : 0;
$_invoice_no = !empty($_GET['invoice_no']) ? $_GET['invoice_no'] : null;

$_msg = null;
$today = date('j-M-Y');
$rec = array();
$number_of_items = 0;
$item_list = null;
$info['invoice'] =  null;
$info['duration'] = 0;
$info['frequency'] = 1;
$info['reminder_lead_time'] = 1;
$info['remarks'] = null;
$info['emails'] = null;
$info['last_date_of_payment'] = null;
$info['next_date_of_payment'] = null;
$info['first_date_of_payment'] = null;
$info['date_of_purchase'] = null;

if (!empty($_POST['generate']) || !empty($_POST['add_item'])){
	
    $date_of_purchase = convert_date($_POST['date_of_purchase'], 'Y-m-d') . date(' H:i:s');
    switch($_POST['frequency']){
	case 3: $interval = 'INTERVAL 1 YEAR'; break;
	case 2: $interval = 'INTERVAL 6 MONTH'; break;
	case 1: $interval = 'INTERVAL 1 MONTH'; break;
	default: $interval = 'INTERVAL 1 WEEK'; break;
	}
	$_duration = ($_POST['duration'] >= 0) ? $_POST['duration'] : 1; 
	$duration = $_duration . ' MONTH'; 
	$send_notification = true;
	$first_date_of_payment = convert_date($_POST['first_date_of_payment'], 'Y-m-d');
	if ($_id > 0)
		$query = "REPLACE INTO payment(id_payment, date_of_purchase, duration, frequency, first_date_of_payment, next_date_of_payment,
					last_date_of_payment, invoice_no, remarks, reminder_lead_time, emails)
					VALUES('$_id', '$date_of_purchase', '$_POST[duration]', '$_POST[frequency]', '$first_date_of_payment', 
					DATE_ADD('$first_date_of_payment',  $interval),  DATE_ADD('$first_date_of_payment', INTERVAL $duration), 
					'$_POST[invoice_no]', '$_POST[remarks]', '$_POST[reminder_lead_time]', '$_POST[emails]')";
	else {
        $send_notification = true;
		$query = "INSERT INTO payment(date_of_purchase, duration, frequency, first_date_of_payment, next_date_of_payment,
					last_date_of_payment, invoice_no, remarks, reminder_lead_time, emails)
					VALUES('$date_of_purchase', '$_POST[duration]', '$_POST[frequency]', '$first_date_of_payment', 
					DATE_ADD('$first_date_of_payment',  $interval),  DATE_ADD('$first_date_of_payment', INTERVAL $duration), 
					'$_POST[invoice_no]', '$_POST[remarks]', '$_POST[reminder_lead_time]', '$_POST[emails]')";
	}
    mysql_query($query);
	//echo mysql_error().$query;
	if (mysql_affected_rows() > 0){
		$id = mysql_insert_id();
        if ($send_notification){
            send_payment_notification($id);
        }
        
		ob_clean();
		if (!empty($_POST['add_item'])){
			header('Location: ./?mod=item&act=edit&for=payment&invoice_no='.$_invoice_no);
		} else {
			header('Location: ./?mod=payment&act=view&id='.$id);
		}
		ob_end_flush();
		exit;
        
	}
}

if (!empty($_invoice_no) || !empty($_id)){
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
		$info = mysql_fetch_assoc($rs);
		$info['invoice'] = $info['invoice_no'];
		$_invoice_no = $info['invoice_no'];
		$_id = $info['id_payment'];
	
	} 
	// get items under invoice_no
	$query  = "SELECT invoice, date_format(date_of_purchase, '%d-%b-%Y') as date_of_purchase, serial_no, id_item, cost, asset_no     
			   FROM item 
			   WHERE invoice = '$_invoice_no' ";
	$rs = mysql_query($query);
	if ($rs && (mysql_num_rows($rs)>0)){
		$item_list .= '<tr><th align="center" width=25>No</th>
						<th align="center">Asset No</th><th align="center">Serial No</th><th align="center">Cost</th></tr>';
		$cnt = 1;
		while ($rec = mysql_fetch_assoc($rs)){
			$items[] = $rec['serial_no'];
			if ($info['invoice'] == null)
				$info['invoice'] = $rec['invoice'];
			if ($info['date_of_purchase'] == null)
				$info['date_of_purchase'] = $rec['date_of_purchase'];
			$class_name = ($cnt % 2 == 0) ? 'alt' : 'normal';
			$item_list .=  '<tr valign="top" align="left" class="'.$class_name.'">
							<td align="right">'.($cnt++).'.</td>
							<td align="center">'.$rec['asset_no'].'</td>
							<td align="left"><a href="./?mod=item&act=view&id='.$rec['id_item'].'">'.$rec['serial_no'].'</a></td>
							<td align="center">'.$rec['cost'].'</td>
							</tr>';
		}
		$number_of_items = count($items);
	} else {
		$rec['invoice'] =  null;
		$query  = "SELECT DISTINCT invoice, date_format(date_of_purchase, '%d-%b-%Y') as date_of_purchase, 
					count(id_item) num_of_items 
				   FROM item 
				   WHERE invoice like '$_invoice_no%' ";
		$rs = mysql_query($query);
		while ($rec = mysql_fetch_assoc($rs))
			$invoices[] = $rec;
	}
}

$frequency_combo = build_combo('frequency', $frequency_list, $info['frequency']);

$caption = ($_id > 0) ? 'Edit Payment Schedule' : 'Create New Payment Schedule';
?>
<script type="text/javascript">
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

function submit_issue(){
    var frm = document.forms[0]
    if (frm.serial_no.value == ''){
        alert('Please fill in Serial No of Item!');
        return false;
    }
    if (frm.name.value == ''){
        alert('Please fill in Loan Out to!');
        return false;
    }
    if (frm.nric.value == ''){
        alert('Please fill in NRIC!');
        return false;
    }
    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var ok = confirm('Are you sure to proceed with this Loan-Out?');
    if (!ok)
        return false;
    var cvs = document.getElementById('imageView');
    frm.issue_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.invoice_signature.value = cvs.toDataURL("image/png");    
    frm.issue.value = 1;
    //frm.submit();
    return true;
}

</script>
<h4><?php echo $caption?></h4>
<form method="post">
<?php

if (!empty($info['invoice'])){

    echo <<<INVOICE
<input type="hidden" name="invoice_no" value="$_invoice_no" >
<!-- <h4>Payment Schedule Generation for PO/Invoice  No. $info[invoice]</h4> -->
<table  class="invoice_table" border=0 cellpadding=2 cellspacing=1 width=700>
<tr valign="top">
   <td width=350>
    <table width="100%" cellpadding=3 cellspacing=1 class="invoice_table cellform" >
      <tr valign="top" align="left">
        <th align="left" width=130>Invoice No.</th>
        <th align="left" >$info[invoice]</th>
	  </tr>
      <tr valign="top" align="left">
        <td align="left">Date of Purchace</td>
        <td align="left" >
			<input type=text name=date_of_purchase id=date_of_purchase size=10 value="$info[date_of_purchase]">
            <button type="button" id="button_date_of_purchase"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></button>
            <script>
			$('#button_date_of_purchase').click(
			  function(e) {
				$('#date_of_purchase').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
			</script>
		</td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Duration</td>
        <td align="left"><input type=text name=duration id=duration size=4 value="$info[duration]"></td>
	  </tr>
      <tr valign="top">  
        <td align="left">Frequency</td>
        <td align="left">$frequency_combo</td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">First Payment Date</td>
        <td align="left">
			<input size=12 type=text name=first_date_of_payment id=first_date_of_payment value="$info[first_date_of_payment]">
			<button type="button" id="button_first_date_of_payment"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></button>
            <script>
			$('#button_first_date_of_payment').click(
			  function(e) {
				$('#first_date_of_payment').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y"}).focus();
				e.preventDefault();
			  } );
			</script>

		</td>
      </tr>  
      <tr valign="top">  
        <td align="left">Next Payment Date</td>
        <td align="left"><input size=12 type=text name=next_date_of_payment id=next_date_of_payment value="$info[next_date_of_payment]" readonly></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Last Payment Date</td>
        <td align="left"><input size=12 type=text name=last_date_of_payment id=last_date_of_payment value="$info[last_date_of_payment]" readonly></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Reminder Lead Time (No. of days before)</td>
        <td align="left"><input type=text name=reminder_lead_time id=reminder_lead_time size=4 value="$info[reminder_lead_time]"></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Remarks</td>
        <td align="left"><textarea name=remarks cols=28 rows=3>$info[remarks]</textarea></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Email Address for Notification</td>
        <td align="left">
			<textarea name=emails id=emails cols=28 rows=3>$info[emails]</textarea>	
			<br/><small>*comma separated for multiple email.</small>
		</td>    
      </tr>  
      <tr valign="middle">  
        <td align="center" colspan=2 style="padding-top: 40px">
			<button type=submit name=generate value="1">Save Payment</button> 
			<!--button type=submit name=add_item value="1">Add New Item</button-->
		</td>
      </tr>  
    </table>
    </td>
    <td width=250>
    <table width="100%" cellpadding=3 cellspacing=1 class="invoice_table" >
      <tr valign="top" align="left">
        <th align="left">Number of Items.</th>
        <th align="center">$number_of_items</th>
	  </tr>
	  <tr><td colspan=2 >
	  <div  style="overflow: auto; height:400px">
 	  <table width="100%" cellpadding=1 cellspacing=1>
	  $item_list
	  </table>
	  </div>
	  </td></tr>
    </table>
    </td>
</tr>
</table>

INVOICE;
/*
      <tr valign="top">  
        <td align="left"><a href="">Add New Item</a></td>
        <td align="left"><a href="">Add Existing Item</a></td>
      </tr>  
	  <tr class="alt">
        <th align="left" >
			<input type=text id="serial_no" size=30 name="serial_no" value="" onKeyUp="suggest(this, this.value);" onBlur="fill('serial_no', this.value);">
			<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
				<img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
				<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
			</div>
		</th>
      </tr>  
*/

} else { // show searching info
	echo <<<SEARCH
	
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
	<input type="submit" name="selectInvoice" value="Save the Payment">
	</td>
</tr>
</table>
SEARCH;

}
?>
</form>
