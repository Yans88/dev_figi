 <?php

if (!defined('FIGIPASS')) exit;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_status = isset($_GET['status']) ? $_GET['status'] : null;
$_user = USERID;
$_msg = null;

function get_serials($id = 0)
{   
    $data = array();
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

$reccnt = 0;
$single = true;
//echo '<h3><br/>Equipment Loan Record</h3>';
echo '<br>';
switch ($_status){
case 'PENDING' : $reccnt += show_pending($_user, $_page, true); break;
case 'LOANED' : $reccnt += show_onloan($_user, $_page, true); break;
case 'RETURNED' : $reccnt += show_returned($_user, $_page, true); break;
case 'REJECTED' : $reccnt += show_returned($_user, $_page, true); break;
default:
    $reccnt += show_onloan($_user, $_page);
    $reccnt += show_pending($_user, $_page);
    $reccnt += show_returned($_user, $_page);
    $reccnt += show_rejected($_user, $_page);
    $single = false;
}
if ($reccnt == 0)
    echo '<h3 class="error">Data is not available!</h3>&nbsp;';
if ($single)
    echo '<div><a href="./?mod=portal&portal=loan&sub=history">Back to Main History</a></div>&nbsp;';

function show_onloan($_user, $_page, $_single = false)
{
    $transaction_prefix = TRX_PREFIX_LOAN;
    $_limit = ($_single) ? RECORD_PER_PAGE : PORTAL_RECORD_PER_PAGE;
    $_start = 0;
    $query = "SELECT COUNT(lr.id_loan) 
                FROM loan_request lr, loan_out lo, loan_process lp, category c  
                LEFT JOIN department d ON c.id_department = d.id_department 	  
                WHERE lr.requester ='$_user' AND lr.id_loan=lp.id_loan  AND lr.id_loan=lo.id_loan 
                AND c.id_category = lr.id_category 
                AND c.category_type = 'EQUIPMENT' 
                AND lr.status = 'LOANED' ";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $total_item = $rec[0];

    if ($total_item > 0) {
        $total_page = ceil($total_item/$_limit);
        if ($_page > $total_page) $_page = 1;
        if ($_page > 0)	$_start = ($_page-1) * $_limit;
                    
        $query = "SELECT lr.id_loan, lr.id_category, date_format(lp.issue_date, '%d-%b-%Y %H:%i') as loan_date, 
                    date_format(lo.return_date, '%d-%b-%Y %H:%i') as return_date, lo.name, lo.contact_no,  
                    category_name, department_name, quantity  	  
                    FROM loan_request lr, loan_out lo, loan_process lp, category c  
                    LEFT JOIN department d ON c.id_department = d.id_department 	  
                    WHERE lr.requester ='$_user' AND lr.id_loan=lp.id_loan  AND lr.id_loan=lo.id_loan 
                    AND c.id_category = lr.id_category 
                    AND c.category_type = 'EQUIPMENT' 
                    AND lr.status = 'LOANED' 
                    ORDER BY loan_date ASC 
                    LIMIT $_start, $_limit ";
        $rs = mysql_query($query);
        $row = 0;
        echo <<<TEXT2
<h4>Items still On Loan</h4>
<table width="100%" class='item-list' cellpadding=2  cellspacing=1>
  <tr>
    <th width=60>Trans.No</th>
    <th width=100>Department</th>
    <th width=150>Category</th>
    <th width=60>Quantity</th>
    <th>Item Serial No.</th>
    <th>Date Loaned</th>
    <th>Date to be returned</th>    
    <th>View</th>
  </tr>
TEXT2;
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
                    <td align="center">'.$rec['quantity'].'</td>
                    <td title="'.$serial_title.'">'.$serial_text.'</td>
                    <td>'.$rec['loan_date'].'</td>
                    <td>'.$rec['return_date'].'</td>
                    <td align="center"><a href="./?mod=portal&portal=loan&sub=history&act=view_issue&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
                   </tr>';
        }
        if ($total_page > 1) {
            echo '<tr ><td colspan=7 class="pagination">';
            if (!empty($_GET['status']))
                echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=loan&act=history&status=LOANED&page=');
            else
                echo '<div style="text-align: right;"><a href="./?mod=portal&sub=history&portal=loan&status=LOANED&page=1">more...</a> &nbsp;</div>';
            echo  '</td></tr>';
        }
        echo '</table><br/>';
    }
    return $total_item;
} // show_onloan

