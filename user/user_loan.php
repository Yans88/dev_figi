<?php

if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_username = null;
$dept = USERDEPT;

if ($_id == 0){
    include 'user/user_loan_search.php';
    return;
}
$query = 'SELECT full_name FROM user WHERE id_user = ' .$_id;
$res = mysql_query($query);
if (mysql_num_rows($res) > 0) {
    $rec = mysql_fetch_row($res);
    $_username = $rec[0];
}

function get_serials($id = 0)
{   $data = array();
    $query = 'SELECT i.serial_no FROM loan_item li 
                LEFT JOIN item i ON li.id_item = i.id_item 
                WHERE id_loan = '. $id;            
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)) {
      while ($rec = mysql_fetch_row($rs))
        $data[] = $rec[0];
    }
    return $data;
}

$transaction_prefix = TRX_PREFIX_LOAN;

?>

<h2>User Loan Record of <?php echo $_username?></h2>
<a href="./?mod=user&sub=user&act=loan_search">search for other user</a>	
<h3>Items still On Loan</h3>
<table width="100%" class='userlist' cellpadding=2>
  <tr>
    <th>Transaction No</th>
    <th>Department</th>
    <th>Category</th>
    <th>Item Serial No.</th>
    <th>Date Loaned</th>
    <th>Date to be returned</th>    
    <th>Contact No</th>
    <th>Detail</th>
  </tr>

<?php  

$query = "SELECT lr.id_loan, lr.id_category, date_format(lp.issue_date, '%d-%b-%Y %H:%i') as loan_date, 
            date_format(lo.return_date, '%d-%b-%Y %H:%i') as return_date, lo.name, lo.contact_no,  
            category_name, department_name 	  
            FROM loan_request lr, loan_out lo, loan_process lp, category c  
            LEFT JOIN department d ON c.id_department = d.id_department 	  
            WHERE lr.requester ='$_id' AND lr.status = 'LOANED' AND lr.id_loan=lp.id_loan  AND lr.id_loan=lo.id_loan 
            AND c.id_category = lr.id_category ";
if ($dept > 0)
    $query .= ' AND c.id_department = ' . $dept;
$rs = mysql_query($query);
//echo mysql_error().$query;
$row = 0;
if (mysql_num_rows($rs)>0)
  while ($rec=mysql_fetch_array($rs)){
    $serials = get_serials($rec['id_loan']);
    $serial_text = null;
    if (count($serials) > 0)
        $serial_text = $serials[0];
    $serial_title = null;
    if (count($serials) > 1){
        $serial_title = implode(", ", $serials);
        $serial_text = '<a style="font-weight: bold; text-decoration: none; " >'.$serial_text.'</a>';
    }
    $row++;
    $class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
    echo '<tr '.$class .'>
        <td>'.$transaction_prefix .$rec['id_loan'].'</td>    
        <td>'.$rec['department_name'].'</td>    
        <td>'.$rec['category_name'].'</td>    
        <td title="'.$serial_title.'">'.$serial_text.'</td>
        <td>'.$rec['loan_date'].'</td>
        <td>'.$rec['return_date'].'</td>
        <td>'.$rec['contact_no'].'</td>
        <td align="center"><a href="./?mod=loan&sub=loan&act=view_issue&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
       </tr>';
    }
else
  echo '<tr class="normal"><td colspan=8  align=center>Data is not available!</td></tr>';
?>
</table>	
<br/>
<h3>Items was Returned</h3>
<table width="100%" class='userlist' cellpadding=2>
  <tr>
    <th>Transaction No</th>
    <th>Department</th>
    <th>Category</th>
    <th>Item Serial No.</th>   
    <th>Date Loaned</th>
    <th>Returned Date</th>    
    <th>Contact No</th>
    <th>View</th>
  </tr>
<?php

$query = "SELECT lr.id_loan, lr.id_category, date_format(lp.loan_date, '%d-%b-%Y %H:%i') as loan_date, 
            date_format(lp.return_date, '%d-%b-%Y %H:%i') as return_date, lo.name, lo.contact_no, 
            category_name, department_name, lr.status   
            FROM loan_request lr, loan_process lp, loan_out lo, category c  
            LEFT JOIN department d ON c.id_department = d.id_department 	   
            WHERE lr.requester ='$_id' AND (lr.status = 'RETURNED' OR lr.status = 'COMPLETED') AND 
            (lr.id_loan=lp.id_loan) AND (lr.id_loan=lo.id_loan) AND c.id_category = lr.id_category";
if ($dept > 0)
    $query .= ' AND c.id_department = ' . $dept;
$rs = mysql_query($query);
//echo mysql_error().$query;
$row = 0;
if (mysql_num_rows($rs)>0)
  while ($rec = mysql_fetch_array($rs)){
    $serials = get_serials($rec['id_loan']);
    $serial_text = null;
    if (count($serials) > 0)
        $serial_text = $serials[0];
    $serial_title = null;
    if (count($serials) > 1){
        $serial_title = implode(", ", $serials);
        $serial_text = '<a style="font-weight: bold; text-decoration: none; " >'.$serial_text.'</a>';
    }
    $row++;
    $class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
	$act = ($rec['status'] == COMPLETED) ? 'view_complete' : 'view_return';
    echo'<tr '. $class . '>
        <td>'.$transaction_prefix .$rec['id_loan'].'</td>    
        <td>'.$rec['department_name'].'</td>    
        <td>'.$rec['category_name'].'</td>    
        <td title="'.$serial_title.'">'.$serial_text.'</td>
        <td>'.$rec['loan_date'].'</td>
        <td>'.$rec['return_date'].'</td>
        <td>'.$rec['contact_no'].'</td>
        <td align="center"><a href="./?mod=loan&sub=loan&act='.$act.'&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
       </tr>';
      }
else
  echo '<tr class="normal"><td colspan=8 align=center>Data is not available!</td></tr>';
?>
</table>

