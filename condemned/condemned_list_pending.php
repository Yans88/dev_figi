<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_condemned_by_status(PENDING);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

$data = get_condemned_issue_by_status(PENDING, $_start, $_limit);

if (REQUIRE_CONDEMNED_APPROVAL)
    $caption = 'Condemned Items Waiting for Recommendation';
else
    $caption = 'Pending Condemned Items';

?>
<br/>
<h3><?php echo $caption?></h3>
<table width="100%" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30 valign="top">
  <th width=60>Trx. No</th><th width=120>Date of Issue</th>
  <th width=60>Quantity</th><th>Remark</th>
  <th width=65>Action</th>
</tr>

<?php
$counter = 0;
if ($total_item > 0) {
    foreach ($data as $rec) {
        $trxno = $transaction_prefix . str_pad($rec['id_issue'], '0', 3, STR_PAD_LEFT);
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;
        echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$trxno</td>
    <td align="center">$rec[issue_datetime]</td>
    <td align="center">$rec[quantity]</td>
    <td align="left">$rec[issue_remark]</td>
    <td align="center">
    <a href="./?mod=condemned&sub=condemned&act=view&id=$rec[id_issue]" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
DATA;
        if (REQUIRE_CONDEMNED_APPROVAL && (USERGROUP==GRPHOD)){
            echo ' <a href="./?mod=condemned&sub=condemned&act=recommend&id='.$rec['id_issue'].'" title="recommend"><img class="icon" src="images/ok.png" alt="recommend"></a>
                    <a href="./?mod=condemned&sub=condemned&act=reject&id='.$rec['id_issue'].'" )" title="reject"><img class="icon" src="images/no.png" alt="reject"></a>';
        } else if (!REQUIRE_CONDEMNED_APPROVAL && (USERGROUP==GRPADM)) {
            echo ' <a href="./?mod=condemned&sub=condemned&act=condemn&id='.$rec['id_issue'].'" title="manage"><img class="icon" src="images/process.png" alt="manage"></a> ';            
        }
    echo '</td></tr>';
  $counter++;
    } 
    echo '<tr ><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=condemned&sub=condemned&act=list&status='.strtolower(PENDING).'&page=');
    echo  '</td></tr>';

}else
	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';
?>
</table>

