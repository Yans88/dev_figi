<?php
if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_serialno = isset($_GET['serialno']) ? $_GET['serialno'] : null;
$item = array();

$transaction_prefix = TRX_PREFIX_LOAN;

$query = "SELECT * 
            FROM item 
            LEFT JOIN status ON status.id_status = item.id_status ";
if ($_serialno != null)    
    $query .= " WHERE Serial_No='$_serialno'";
else if ($_id > 0)
    $query .= " WHERE id_item='$_id' ";
else {
    echo '<script>alert("Item\'s ID or Serial No. is not specified!")</script>';
    return;
}
$rs = mysql_query($query);
if ($rs && mysql_num_rows($rs) > 0)
    $item = mysql_fetch_assoc($rs);
//print_r($item);
echo '<H2>Loan Record of Asset "'.$item['asset_no'].'"</H2>';
echo '<div style="width: 800px"><h3>Current Loan Record</h3></div>';

$loan_out_id = 0;

if ($item['id_status'] == ONLOAN){
    // get loan out info, latest is current status
    $query = "SELECT lo.* 
              FROM loan_item li, loan_out lo 
                WHERE li.id_item = $item[id_item] AND li.id_loan = lo.id_loan 
                ORDER BY loan_date DESC ";
    $rs = mysql_query($query); 
    $rows = mysql_num_rows($rs);    
    if ( $rows > 0){		
        $rec = mysql_fetch_assoc($rs);
        $loan_out_id = $rec['id_loan'];
?>
<table border="0" style="width: 800px" class="itemlist" cellpadding=2 cellspacing=1>
  <tr>
    <th width=100>Transaction No</th>
    <th width=80>Asset No.</th>
    <th width=80>Item Serial No.</th>
    <th width=100>Date Loaned</th>
    <th width=100>Date to be returned</th>
    <th width=80>User</th>
    <th width=80>Contact No.</th>
    <th width=20>Loan Detail</th>
   </tr>

<?php
echo <<<ROW
    <tr>
        <td>$transaction_prefix$rec[id_loan]</td>
        <td>$item[asset_no]</td>
        <td>$item[serial_no]</td>
        <td>$rec[loan_date]</td>
        <td>$rec[return_date]</td>
        <td>$rec[name]</td>
        <td>$rec[contact_no]</td>
        <td align="center"><a href='./?mod=loan&act=view_issue&id=$rec[id_loan]'><img class="icon" src="images/loupe.png"></a></td>
    </tr>
ROW;
    echo "</table>";
    } // loan info found out 
} else { // non-onloan
  echo '<br/><span class="error" align="left">Current Status of Item is "'.$item['status_name'].'".</span>';
}
?>
<br/>&nbsp;<br/>
<div style="width: 800px"><h3>Past Loan Record</h3></div>

<?php
    $query = "SELECT lo.* 
              FROM loan_item li, loan_out lo 
                WHERE li.id_item = $item[id_item] AND li.id_loan = lo.id_loan  ";
if ($loan_out_id > 0)
    $query .= " AND li.id_loan != $loan_out_id ";
$query .= " ORDER BY loan_date DESC ";
$rs = mysql_query($query);

if ($rs && (mysql_num_rows($rs) > 0)) {
    $rows = mysql_num_rows($rs);
?>
<br/>
<table  border=0 style="width: 800px" class="itemlist" cellpadding=2 cellspacing=1>
  <tr><td colspan=8><strong>Past Loan Record Frequency: <?php echo $rows?></strong></td></tr>
  <tr>  
    <th width=100>Transaction No</th>
    <th width=80>Asset No.</th>
    <th width=80>Serial No.</th>
    <th width=120>Date Loaned</th>
    <th width=120>Returned Date</th>
    <th width=80>User</th>
    <th width=80>Contact No.</th>
    <th width=20>Loan Detail</th>
  </tr>
  
<?php
    $i = 0;
    while ($rec = mysql_fetch_assoc($rs)){
        $class = ($i++ % 2 == 0) ? ' class="alt"' : ' class="normal"';
        echo <<<ROW1
    <tr $class>
        <td>$transaction_prefix$rec[id_loan]</td>
        <td>$item[asset_no]</td>
        <td>$item[serial_no]</td>
        <td>$rec[loan_date]</td>
        <td>$rec[return_date]</td>
        <td>$rec[name]</td>
        <td>$rec[contact_no]</td>
        <td align="center"><a href='./?mod=loan&act=view_return&id=$rec[id_loan]'><img class="icon" src="images/loupe.png"></a></td>
    </tr>
ROW1;
    }
    echo "</table>";  
} else {
  
  echo '<br/><span class="error" align="left">Item has no past loan record!</span>';
  
}
?> 
