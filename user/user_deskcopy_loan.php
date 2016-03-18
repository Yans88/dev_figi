<?php

if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$_username = null;
$_contact = null;
$dept = USERDEPT;
if ($_id == 0){
    include 'user/user_loan_search.php';
    return;
}
$query = 'SELECT full_name, contact_no FROM user WHERE id_user = ' .$_id;
$res = mysql_query($query);
if (mysql_num_rows($res) > 0) {
    $rec = mysql_fetch_row($res);
    $_username = $rec[0];
    $_contact = $rec[1];
}

?>

<h2>Deskcopy Loan Record of <?php echo $_username?></h2>
<a href="./?mod=user&sub=user&act=loan_search">search for other user</a>	
<h3>Items still On Loan</h3>
<table width="100%" class='userlist' cellpadding=2 cellspacing=1>
  <tr>
    <th width=60>Trans. No</th>
    <th width=100>Serial No</th>
    <th width=80>ISBN</th>
    <th >Title</th>
    <th width=100>Author</th>
    <th width=120>Date Loaned</th>
    <th width=100>Contact No</th>
  </tr>
<?php  

$query = "SELECT dcl.*, dcli.*, dci.*, dct.*, a.author_name 
            FROM deskcopy_loan dcl 
            LEFT JOIN deskcopy_loan_item dcli ON dcl.id_loan = dcli.id_loan 
            LEFT JOIN deskcopy_item dci ON dci.id_item = dcli.id_item 
            LEFT JOIN deskcopy_title dct ON dci.id_title = dct.id_title 
            LEFT JOIN deskcopy_author a ON a.id_author = dct.id_author 
            WHERE dci.status = 'On Loan' AND dcli.return_date IS NULL ";
if ($dept > 0)
    $query .= ' AND dct.id_department = ' . $dept;
$query .= ' ORDER BY loan_start DESC';
$rs = mysql_query($query);
//echo mysql_error().$query;
$row = 0;
if (mysql_num_rows($rs)>0)
  while ($rec=mysql_fetch_array($rs)){
    $row++;
    $class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
    echo '<tr '.$class .'>
        <td>DCLN'.$rec['id_loan'].'</td>    
        <td>'.$rec['serial_no'].'</td>    
        <td>'.$rec['isbn'].'</td>    
        <td>'.$rec['title'].'</td>    
        <td>'.$rec['author_name'].'</td>    
        <td>'.$rec['loan_start'].'</td>
        <td>'.$_contact.'</td>
       </tr>';
       // <td align="center"><a href="./?mod=loan&sub=loan&act=view_issue&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
    }
else
  echo '<tr class="normal"><td colspan=8  align=center>Data is not available!</td></tr>';
?>
</table>	
<br/>
<h3>Items was Returned</h3>
<table width="100%" class='userlist' cellpadding=2 cellspacing=1>
  <tr>
    <th width=60>Trans. No</th>
    <th width=100>Serial No</th>
    <th width=80>ISBN</th>
    <th >Title</th>
    <th width=100>Author</th>
    <th width=120>Date Loaned</th>
    <th width=120>Date Returned</th>
    <th width=100>Contact No</th>
  </tr>
<?php

$query = "SELECT dcl.*, dcli.*, dci.*, dct.*, a.author_name 
            FROM deskcopy_loan dcl 
            LEFT JOIN deskcopy_loan_item dcli ON dcl.id_loan = dcli.id_loan 
            LEFT JOIN deskcopy_item dci ON dci.id_item = dcli.id_item 
            LEFT JOIN deskcopy_title dct ON dci.id_title = dct.id_title 
            LEFT JOIN deskcopy_author a ON a.id_author = dct.id_author 
            WHERE dci.status != 'On Loan' AND dcli.return_date IS NOT NULL  ";
if ($dept > 0)
    $query .= ' AND dct.id_department = ' . $dept;
$query .= ' ORDER BY loan_start DESC';
$rs = mysql_query($query);
//echo mysql_error().$query;
$row = 0;
if (mysql_num_rows($rs)>0)
  while ($rec = mysql_fetch_array($rs)){
    $row++;
    $class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
    echo'<tr '. $class . '>
        <td>DCLN'.$rec['id_loan'].'</td>    
        <td>'.$rec['serial_no'].'</td>    
        <td>'.$rec['isbn'].'</td>    
        <td>'.$rec['title'].'</td>    
        <td>'.$rec['author_name'].'</td>    
        <td>'.$rec['loan_start'].'</td>
        <td>'.$rec['return_date'].'</td>
        <td>'.$_contact.'</td>
       </tr>';
      }
else
  echo '<tr class="normal"><td colspan=8 align=center>Data is not available!</td></tr>';
?>
</table>

