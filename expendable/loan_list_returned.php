<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){
    export_request_status(RETURNED);
	}

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_request_by_status(RETURNED);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0) $_start = ($_page-1) * $_limit;

$data = get_request_by_status(RETURNED, $_start, $_limit, 'return_date', 'DESC');

?>


<br/>
<h3>Loaned Items Pending Acknowlegment</h3>
<table cellpadding=2 cellspacing=1 class="loan_table item-list" >
<tr height=30 valign="top">
  <th width=35>No</th><th>Date of Return</th>
  <th>Requestor</th><th>Loan Start Date</th>
  <th>Loan End Date</th><th>Category</th>
  <th width=50>Action</th>
</tr>

<?php
$counter = 0;
if ($total_item > 0){
    foreach ($data as $rec) {
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
	<tr $_class>
	<td align="center">$transaction_prefix$rec[id_loan]</td>
	<td align="center">$rec[return_date]</td>
	<td>$rec[requester]</td>
	<td align="center">$rec[start_loan]</td>
	<td align="center">$rec[end_loan]</td>
	<td>$rec[category_name]</td>
	<td align="center">
    <a href="./?mod=loan&sub=loan&act=view_return&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        if ((USERGROUP == GRPHOD) || $i_can_delete){
            echo ' <a href="./?mod=loan&sub=loan&act=acknowledge&id='.$rec['id_loan'].'" title="acknowledge" ><img class="icon" src="images/notes.png" alt="acknowledge"></a>';
        }

        echo '</td></tr>';
        $counter++;
    }
    echo '<tr ><td colspan=7 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=loan&sub=loan&act=list&status='.strtolower(RETURNED).'&page=');
    echo  '<div class="exportdiv"><a href="./?mod=loan&sub=loan&act=list&status=returned&do=export" class="button">Export Data</a></div></td></tr>';
} else
	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';

?>
</table>
