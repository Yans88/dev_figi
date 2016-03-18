<?php
if (!defined('FIGIPASS')) exit;

function count_fault_request_for_user($user)
{
    $result = 0;
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT count(fr.id_fault) 
                FROM fault_report fr 
                WHERE report_user = '$user'";
    $rs = mysql_query($query);
    if ($rs && (mysql_num_rows($rs)>0)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_fault_request_for_user($user, $start = 0, $limit = RECORD_PER_PAGE){
	$result = array();
	$dept = defined('USERDEPT') ? USERDEPT : 0;
	$query  = "SELECT fr.id_fault, date_format(fault_date, '%d-%b-%Y %H:%i') as fault_date,  
                 date_format(report_date, '%d-%b-%Y %H:%i') as report_date, location_name fault_location, 
                 user.full_name, category_name, fault_description, fault_status,
                 date_format(rect.rectify_date, '%d-%b-%Y %H:%i') as rectify_date, 
                 date_format(rect.completion_date, '%d-%b-%Y %H:%i') as completion_date                  
                 FROM fault_report fr 
                 LEFT JOIN user ON report_user = user.id_user 
                 LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 
                 LEFT JOIN fault_rectification rect ON rect.id_fault = fr.id_fault 
                 LEFT JOIN location loc ON loc.id_location = fr.id_location 
                 WHERE report_user = '$user' ";
	$query .= " ORDER BY report_date DESC LIMIT $start, $limit";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	$i = 0;
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;    
	return $result;
}

$id_user = USERID;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_fault_request_for_user($id_user);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

$data = get_fault_request_for_user($id_user, $_start, $_limit);
$transaction_prefix = TRX_PREFIX_FAULT;

?>
<h4><br/>Fault Reporting Record</h4>

<?php
$counter = 0;
if ($total_item > 0) {
    echo <<<TEXT
<table cellpadding=2 cellspacing=1 class="fault_table portal" >
<tr height=30 valign="top" align="center">
  <th width=25>No</th><th width=110>Date of Report</th>
  <th width=110>Fault Date</th>
  <th >Category</th><th>Description</th>
  <th width=80>Status</th><th width=30>Action</th>
</tr>
TEXT;
    foreach ($data as $rec) {
        if (strlen($rec['fault_description'])>35)
            $desc = substr($rec['fault_description'], 0, 35) . ' ...';
        else
            $desc = $rec['fault_description'];
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        $status = ($rec['fault_status'] == FAULT_PROGRESS) ? 'IN PROGRESS' : $rec['fault_status'];
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_fault]</td>
    <td align="center" nowrap>$rec[report_date]</td>
    <td align="center" nowrap>$rec[fault_date]</td>
    <td>$rec[category_name]</td>
    <td>$desc</td>
    <td align="center">$status</td>
    <td align="center">
    <a href="./?mod=portal&sub=history&act=view&portal=fault&id=$rec[id_fault]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        echo '</td></tr>';
        $counter++;
    } 
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=fault&act=history&status='.strtolower(FAULT_NOTIFIED).'&page=');
    echo  '</td></tr></table>';
} else
	echo '<h4 class="error">Data is not available!</h4>&nbsp;';
?>
