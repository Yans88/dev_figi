<?php

if (!defined('FIGIPASS')) exit;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_status = isset($_GET['status']) ? $_GET['status'] : null;

$_user = USERID;
$_msg = null;
$reccnt = 0;
echo '<h4><br/>Deskcopy Loan Record</h4>';
switch($_status){
    case 'ONLOAN' : $reccnt += show_onloan($_user, $_page); break;
    case 'RETURNED' :$reccnt +=  show_returned($_user, $_page); break;
    default : 
        $reccnt += show_onloan($_user, $_page); 
        $reccnt += show_returned($_user, $_page);    
}
if ($reccnt == 0)
    echo '<h4 class="error">Data is not available!</h4>&nbsp;';
    
/* sub-routines */
function show_onloan($user, $_page)
{
    $transaction_prefix = TRX_PREFIX_DESKCOPY;
    $_limit = PORTAL_RECORD_PER_PAGE;
    $_start = 0;
    $query = "SELECT COUNT(dcl.id_loan) 
                FROM deskcopy_loan dcl 
                LEFT JOIN deskcopy_loan_item dcli ON dcl.id_loan = dcli.id_loan 
                LEFT JOIN deskcopy_item dci ON dci.id_item = dcli.id_item  
                WHERE dcl.id_user='$user' AND dci.status = 'On Loan' AND dcli.return_date IS NULL ";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $total_item = $rec[0];
    
    if ($total_item > 0) {
        $total_page = ceil($total_item/$_limit);
        if ($_page > $total_page) $_page = 1;
        if ($_page > 0)	$_start = ($_page-1) * $_limit;

        $query = "SELECT dcl.*, dcli.*, dci.*, dct.*, a.author_name 
                    FROM deskcopy_loan dcl 
                    LEFT JOIN deskcopy_loan_item dcli ON dcl.id_loan = dcli.id_loan 
                    LEFT JOIN deskcopy_item dci ON dci.id_item = dcli.id_item 
                    LEFT JOIN deskcopy_title dct ON dci.id_title = dct.id_title 
                    LEFT JOIN deskcopy_author a ON a.id_author = dct.id_author 
                    WHERE dcl.id_user='$user' AND dci.status = 'On Loan' AND dcli.return_date IS NULL 
                    ORDER BY loan_start DESC ";
        $rs = mysql_query($query);
        $row = 0;
        echo <<<TEXT1
<h3>Items still On Loan</h3>
<table width="100%" class='item-list' cellpadding=2 cellspacing=1>
  <tr>
    <th width=60>Trans. No</th>
    <th width=100>Serial No</th>
    <th width=80>ISBN</th>
    <th >Title</th>
    <th width=100>Author</th>
    <th width=120>Date Loaned</th>
  </tr>
TEXT1;

        while ($rec=mysql_fetch_array($rs)){
            $row++;
            $class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
            echo '<tr '.$class .'>
                <td>'.$transaction_prefix.$rec['id_loan'].'</td>    
                <td>'.$rec['serial_no'].'</td>    
                <td>'.$rec['isbn'].'</td>    
                <td>'.$rec['title'].'</td>    
                <td>'.$rec['author_name'].'</td>    
                <td>'.$rec['loan_start'].'</td>
               </tr>';
        }
        if ($total_page > 1) {
            echo '<tr ><td colspan=7 class="pagination">';
            if (!empty($_GET['status']))
                echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=deskcopy&act=history&status=ONLOAN&page=');
            else
                echo '<div style="text-align: right;"><a href="./?mod=portal&sub=history&portal=deskcopy&status=ONLOAN&page='.($_page+1).'">more...</a> &nbsp;</div>';
            echo  '</td></tr>';
        }
        echo '</table><br/>';
    }
    return $total_item;
} // show_onloan

function show_returned($user, $_page)
{
    $transaction_prefix = TRX_PREFIX_DESKCOPY;
    $_limit = PORTAL_RECORD_PER_PAGE;
    $_start = 0;
    $query = "SELECT COUNT(*) 
                FROM deskcopy_loan dcl 
                LEFT JOIN deskcopy_loan_item dcli ON dcl.id_loan = dcli.id_loan 
                LEFT JOIN deskcopy_item dci ON dci.id_item = dcli.id_item  
                WHERE dcl.id_user='$user' AND dci.status != 'On Loan' AND dcli.return_date IS NOT NULL ";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $total_item = $rec[0];
    
    if ($total_item > 0) {
        $total_page = ceil($total_item/$_limit);
        if ($_page > $total_page) $_page = 1;
        if ($_page > 0)	$_start = ($_page-1) * $_limit;

        $query = "SELECT dcl.*, dcli.*, dci.*, dct.*, a.author_name 
                    FROM deskcopy_loan dcl 
                    LEFT JOIN deskcopy_loan_item dcli ON dcl.id_loan = dcli.id_loan 
                    LEFT JOIN deskcopy_item dci ON dci.id_item = dcli.id_item 
                    LEFT JOIN deskcopy_title dct ON dci.id_title = dct.id_title 
                    LEFT JOIN deskcopy_author a ON a.id_author = dct.id_author 
                    WHERE dcl.id_user='$user' AND dci.status != 'On Loan' AND dcli.return_date IS NOT NULL 
                    ORDER BY loan_start DESC 
                    LIMIT $_start, $_limit ";
        $rs = mysql_query($query);
        $row = 0;
        echo <<<TEXT2
<h3>Items was Returned</h3>
<table width="100%" class='item-list' cellpadding=2 cellspacing=1>
  <tr>
    <th width=60>Trans. No</th>
    <th width=100>Serial No</th>
    <th width=80>ISBN</th>
    <th >Title</th>
    <th width=100>Author</th>
    <th width=120>Date Loaned</th>
    <th width=120>Date Returned</th>
  </tr>
TEXT2;

        while ($rec = mysql_fetch_array($rs)){
            $row++;
            $class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
            echo'<tr '. $class . '>
                <td>'.$transaction_prefix.$rec['id_loan'].'</td>    
                <td>'.$rec['serial_no'].'</td>    
                <td>'.$rec['isbn'].'</td>    
                <td>'.$rec['title'].'</td>    
                <td>'.$rec['author_name'].'</td>    
                <td>'.$rec['loan_start'].'</td>
                <td>'.$rec['return_date'].'</td>
               </tr>';
        }
        if ($total_page > 1) {
            echo '<tr ><td colspan=7 class="pagination">';
            if (!empty($_GET['status']))
                echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=deskcopy&act=history&status=RETURNED&page=');
            else
                echo '<div style="text-align: right;"><a href="./?mod=portal&sub=history&portal=deskcopy&status=RETURNED&page='.($_page+1).'">more...</a> &nbsp;</div>';
            echo  '</td></tr>';
        }
        echo '</table><br/>';
    }
    return $total_item;
} // show_returned

?>