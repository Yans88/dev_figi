<?php

if (!defined('FIGIPASS')) exit;


if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('report_date' => 'desc', 'lead_time' => 'asc'	);


$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'report_date';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_do = isset($_GET['do']) ? $_GET['do'] : null;



if ($_do == 'export'){

    export_fault_request_status(FAULT_COMPLETED);

}


$_limit = RECORD_PER_PAGE;
$_start = 0;


$total_item = count_get_fault_data(FAULT_COMPLETED);

$total_page = ceil($total_item/$_limit);

if ($_page > $total_page) $_page = 1;

if ($_page > 0)	$_start = ($_page-1) * $_limit;



$sort_order = $order_status[$_orderby];

if ($_changeorder)

    $sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';

$order_status[$_orderby] = $sort_order;

$buffer = ob_get_contents();

ob_clean();

$_SESSION['ITEM_ORDER_STATUS'] = serialize($order_status);

echo $buffer;

$row_class = ' class="sort_'.$sort_order.'"';

$order_link = './?mod=report&sub=fault&act=view&term=frequency&by=leadtime&chgord=1&page='.$_page.'&ordby=';
/*ORDER LINK*/



?>

<h3>Report Fault already Completed</h3>
<br />
<table cellpadding=2 cellspacing=1 class="fault_table middle" style="width:99%">

<tr height=30 valign="top" align="center">

  <th width=25>No</th><th width=110>Date of Report</th>

  <th>Reporter</th><th width=110>Rectification Date</th><th width=110>Completion Date</th>

  <th>Category</th><th>Remark</th><th <?php echo ($_orderby == 'lead_time') ? $row_class : null ?>><a href="<?php echo $order_link ?>lead_time">Lead Time (Day)</a></th><th width=50>Action</th>

</tr>



<?php
//echo $_orderby. " " . $sort_order. " " .$_start . " ".$_limit;
$data = get_fault_data(FAULT_COMPLETED, $_orderby, $sort_order, $_start, $_limit);

$counter = 0;

if ($total_item > 0) {

    foreach ($data as $rec) {

        $desc = substr($rec['completion_remark'], 0, 35) . ' ...';

        $_class = ($counter % 2 == 0) ? 'class="alt"':null;

        echo <<<DATA

    <tr $_class valign='top'>

    <td align="center">$rec[id_fault]</td>

    <td align="center" nowrap>$rec[report_date]</td>

    <td>$rec[full_name]</td>

    <td align="center" nowrap>$rec[rectify_date]</td>

    <td align="center" nowrap>$rec[completion_date]</td>

    <td>$rec[category_name]</td>

    <td>$desc</td>
	
	<td width=50 align=center>$rec[lead_time]</td>

    <td align="center">

    <a href="./?mod=fault&sub=fault&act=view_complete&id=$rec[id_fault]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 

DATA;

    echo '</td></tr>';

  $counter++;

    } 

    echo '<tr ><td colspan=9 class="pagination">';

    echo make_paging($_page, $total_page, './?mod=report&sub=fault&act=view&term=frequency&by=leadtime&page=');

    echo  '<div class="exportdiv"><a href="./?mod=fault&act=list&status=completed&do=export" class="button">Export Data</a></div></td></tr>';



}else

	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';

?>

</table>



