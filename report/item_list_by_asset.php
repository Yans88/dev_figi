<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('asset_no' => 'asc', 
                          'serial_no' => 'asc', 
                          'category_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'brand_name' =>  'asc', 
                          'model_no' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : 'asset_no';
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : 'all';
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;
$brands = get_brand_list();

$total_item = 0;
if ($_searchtext != ''){
    if ($_searchtext == 'all')
        $total_item = count_item($_searchby, null, $dept, true);
    else
        $total_item = count_item($_searchby, $_searchtext, $dept, true);
}
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
$order_link = './?mod=report&sub=item&term=list&by=asset&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div id="submodhead" >
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
        
        $.post("item/asset_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", dept: ""+dept+"", searchBy: ""+$('input[name=searchby]').val()+""}, function(data){
            if(data.length >0) {
                $('#suggestions').fadeIn();
                $('#suggestionsList').html(data);
                var pos =  $('#searchtext').offset();                       
                $('#suggestions').css('position', 'absolute');
                $('#suggestions').offset({left:pos.left});
            } else
                $('#suggestions').fadeOut();
        });
    }
}

function reload_brand(me)
{
    var form = me.form;
    form.submit();
}

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<form method="get">
<input type="hidden" name="mod" value="report">
<input type="hidden" name="sub" value="item">
<input type="hidden" name="term" value="list">
<input type="hidden" name="by" value="asset">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div style="text-align: left; float: left; width: 80%; font-weight:bold" >
    Search Item by <input type="radio" name="searchby" value="asset_no" <?php if ($_searchby=='asset_no') echo ' checked ';?>>Asset No &nbsp;
    <input type="radio" name="searchby" value="serial_no" <?php if ($_searchby=='serial_no') echo ' checked ';?>>Serial No
    <img src="images/space.gif" height=1 width=100>
    Enter Part of No. 
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=30 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off> &nbsp;
    <input type="image" src="images/loupe.png" class="searchsubmit" >
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>
<?php if ($total_item>0){?>
<div style="float: right">
    <a class="button" href="./?mod=report&sub=item&term=export&by=asset&searchby=<?php echo $_searchby?>&searchtext=<?php echo $_searchtext?>&ordby=<?php echo $_orderby?>">Export</a>
</div>
<?php } ?>
</div>
</form>
<?php
    if ($total_item > 0) {
        
?>
&nbsp;<br>
&nbsp;<br>
<h4>Search Items by Asset No/Serial No "<?php echo $_searchtext?>"</h4>
<div class="clear"></div>
<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
    <th  <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
  <th   <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>category_name">Category</a></th>
  <th  <?php echo ($_orderby == 'brand_name') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>brand_name">Brand</a></th>
  <th  <?php echo ($_orderby == 'model_no') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>model_no">Model No</a></th>
  <th width=60>Purchase Price</th>
  <th width=85>Purchase Date</th>
  <th width=85>Warranty End Date</th>
  <th width=110>Status</th>
  <th>Action</th>
</tr>

<?php
if ($_searchtext == 'all')
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, null, $dept, true);
else
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept, true);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
    $edit_link = null;
    ///if (!SUPERADMIN && $i_can_update && $i_can_delete && ($rec['id_status']!=CONDEMNED))
    $_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
    echo <<<DATA
    <tr $_class>
    <td align="right">$counter</td>
    <td>$rec[asset_no]</td>
    <td>$rec[serial_no]</td>
    <td title="Department: $rec[department_name]">$rec[category_name]</td>
    <td title="Manufacturer: $rec[manufacturer_name]">$rec[brand_name]</td>
    <td >$rec[model_no]</td>
    <td align="center" >$rec[cost]</td>
    <td align="center" >$rec[date_of_purchase_fmt]</td>
    <td align="center" >$rec[warranty_end_date_fmt]</td>
    <td >$rec[status_name]</td>
    <td align="center" nowrap>
    <a href="?mod=item&act=view&id=$rec[id_item]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
    </td>
    </tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=11 class="pagination">';
echo make_paging($_page, $total_page, './?mod=report&sub=item&term=list&by=asset&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<div class="clear">&nbsp;</div><p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>
