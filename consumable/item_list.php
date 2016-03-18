<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['CONSUMABLE_ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['CONSUMABLE_ITEM_ORDER_STATUS']);
else
    $order_status = array('item_code' => 'asc', 
                          'item_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'category_name' => 'asc');

$dept = USERDEPT;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'item_code';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = count_consumable_item($_searchby, $_searchtext, $dept);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) 
    $_page = 1;
if ($_page > 0)
	$_start = ($_page-1) * $_limit;

$sort_order = $order_status[$_orderby];
if ($_changeorder)
    $sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;
$buffer = ob_get_contents();
ob_clean();
$_SESSION['CONSUMABLE_ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=consumable&sub=item&act=list&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div style="" >
<div align="left" valign="middle" class="leftlink" >
<?php
/*
if ($i_can_create && !SUPERADMIN){
    echo '<a class="button" href="./?mod=consumable&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Item</a> ';
	echo '<a class="button" href="./?mod=consumable&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Item(s)</a> ';
}
if ($total_item > 0)
	echo '<a class="button" href="./?mod=consumable&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Item</a>';
*/
echo '</div>';

if ($total_item > 0){
?>
<script type="text/javascript">
var department = '<?php echo  $dept?>';
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("consumable/suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", dept: ""+department+"", searchBy: ""+$('#searchby').val()+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#searchtext').offset();
                var w =  $('#searchtext').width();                                              
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({top:pos.bottom, left:pos.left});
                $('#suggestions').width(w);
			}
		});
	}
}

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<form method="get">
<input type="hidden" name="mod" value="consumable">
<input type="hidden" name="sub" value="item">
<input type="hidden" name="act" value="list">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div class="searchbox" >
    Search by
    <select name="searchby" id="searchby">
    <option value="item_code" <?php if ($_searchby == 'item_code') echo 'selected'?> >Item Code</option>
    <option value="item_name" <?php if ($_searchby == 'item_name') echo 'selected'?>>Item Name</option>
    <option value="category_name" <?php if ($_searchby == 'category_name') echo 'selected'?>>Category</option>
    <!--option value="vendor_name" <?php if ($_searchby == 'vendor_name') echo 'selected'?>>Vendor</option-->
    </select>
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete="off">
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>
<?php } ?>
</div>
</form>
<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th width=80 <?php echo ($_orderby == 'item_code') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>item_code">Item Code</a></th>
  <th <?php echo ($_orderby == 'item_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>item_name">Item Name</a></th>
  <th width=150 <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>category_name">Category</a></th>
  <th width=60>Available</th>
<?php
if ($dept == 0) {
?>
  <th width=100 <?php echo ($_orderby == 'department_name') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>department_name">Department</a></th>
<?php } ?>  
  <th width=50>Action</th>
</tr>

<?php

$rs = get_consumable_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
	$edit_link = null;
	if (!SUPERADMIN && $i_can_update && $i_can_delete)
		$edit_link = <<<EDIT
<a href="./?mod=consumable&act=edit&id=$rec[id_item]" item_name="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
<a href="./?mod=consumable&act=del&id=$rec[id_item]" 
       onclick="return confirm('Are you sure delete &quot;$rec[item_name]&quot;?')" item_name="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
	
	$dept_name = ($dept > 0) ? null : "	<td>$rec[department_name]</td>";
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	echo <<<DATA
	<tr $_class>
	<td align="right">$counter</td>
    <td>$rec[item_code]</td>
	<td>$rec[item_name]</td>
	<td>$rec[category_name]</td>
    <td align="center">$rec[item_stock]</td>
    $dept_name
	<td align="center" nowrap>
	<a href="./?mod=consumable&act=view&id=$rec[id_item]" item_name="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	$edit_link
	</td>
	</tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=8 class="pagination">';
echo make_paging($_page, $total_page, './?mod=consumable&sub=item&act=list&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>