function show_rejected($_user, $_page, $_single = false)
{
    $transaction_prefix = TRX_PREFIX_LOAN;
    $_limit = ($_single) ? RECORD_PER_PAGE : PORTAL_RECORD_PER_PAGE;
    $_start = 0;
    $query = "SELECT COUNT(lr.id_loan) 
                FROM loan_request lr, loan_reject lj, category c  
                LEFT JOIN department d ON c.id_department = d.id_department 	  
                WHERE lr.requester ='$_user' AND lr.id_loan=lj.id_loan  
                AND c.id_category = lr.id_category 
                AND c.category_type = 'EQUIPMENT' 
                AND lr.status = 'REJECTED' ";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $total_item = $rec[0];

    if ($total_item > 0) {
        $total_page = ceil($total_item/$_limit);
        if ($_page > $total_page) $_page = 1;
        if ($_page > 0)	$_start = ($_page-1) * $_limit;
                    
        $query = "SELECT lr.id_loan, lr.id_category, date_format(lr.start_loan, '%d-%b-%Y %H:%i') as loan_date, 
                    date_format(lj.reject_date, '%d-%b-%Y %H:%i') as reject_date,   
                    category_name, department_name, quantity  	  
                    FROM loan_request lr, loan_reject lj, category c   
                    LEFT JOIN department d ON c.id_department = d.id_department 	  
                    WHERE lr.requester ='$_user' AND lr.id_loan=lj.id_loan 
                    AND c.id_category = lr.id_category 
                    AND c.category_type = 'EQUIPMENT' 
                    AND lr.status = 'REJECTED' 
                    ORDER BY loan_date ASC 
                    LIMIT $_start, $_limit ";
        $rs = mysql_query($query);
        //echo mysql_error();
        $row = 0;
        echo <<<TEXT2
<h4>Rejected Request</h4>
<table width="100%" class='item-list' cellpadding=2  cellspacing=1>
  <tr>
    <th width=60>Trans.No</th>
    <th width=100>Department</th>
    <th width=150>Category</th>
    <th width=60>Quantity</th>
    <th>Date Rejected</th>
    <th>View</th>
  </tr>
TEXT2;
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
                    <td align="center">'.$rec['quantity'].'</td>
                    <td>'.$rec['reject_date'].'</td>
                    <td align="center"><a href="./?mod=portal&portal=loan&sub=history&act=view&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
                   </tr>';
        }
        if ($total_page > 1) {
            echo '<tr ><td colspan=7 class="pagination">';
            if (!empty($_GET['status']))
                echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=loan&act=history&status=REJECTED&page=');
            else
                echo '<div style="text-align: right;"><a href="./?mod=portal&sub=history&portal=loan&status=REJECTED&page=1">more...</a> &nbsp;</div>';
            echo  '</td></tr>';
        }
        echo '</table><br/>';
    }
    return $total_item;
} // show_onloan

function show_pending($_user, $_page, $_single = false)
{
    $transaction_prefix = TRX_PREFIX_LOAN;
    $_limit = ($_single) ? RECORD_PER_PAGE : PORTAL_RECORD_PER_PAGE;
    $_start = 0;
    $query = "SELECT COUNT(lr.id_loan) 
                FROM loan_request lr, user u, category c  
                WHERE lr.requester ='$_user' AND lr.requester = u.id_user 
                AND c.category_type = 'EQUIPMENT' 
                AND c.id_category = lr.id_category 
                AND lr.status = 'PENDING' ";
                // LEFT JOIN department d ON c.id_department = d.id_department 
    $rs = mysql_query($query);
    
    $rec = mysql_fetch_row($rs);
    $total_item = $rec[0];
    
    if ($total_item > 0) {
        $total_page = ceil($total_item/$_limit);
        if ($_page > $total_page) $_page = 1;
        if ($_page > 0)	$_start = ($_page-1) * $_limit;
        $query = "SELECT lr.id_loan, lr.id_category, date_format(lr.start_loan, '%d-%b-%Y %H:%i') as start_loan, 
                    date_format(lr.end_loan, '%d-%b-%Y %H:%i') as end_loan, 
                    date_format(lr.request_date, '%d-%b-%Y %H:%i') as request_date, 
                    category_name, department_name, lr.status, u.full_name name   	  
                    FROM loan_request lr, user u, category c  
                    LEFT JOIN department d ON c.id_department = d.id_department 
                    WHERE lr.requester ='$_user' AND lr.requester = u.id_user 
                    AND c.category_type = 'EQUIPMENT' 
                    AND c.id_category = lr.id_category AND lr.status = 'PENDING' 
                    ORDER BY request_date ASC 
                    LIMIT $_start, $_limit ";
        $rs = mysql_query($query);
        $row = 0;
        echo <<<TEXT1
<h4>Pending Request</h4>
<table width="100%" class='item-list' cellpadding=2  cellspacing=1>
  <tr>
    <th width=60>Trans.No</th>
    <th width=100>Department</th>
    <th width=150>Category</th>
    <th>Request Date</th>
    <th>Expected Loan Date</th>    
    <th>View</th>
  </tr>
TEXT1;
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
                <td>'.$rec['request_date'].'</td>
                <td>'.$rec['start_loan'].' - '.$rec['end_loan'].'</td>
                <td align="center"><a href="./?mod=portal&portal=loan&sub=history&act=view&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
               </tr>';
        }
        if ($total_page > 1) {
            echo '<tr ><td colspan=7 class="pagination">';
            if (!empty($_GET['status']))
                echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=loan&act=history&status=PENDING&page=');
            else
                echo '<div style="text-align: right;"><a href="./?mod=portal&sub=history&portal=loan&status=PENDING&page=1">more...</a> &nbsp;</div>';
            echo  '</td></tr>';
        }
        echo '</table><br/>';
    }
    return $total_item;
} // show_pending

