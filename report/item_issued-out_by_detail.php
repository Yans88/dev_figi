<?php
if (!defined('FIGIPASS')) exit;

$dept = defined('USERDEPT') ? USERDEPT : 0;
$id_item = isset($_GET['id']) ? $_GET['id'] : 0;
$_id = get_item_issue_by_item($id_item);
$issue = get_item_issue($_id);
$item = get_item($id_item);

?>
<div class="clear"></div>
<h2>Item Issue-Out Detail</h2>
<table cellpadding=2 cellspacing=1 class="item_issuance" >
<tr><td >
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Item Info
            <div class="foldtoggle"><a id="btn_item_info" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="item_info">
       <tr valign="top" align="left" class="normal">
        <td align="left" width=100>Category</td>
        <td align="left"><?php echo $issue['src_category_name']?></td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left">Asset No</td>
        <td align="left" colspan=3><?php echo $item['asset_no']?></td>
        </tr>
       <tr valign="top" align="left" class="normal">
        <td align="left">Serial No</td>
        <td align="left" colspan=3><?php echo $item['serial_no']?></td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left">Model No</td>
        <td align="left" colspan=3><?php echo $item['model_no']?></td>
        </tr>
       <tr valign="top" align="left" class="normal">
        <td align="left">Brand</td>
        <td align="left" colspan=3><?php echo $item['brand_name']?></td>
        </tr>
    </tbody>
    </table>
</td></tr>
<tr><td ><?php view_issued_to($issue); ?></td></tr>
<tr><td >
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
    <tr>
        <th align="left" colspan=4>Item Status
            <div class="foldtoggle"><a id="btn_item_status" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
    <tbody id="item_status">
       <tr valign="top" align="left" class="normal">
        <td align="left" width=100>Status</td>
        <td align="left"><?php echo $item['status_name']?></td>
        </tr>
<?php 
    if ($item['id_status'] == ONLOAN){
        require 'loan/loan_util.php';
        $issue = get_request_by_item($item['id_item']);
?>
       <tr valign="top" align="left" class="alt">
        <td align="left">Request No.</td>
        <td align="left">LR<?php echo $issue['id_loan']?></td>
        <td align="right">Long Term</td>
        <td align="left"><?php echo ($issue['long_term'] == 1) ? 'True' : 'False';?></td>
        </tr>
       <tr valign="top" align="left" class="normal">
        <td align="left">Requested By</td>
        <td align="left"><?php echo $issue['requester']?></td>
        <td align="right">Request Date/Time</td>
        <td align="left"><?php echo $issue['request_date']?></td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left">Loan Start</td>
        <td align="left"><?php echo $issue['start_loan']?></td>
        <td align="right">Loan End</td>
        <td align="left"><?php echo $issue['end_loan']?></td>
        </tr>
       <tr valign="top" align="left" class="normal">
        <td align="left">Purpose</td>
        <td align="left" colspan=3><?php echo $issue['purpose']?></td>
        </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left">Remark</td>
        <td align="left" colspan=3><?php echo $issue['remark']?></td>
        </tr>
       <tr valign="top" align="left" class="normal">
        <td align="right" colspan=4><a class="button" href="./?mod=loan&act=view_issue&id=<?php echo $issue['id_loan']; ?>" >Detail Loan Info</a></td>
        </tr>
<?php
    }
?>
    </tbody>
    </table>
</td></tr>
</table>
<br/><br/>
<script>
$('#btn_item_info').click(function (e){
    toggle_fold(this);
});
$('#btn_item_status').click(function (e){
    toggle_fold(this);
});
$('.item').click(function(e){
    var id = this.id.substring(5);
    location.href="./?mod=report&sub=item&act=view&term=issued-out-detail&id="+id;
});
</script>
