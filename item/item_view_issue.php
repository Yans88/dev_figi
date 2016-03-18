<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_serialno = isset($_POST['serial_no']) ? $_POST['serial_no'] : null;
$_dept = isset($_POST['id_department']) ? $_POST['id_department'] : null;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : null;
$_msg = null;
$today = date('j-M-Y H:i');

$issue = get_item_issue($_id);
$items = get_item_issue_list($_id);
$item_list = null;
/*
$no = 0;
foreach($items as $rec){
    $no++;
    $item_list .= $no . '. ' . $rec['asset_no'] . ' ('.$rec['serial_no'].')<br/>';

}
*/
$item_list = issued_item_list($items);

$issue['item_list'] = $item_list;

$signs = get_item_issuance_signatures($_id);
$issue = @array_merge($issue, $signs);
$returned = ($issue['status'] == 'RETURNED');

if ($returned){ 
    $returns = get_item_issue_return($_id);
    $returns = @array_merge($returns, $signs);
}


?>

<div style="width: 800px; text-align: left; ">
<h4>Departmental Item Issuance (View<?php if ($returned) echo ' - Returned';?>)</h4>
<a class="button" href="./?mod=item&act=issue">Create Item Issuance</a>
<a class="button" href="./?mod=item&act=issue_history">Issuance History</a>
<!--a class="button" href="./?mod=item&act=issue_return">Return Item</a-->
<form method="post">
<input type="hidden" name="quantity" id="quantity" value="<?php echo $request['quantity']?>">
<input type="hidden" name="items" id="items" value="">
<input type="hidden" name="iditems" id="iditems" value="">
<table  class="loan_table" cellpadding=2 cellspacing=1>
<tr><td ><?php view_issue($issue); ?></td></tr>
<tr><td ><?php view_issued_to($issue); ?></td></tr>
<tr><td ><?php view_issue_signature($issue); ?></td></tr>
<?php
if ($i_can_update && !$returned){
?>
<tr>
    <td align="right" valign="middle">
        <button type="button" onclick=" return_item()">Return</button>
    </td>
</tr>
<?php
}
if ($returned){
?>
<tr><td ><?php view_issue_return($returns); ?></td></tr>
<?php
}
?>
</table>
</form>
<br/><br/>
<script>
function return_item()
{
    location.href = "./?mod=item&act=issue_return&id=<?php echo $issue['id_issue']?>";
}
</script>

</div>