function show_returned($_user, $_page, $_single = false)
{
    $transaction_prefix = TRX_PREFIX_LOAN;
    $_limit = ($_single) ? RECORD_PER_PAGE : PORTAL_RECORD_PER_PAGE;
    $_start = 0;
    $query = "SELECT COUNT(lr.id_loan) 
                FROM loan_request lr, loan_process lp, loan_out lo, category c  
                LEFT JOIN department d ON c.id_department = d.id_department 	   
                WHERE lr.requester ='$_user' AND c.category_type = 'EQUIPMENT' AND 
                (lr.status = 'RETURNED' OR lr.status = 'COMPLETED') AND 
                (lr.id_loan=lp.id_loan) AND (lr.id_loan=lo.id_loan) AND 
                c.id_category = lr.id_category";
    $rs = mysql_query($query);
    $rec = mysql_fetch_row($rs);
    $total_item = $rec[0];
    
    if ($total_item > 0) {
        $total_page = ceil($total_item/$_limit);
        if ($_page > $total_page) $_page = 1;
        if ($_page > 0)	$_start = ($_page-1) * $_limit;

        $query = "SELECT lr.id_loan, lr.id_category, date_format(lp.loan_date, '%d-%b-%Y %H:%i') as loan_date, 
                date_format(lp.return_date, '%d-%b-%Y %H:%i') as return_date, lo.name, lo.contact_no, 
                category_name, department_name, lr.status, quantity    
                FROM loan_request lr, loan_process lp, loan_out lo, category c  
                LEFT JOIN department d ON c.id_department = d.id_department 	   
                WHERE lr.requester ='$_user' AND c.category_type = 'EQUIPMENT' AND 
                (lr.status = 'RETURNED' OR lr.status = 'COMPLETED') AND 
                (lr.id_loan=lp.id_loan) AND (lr.id_loan=lo.id_loan) AND c.id_category = lr.id_category
                ORDER BY request_date ASC 
                LIMIT $_start, $_limit ";
        $rs = mysql_query($query);
        $row = 0;
        echo <<<TEXT3
<h4>Items was Returned</h4>
<table width="100%" class='item-list' cellpadding=2  cellspacing=1>
  <tr>
    <th width=60>Trans.No</th>
    <th width=100>Department</th>
    <th width=150>Category</th>
    <th width=60 align="center">Quantity</th>
    <th>Item Serial No.</th>   
    <th>Date Loaned</th>
    <th>Returned Date</th>    
    <th>View</th>
  </tr>
TEXT3;
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
                <td align="center">'.$rec['quantity'].'</td>
                <td title="'.$serial_title.'">'.$serial_text.'</td>
                <td>'.$rec['loan_date'].'</td>
                <td>'.$rec['return_date'].'</td>
                <td align="center"><a href="./?mod=portal&portal=loan&sub=history&act=history&act='.$act.'&id='.$rec['id_loan'].'"><img class="icon" src="images/loupe.png"></a></td>
               </tr>';
        }
        if ($total_page > 1) {
            echo '<tr ><td colspan=7 class="pagination">';
            if (!empty($_GET['status']))
                echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=loan&act=history&status=RETURNED&page=');
            else
                echo '<div style="text-align: right;"><a href="./?mod=portal&sub=history&portal=loan&status=RETURNED&page=1">more...</a> &nbsp;</div>';
            echo  '</td></tr>';
        }
        echo '</table><br/>';
    }
    return $total_item;
} // show_returned

?>
