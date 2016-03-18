<?php
if (!defined('FIGIPASS')) exit;
?>
<h4>Existing Schedules</h4>
<?php

$link_create = null;
/*
if ($i_can_create && !SUPERADMIN) {
    echo <<<LINK1
<div valign="middle" >
<a class="button" href="./?mod=payment&act=generate" id="leftlink">Create New Schedule</a>
</div>
LINK1;
} 
*/
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;


//count
$query  = "SELECT count(*) FROM payment ";           
$rs = mysql_query($query);
$rec = mysql_fetch_row($rs);
$total_item = $rec[0];
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page)
	$_page = $total_page;
else if ($_page < 1)
	$_page = 1;

	$_start = ($_page-1) * $_limit;

$query  = "SELECT *, date_format(date_of_purchase, '%d-%b-%Y') as date_of_purchase, 
		   date_format(last_date_of_payment, '%d-%b-%Y') as last_date_of_payment, 
		   date_format(next_date_of_payment, '%d-%b-%Y') as next_date_of_payment 
           FROM payment
           LIMIT $_start,$_limit ";
$rs = mysql_query($query);
$counter = $_start+1;
if ($rs && mysql_num_rows($rs) > 0) {

?>
<table width="700" cellpadding=2 cellspacing=1 class="itemlist" id="paymenttable">
<tr height=30>
  <th >Invoice No</th><th width=80>Date of Purchase</th>
  <th width=80>Duration (Month)</th><th width=40>Frequency</th>
  <th width=80>Last Payment</th><th width=80>Next Payment</th>
  <th width=80>Action</th>
</tr>

<?php
while ($rec = mysql_fetch_array($rs))
{
  $_class = ($counter % 2 == 0) ? 'class="alt"':null;
  $edit_link = null;;
  if ($i_can_update && (USERDEPT>0) && !SUPERADMIN)
        $edit_link = '<a href="?mod=payment&act=edit&invoice_no='.$rec['invoice_no'].'" title="view" ><img src="images/loupe.png"></a>'; 
  echo <<<DATA
  <tr $_class>
  <td>$rec[invoice_no]</td>
  <td align="center">$rec[date_of_purchase]</td>
  <td align="center">$rec[duration]</td>
  <td align="center">$rec[frequency]</td>
  <td align="center">$rec[last_date_of_payment]</td>
  <td align="center">$rec[next_date_of_payment]</td>
  <td align="center">
    <a href="?mod=payment&act=view&invoice_no=$rec[invoice_no]" title="view" ><img src="images/loupe.png"></a> 
    $edit_link
<!--	| 
    <a href="?mod=payment&act=del&invoice_no=$rec[invoice_no]" 
       onclick="return confirm('Are you sure you want to delete $rec[invoice_no]?')" title="delete" ><img src="images/delete.png"></a>
	   -->
  </td>
  </tr>
DATA;
  $counter++;
}

    echo '<tr ><td colspan=7 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=payment&sub=item&act=list&page=');
    echo '</td></tr>';
    echo '</table><br/>';

} else { // no records
    echo '<div class="error" style="margin-top: 30px;">Data is not available!</div>';
}
?>
<script>
var p = $('#paymenttable').position();
if (p.left > 0)
  $('#leftlink').offset({left: p.left});
</script>

