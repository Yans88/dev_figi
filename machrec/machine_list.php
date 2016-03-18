<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('asset_no' => 'asc', 
                          'category_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'brand_name' =>  'asc', 
                          'status_name' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = machrec_count_item($_searchby, $_searchtext);
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
$order_link = './?mod=item&sub=item&act=list&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div id="submodhead" >
<div align="left" valign="middle" class="leftlink" >
<?php
/*
if ($i_can_create && !SUPERADMIN){
    echo '<a class="button" href="./?mod=item&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Item</a> ';
	echo '<a class="button" href="./?mod=item&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Item(s)</a> ';
}
if ($total_item > 0)
	echo '<a class="button" href="./?mod=item&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Item</a>';
*/
echo '</div>';
if ($total_item > 0){
?>
<script>
var dept = '<?php echo $dept?>';
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("machrec/machine_item_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", dept: ""+dept+"", searchBy: ""+$('#searchby').val()+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#searchtext').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({left:pos.left});
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
<input type="hidden" name="mod" value="machrec">
<input type="hidden" name="act" value="list">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div class="searchbox" >
    Search by
    <select name="searchby" id="searchby">
    <option value="asset_no" <?php if ($_searchby == 'asset_no') echo 'selected'?> >Asset No</option>
    <option value="serial_no" <?php if ($_searchby == 'serial_no') echo 'selected'?>>Serial No</option>
    <option value="category_name" <?php if ($_searchby == 'category_name') echo 'selected'?>>Category</option>
    <option value="vendor_name" <?php if ($_searchby == 'vendor_name') echo 'selected'?>>Vendor</option>
    <option value="manufacturer_name" <?php if ($_searchby == 'manufacturer_name') echo 'selected'?>>Manufacturer</option>
    <option value="brand_name" <?php if ($_searchby == 'brand_name') echo 'selected'?>>Brand</option>
    <option value="status_name" <?php if ($_searchby == 'status_name') echo 'selected'?>>Status</option>
    </select>
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off>
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
    <!--
    <input type="radio" name="searchby" value="asset_no" <?php echo ($_searchby=='asset_no')?' checked':null?> >Asset No
    <input type="radio" name="searchby" value="serial_no" <?php echo ($_searchby=='serial_no')?' checked':null?>>Serial No 
    -->
</div>
<?php } // total_item > 0 ?>
</div>
</form>
<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=2 cellspacing=1 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th width=110 <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
<?php
	if (USERDEPT == 0) {
?>
  <th width=100 <?php echo ($_orderby == 'department_name') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>department_name">Department</a></th>
<?php } ?>	
  <th width=110  <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>category_name">Category</a></th>
  <th width=150 <?php echo ($_orderby == 'vendor_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>vendor_name">Vendor</a></th>
  <th width=100 <?php echo ($_orderby == 'brand_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>brand_name">Brand</a></th>
  <th width=100 <?php echo ($_orderby == 'status_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>status_name">Status</a></th>
  <th width=50>Action</th>
</tr>

<?php
$rs = machrec_get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
	$edit_link = null;
	if (!SUPERADMIN && $i_can_update && $i_can_delete)
		$edit_link = <<<EDIT
<a href="?mod=machrec&sub=machine&act=info&id=$rec[id_machine]" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
EDIT;
	//$edit_link = null;
	$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	echo <<<DATA
	<tr $_class>
	<td align="right">$counter</td>
	<td title="Serial No.: $rec[serial_no]">$rec[asset_no]</td>
	$dept_name
	<td title="Department: $rec[department_name]">$rec[category_name]</td>
	<td>$rec[vendor_name]</td>
	<td title="Manufacturer: $rec[manufacturer_name]">$rec[brand_name]</td>
	<td >$rec[status_name]</td>
	<td align="center" nowrap>
	<a href="?mod=machrec&sub=machine&act=view&id=$rec[id_machine]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	$edit_link
	</td>
	</tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=8 class="pagination">';
echo make_paging($_page, $total_page, './?mod=item&sub=item&act=list&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>