<?php
/*
main
*/

if (!defined('FIGIPASS')) return;
$i_can_create = (USERGROUP == GRPADM);
$i_can_update = (USERGROUP == GRPADM);



if (!empty($_sub) && empty($_mod)){
    
    require_once $_sub . '.php';
    
} else {

require_once 'loan/loan_util.php';

$msgbox = null;
// get loan_return_due_date
$rs = loan_return_due_date_query(USERDEPT);
if ($rs && mysql_num_rows($rs) > 0){
    $msgbox .= '<div class="due_date_loans"><div class="header foldtoggle">&nbsp;<h4>List of Overdue Loan</h4><a id="btn_due_date_loan_list" rel="open" href="javascript:void(0)">&uarr;</a></h4></div>';
    $msgbox .= '<div class="notification message" id="due_date_loan_list">';
    $msgbox .= '<table style="min-width:580px" cellpadding=3 cellspacing=0 border=0 id="duedatelist">';
    $msgbox .= '<tr><th>Request No.</th><th>Requester</th><th>Loan Start</th><th>Return Date</th><th>Category</th><th>Qty</th></tr>';
	$no = 1;
    while ($rec = mysql_fetch_assoc($rs)){
		$cn = ($no>10) ? 'overten' : null;
        $msgbox .= '<tr class="'.$cn.'"><td><a href="./?mod=loan&act=view_issue&id='.$rec['id_loan'].'">LR' . $rec['id_loan'] . '</a></td><td>';
        $msgbox .=  $rec['name'] . '</td><td align="center">' . $rec['loan_date'] . '</td><td align="center">' . $rec['return_date'];
        $msgbox .= '</td><td align="center">' . $rec['category_name'] . '</td><td align="center">' . $rec['quantity'] . '</td></tr>';
        $no++;
    }
	if ($no>10)
		$msgbox .= '<tr id="last_row"><td colspan=6 style="text-align: right"><a href="javascript:void(0)" id="toggleexpand">more...</a></td></tr>';
    $msgbox .= '</table></div></div>&nbsp;';
}
echo "<br/>$msgbox";
include_once 'upcoming_events.php';


?>
<style>
#btn_due_date_loan_list { display: block; float: right;  }
.overten { display: none; }
</style>
<script type="text/javascript">

$('#btn_due_date_loan_list').click(function (e){
    toggle_fold(this);
});

var expanded = false;

$('#toggleexpand').click(function(){
	if (!expanded){
		$('.overten').show();
		$(this).html('min...');
	} else {
		$('.overten').hide();
		$(this).html('more...');
	}
	expanded = !expanded;
});
</script>
<br/> <br/>

<div id="generalmenu" align="center">
<?php if (SUPERADMIN) { ?>    
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Manage Users</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=user">User List</a></li>
        <li><a href="./?mod=user&act=edit">Create New User</a></li>
        <li><a href="./?mod=user&sub=group">Group List</a></li>
      </ul>
    </div>
<?php } ?>    
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Manage Items</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=item">Equipment Items</a></li>
        <li><a href="./?mod=deskcopy">Deskcopy Items</a></li>
        <li><a href="./?mod=keyloan">Key Loan</a></li>
        <li><a href="./?mod=consumable">Consumable Items</a></li>
        <!--li><a href="./?mod=item&act=edit">Create New Item</a></li-->
        <li><a href="./?mod=item&sub=vendor">Vendor</a></li>
        <li><a href="./?mod=item&sub=manufacturer">Manufacturer</a></li>
        <li><a href="./?mod=item&sub=brand">Brand</a></li>
      </ul>
    </div>
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Manage Loan</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=portal&portal=loan">Loan Portal</a></li>
        <li><a href="./?mod=loan&sub=loan">Loan Request Records</a></li>
        <li><a href="./?mod=user&act=loan">Individual User Loan Records</a></li>
      </ul>
    </div>
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Facility</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=portal&portal=facility">Booking Calendar</a></li>
        <li><a href="./?mod=portal&portal=facility&act=make">Make Booking</a></li>
        <li><a href="./?mod=portal&portal=student_usage">Student Usage</a></li>
      </ul>
    </div>
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Miscellaneous </div>
      <ul class="menuboxbody">
        <li><a href="./?mod=calendar">Events Calendar</a></li>
        <li><a href="./?sub=location">Locations</a></li>
<?php
    if (SUPERADMIN) {
?>
        <li><a href="./?mod=admin&sub=setting">Global Setting</a></li>
        <li><a href="./?mod=admin&sub=backuprestore">Data Backup & Restore</a></li>
<?php
    }
?>
      </ul>
    </div>
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Portals</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=portal&portal=loan">Make Loan Request</a></li>
        <li><a href="./?mod=portal&portal=service">Requesting a Service</a></li>
        <li><a href="./?mod=portal&portal=fault">Fault Reporting</a></li>        
        <li><a href="./?mod=booking">Facility Booking</a></li>        
        <li><a href="./?mod=portal&portal=calendar">Calendar</a></li>        
      </ul>
    </div>
    
</div>
    
<?php
} // sub without mod
?>
<div class="clear">&nbsp;</div>
<br/>&nbsp;
<br/>&nbsp;
