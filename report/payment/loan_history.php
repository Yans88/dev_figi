<?php
if (!defined('FIGIPASS')) exit;
$item_id = isset($_GET['item'] ? $_GET['item'] : 0;

$query = "SELECT * FROM submitloan WHERE Serial_Number='$_serialno' AND status= 'Onloan' ORDER BY id";
$rs = mysql_query($query);
$rows = mysql_num_rows($rs);

?>

<H2>Loan Record of Item</H2>
<h3>Current Loan Record</h3>
<table width="89%" border="0" style="font:arial;" class="itemlist">
  <tr>
    <th>Transaction No</th>
    <th>Item Serial No.</th>
    <th>Date Loaned</th>
    <th>Date to be returned</th>
    <th>Contact No.</th>
    <th>View</th>
   </tr>

<?php
if ( $rows >= 1){		
  while($rec=mysql_fetch_array($rs)){
    //$status=$rec[12];
    echo "<tr>
        <td>LN".$rec[0]."</td>
        <td>".$rec[1]."</td>
        <td>".$rec[6]."</td>
        <td>".$rec[7]."</td>
        <td>".$rec[9]."</td>
        ";
      echo "<td><a href='newsecond_signature.php?&serialnox=".$rec[0]."'>View Form</a></td>";
      echo"</tr>";
  }
} else {
		$query = "SELECT item.id_item, item_status.status from item, item_status where item.serial_no='".$_GET['serialno']."' AND item.id_item = item_status.id_item ";
		$rs = mysql_query($query);
		$rec = mysql_fetch_array($rs);
		echo "<strong style='color:#FFFFFF;'><h2>Item Status is currently  ".$rec[1]."</h2>";
		
}
echo "</table>";
		
  $query = "SELECT * FROM submitloan WHERE Serial_Number='".$_GET['serialno']."' and status= 'Storage' order by Loan_Period_end desc";
  $rs = mysql_query($query);
  $rows = mysql_num_rows($rs);
?>
<table width="89%" border="0" style="font:arial;" class="itemlist">
  <tr>
    <td colspan=3><h3> Past Loan Record </h3></td>
    <td colspan=3><h3 >Past Loan Record Frequency: <?php echo $rows?></h3></td>
  </tr>
  <tr>  
    <td>Transaction No</td>
    <td>Item Serial No.</td>
    <td>Date Loaned</td>
    <td>Returned Date</td>
    <td>Contact No.</td>
    <td>View</td>
  </tr>
  
<?php
if ( $rows >= 1){		
  while( $rec = mysql_fetch_array($rs)){
    $status=$rec[12];
    echo "<tr >
        <td>LN".$rec[0]."</td>
        <td>".$rec[1]."</td>
        <td>".$rec[6]."</td>
        <td>".$rec[7]."</td>
        <td>".$rec[9]."</td>
        ";
      echo "<td bgcolor='#006600'><a href='reportborrownreutrn.php?username=".$_GET['username']."&serialnox=".$rec[0]."'>View </a></td>";
        echo"</tr>";
  }
  
} else {
  
  echo "<strong style='color:#FFFFFF;'><h2> PAST LOAN RECORD DATA NOT FOUND</h2>";
  
}
echo "</table>";
 
