<?php
if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_serialno = isset($_GET['serialno']) ? $_GET['serialno'] : null;
$item = get_key($_id);

echo '<H2>Loan Record</H2>';

$curr_exists = false;
$old_exists = false;

$loan_out_id = 0;
$query = "SELECT * 
            FROM key_item kt             
            LEFT JOIN key_loan_item kli ON kli.id_item = kt.id_item   
			LEFT JOIN key_loan kl ON kl.id_loan = kli.id_loan			
            LEFT JOIN user u ON u.id_user = kl.id_user
            WHERE kt.id_item = $item[id_item] AND status = 'On Loan'
            ORDER BY loan_start DESC ";
$rs = mysql_query($query); 
$rows = mysql_num_rows($rs);    
if ( $rows > 0){		
    $curr_exists = true;
    $rec = mysql_fetch_assoc($rs);
    $loan_out_id = $rec['id_loan'];
	
	$date = date_create($rec[loan_start]);
	$loan_start = date_format($date,"d-M-Y h:i");
	
?>
<div style="width: 800px"><h3>Current Loan Record</h3></div>
<table border="0" style="width: 800px" class="itemlist" cellpadding=2 cellspacing=1>
  <tr>
    <th width=100>Transaction No</th>
    <th width=80>Serial No.</th>    
    <th width=115>Date Loaned</th>
    <th width=80>User</th>
    <th width=80>Contact No.</th>
   </tr>

<?php
echo <<<ROW
    <tr>
        <td>KLN$rec[id_loan]</td>     
        <td>$rec[serial_no]</td>       
        <td>$loan_start</td>
        <td>$rec[full_name]</td>
        <td>$rec[contact_no]</td>
    </tr>
ROW;
    echo "</table>";
} // loan info found out 

?>
<?php


$query = "SELECT * 
            FROM key_loan_item kli 
            LEFT JOIN key_loan kl ON kl.id_loan = kli.id_loan
            LEFT JOIN key_item ki ON ki.id_item = kli.id_item           
            LEFT JOIN user u ON u.id_user = kl.id_user
            WHERE kli.id_item = $item[id_item] and ki.status = 'Available for Loan'
            ORDER BY loan_start DESC ";
			
$rs = mysql_query($query);

if ($rs && (mysql_num_rows($rs) > 0)) {
    $rows = mysql_num_rows($rs);
    $old_exists = true;
?>
<br/>
<div style="width: 800px"><h3>Past Loan Record</h3></div>
<table  border=0 style="width: 800px" class="itemlist" cellpadding=2 cellspacing=1>
  <tr><td colspan=8><strong>Past Loan Record Frequency: <?php echo $rows?></strong></td></tr>
  <tr>  
    <th width=100>Transaction No</th>
    
    <th width=80>Serial No.</th>
   
    <th width=115>Date Loaned</th>
    <th width=115>Date Returned</th>
    <th width=80>User</th>
    <th width=80>Contact No.</th>
  </tr>
  
<?php
    $i = 0;
	
    while ($rec = mysql_fetch_assoc($rs)){
        $class = ($i++ % 2 == 0) ? ' class="alt"' : ' class="normal"';
		$date = date_create($rec[return_date]);
		$return_date = date_format($date,"d-M-Y h:i");
		$date = date_create($rec[loan_start]);
		$loan_start = date_format($date,"d-M-Y h:i");
        echo <<<ROW1
    <tr $class>
        <td>KLN$rec[id_loan]</td>
       
        <td>$rec[serial_no]</td>
      
        <td>$loan_start</td>
        <td>$return_date</td>
        <td>$rec[full_name]</td>
        <td>$rec[contact_no]</td>
    </tr>
ROW1;
    }
    echo "</table>";  
} else {
  
 // echo '<br/><br/><span class="error">Item has no past loan record!</span>';
  
}

if (!$old_exists && !$curr_exists)
    echo '<div class="error"> The Key doesn\'t has history records!</div>';
?> 
