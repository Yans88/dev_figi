<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
if (!empty($_SESSION['LOG_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['LOG_ORDER_STATUS']);
else
    $order_status = array('log_time' => 'asc', 
                          'log_activity' => 'asc');

$_id = isset($_GET['id']) ? $_GET['id'] : 1;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'log_time';

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = count_user_log($_id);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) 
    $_page = 1;
if ($_page > 0)
	$_start = ($_page-1) * $_limit;

$sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;
$buffer = ob_get_contents();
ob_clean();
$_SESSION['LOG_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
?>
<?php
  
$user_info = get_user($_id);
$row_class = ' class="sort_'.$sort_order.'"';
$data = get_user_logs($_id, $sort_order, $_start, $_limit);
$order_link = './?mod=user&sub=user&act=log&page='.$_page.'&id='.$_id.'&ordby=';

?>
<h4 style="color: #fff">Activity Logs for <?php echo $user_info['full_name']?></h4>
<table width="600" cellpadding=2 cellspacing=1 class="userlist" >
<tr height="20">
	<th width="140px" <?php echo ($_orderby == 'log_time') ? $row_class : null ?> >
            <a href="<?php echo $order_link ?>log_time">Log Time</a></th>
	<th width="80px" <?php echo ($_orderby == 'user_name') ? $row_class : null ?>>
            <a href="<?php echo $order_link ?>log_activity">Activity</a></th>
	<th>Description</a></th>
</tr>
<?php
	
$counter = 0;
if ($total_item>0) {
    foreach($data as $rec){
        $class = ($counter % 2 == 0) ? 'class="alt"' : 'class="normal"';
        echo <<<TEXT
        <tr $class >
            <td align="center">&nbsp;$rec[log_time]</td>
            <td align="center">&nbsp;$rec[log_activity]</td>
            <td>&nbsp;$rec[log_description]</td>
            </tr>
TEXT;

        $counter++;
    }
    echo '<tr ><td colspan=3 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=user&act=log&id='.$_id.'&page=');
    echo  '</td></tr>';
} else {
    echo '<tr ><td colspan=3 align="center">Data is not available!</td></tr>';

}
echo '</table><br/>';
?>

