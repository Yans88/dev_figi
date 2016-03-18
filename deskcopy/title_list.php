<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['DC_ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['DC_ITEM_ORDER_STATUS']);
else
    $order_status = array('isbn' => 'asc', 
                          'title' => 'asc', 
                          'author_name' => 'asc', 
                          'publisher_name' => 'asc', 
                          'department_name' => 'asc', 
                          'status' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'isbn';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = count_deskcopy_title($_searchby, $_searchtext, USERDEPT);
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
$_SESSION['DC_ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=deskcopy&sub=title&act=list&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div style="width:800px;" >
<div align="left" valign="middle" class="leftlink" >
<?php
/*
if ($i_can_create && !SUPERADMIN){
    echo '<a class="button" href="./?mod=deskcopy&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Add New Item</a> ';
	echo '<a class="button" href="./?mod=deskcopy&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import Item(s)</a> ';
}
if ($total_item > 0)
	echo '<a class="button" href="./?mod=deskcopy&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All Item</a>';
*/
echo '</div>';

if ($total_item > 0){
?>
<script>
function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("deskcopy/suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", searchBy: ""+$('#searchby').val()+""}, function(data){
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
<input type="hidden" name="mod" value="deskcopy">
<input type="hidden" name="sub" value="title">
<input type="hidden" name="act" value="list">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div class="searchbox" >
    Search by
    <select name="searchby" id="searchby">
    <option value="isbn" <?php if ($_searchby == 'isbn') echo 'selected'?> >ISBN</option>
    <option value="title" <?php if ($_searchby == 'title') echo 'selected'?>>Title</option>
    <option value="author_name" <?php if ($_searchby == 'author_name') echo 'selected'?>>Author</option>
    <option value="publisher_name" <?php if ($_searchby == 'publisher_name') echo 'selected'?>>Publisher</option>
    <option value="serial_no" <?php if ($_searchby == 'serial_no') echo 'selected'?>>Serial No</option>
    <option value="status" <?php if ($_searchby == 'status') echo 'selected'?>>Status</option>
    <!--<option value="department_name">Department</option>-->
    </select>
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off>
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
  <th width=80 <?php echo ($_orderby == 'isbn') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>isbn">ISBN</a></th>
  <th <?php echo ($_orderby == 'title') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>title">Title</a></th>
  <th width=150 <?php echo ($_orderby == 'author_name') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>author_name">Author</a></th>
  <th width=60>Number of Items</th>
  <th width=60>Available for Loan</th>
<?php
if (USERDEPT == 0) {
?>
  <th width=100 <?php echo ($_orderby == 'department_name') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>department_name">Department</a></th>
<?php } ?>  
  <th width=50>Action</th>
</tr>

<?php
$dept = defined('USERDEPT') ? USERDEPT : 0;
$rs = get_deskcopy_titles($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
	$edit_link = null;
	if (!SUPERADMIN && $i_can_update && $i_can_delete)
		$edit_link = <<<EDIT
<a href="./?mod=deskcopy&act=edit&id=$rec[id_title]" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
<a href="./?mod=deskcopy&act=del&id=$rec[id_title]" 
       onclick="return confirm('Are you sure delete &quot;$rec[title]&quot;?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
	
	$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	echo <<<DATA
	<tr $_class>
	<td align="right">$counter</td>
    <td>$rec[isbn]</td>
	<td>$rec[title]</td>
	<td>$rec[author_name]</td>
    <td align="center">$rec[number_of_items]</td>
    <td align="center">$rec[stock]</td>
    $dept_name
	<td align="center" nowrap>
	<a href="./?mod=deskcopy&act=view&id=$rec[id_title]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	$edit_link
	</td>
	</tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=8 class="pagination">';
echo make_paging($_page, $total_page, './?mod=deskcopy&sub=title&act=list&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>