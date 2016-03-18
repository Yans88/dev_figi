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
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : date('M-Y');
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$_searchby = 'condemn_date';

function count_item_tobe_condemn($My = null, $dept = 0)
{
	$result = 0;
	$query  = "SELECT count(*) FROM item i 
                LEFT JOIN category c ON i.id_category=c.id_category 
                WHERE category_type = 'EQUIPMENT' AND 
                DATE_FORMAT(DATE_ADD(i.date_of_purchase, INTERVAL c.condemn_period MONTH), '%b-%Y') = '$My' ";
    if ($dept > 0)
        $query .= " AND (c.id_department = $dept OR i.id_owner = $dept) ";
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}

function get_items_tobe_condemn($orderby = 'asset_no', $sort = 'asc', $start = 0, $limit = 10, $My = null, $dept = 0)
{
	$query  = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name,
                DATE_FORMAT(DATE_ADD(item.date_of_purchase, INTERVAL category.condemn_period MONTH), '%d-%b-%Y') AS condemn_date,
                DATE_FORMAT(warranty_end_date, '%d-%b-%Y') AS warranty_end_date_fmt, 
                DATE_FORMAT(date_of_purchase, '%d-%b-%Y') AS purchase_date_fmt 
                FROM item 
                LEFT JOIN category ON item.id_category=category.id_category 
                LEFT JOIN department ON category.id_department = department.id_department 
                LEFT JOIN status ON item.id_status=status.id_status 
                LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
                LEFT JOIN brand ON item.id_brand=brand.id_brand 
                LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
                WHERE category_type = 'EQUIPMENT' AND   
                DATE_FORMAT(DATE_ADD(item.date_of_purchase, INTERVAL category.condemn_period MONTH), '%b-%Y') = '$My' ";
	if ($dept > 0)
		$query .= " AND (category.id_department = $dept OR item.id_owner = $dept) ";
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
    //echo $query.mysql_error();
	return $rs;
}

$total_item = count_item_tobe_condemn($_searchtext, $dept);
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
$order_link = './?mod=report&sub=item&term=list&by=condemn_date&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

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
        
        $.post("item/invoice_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", dept: ""+dept+"", searchBy: "invoice"}, function(data){
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
<input type="hidden" name="by" value="condemn_date">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div style="text-align: left; float: left; width: 80%;  font-weight:bold" >
    Projected Condemn Date (Month)
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=10 value="<?php echo $_searchtext?>"> &nbsp;
    <input id="loupe" type="image" src="images/loupe.png" class="searchsubmit" >
    <script>
        $('#searchtext').AnyTime_noPicker().AnyTime_picker({format: "%b-%Y"});
        $('#loupe').focus();
    </script>

</div>
<?php if ($total_item>0) {?>
<div style="float: right">
    <a class="button" href="./?mod=report&sub=item&term=export&by=condemn_date&chgord=1&searchby=condemn_date&searchtext=<?php echo $_searchtext?>&ordby=<?php echo $_orderby?>">Export</a>
</div>
<?php } ?>
</div>
</form>
<?php
    if ($total_item > 0) {
?>

<div class="clear"></div>
<form method="post" action="./?mod=condemned&act=issue">
<input type="hidden" id="itemlist" name="itemlist" value="">

<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
    <th <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
  <th <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>category_name">Category</a></th>
  <th <?php echo ($_orderby == 'brand_name') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>brand_name">Brand</a></th>
  <th <?php echo ($_orderby == 'model_no') ? $row_class : null ?> >
    <a href="<?php echo $order_link ?>model_no">Model No</a></th>
  <th width=80>Purchase Price</th>
  <th width=85>Purchase Date</th>
  <th width=85>Warranty End Date</th>
  <th width=85>Projected Condemn Date</th>
  <th width=50>Action<br><input type="checkbox" id="cb_toggle" name="cb_toggle"></th>
</tr>

<?php
$rs = get_items_tobe_condemn($_orderby, $sort_order, $_start, $_limit, $_searchtext, $dept);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
    $edit_link = null;
    if (!SUPERADMIN && $i_can_update && $i_can_delete && ($rec['id_status']!=CONDEMNED))
    $_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
    echo <<<DATA
    <tr $_class>
    <td align="right">$counter</td>
    <td>$rec[asset_no]</td>
    <td>$rec[serial_no]</td>
    <td title="Department: $rec[department_name]">$rec[category_name]</td>
    <td title="Manufacturer: $rec[manufacturer_name]">$rec[brand_name]</td>
    <td >$rec[model_no]</td>
    <td >$rec[cost]</td>
    <td align="center">$rec[purchase_date_fmt]</td>
    <td align="center">$rec[warranty_end_date_fmt]</td>
    <td align="center">$rec[condemn_date]</td>
    <td align="center" nowrap>
    <a href="?mod=item&act=view&id=$rec[id_item]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
    <input type="checkbox" name="cb" class="cb" value="$rec[id_item]" id="cb$rec[id_item]">
    </td>
    </tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=11 class="pagination">';
echo make_paging($_page, $total_page, './?mod=report&sub=item&term=list&by=condemn_date&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo '<div class="modmenu"><button type="button" id="condemnbtn">Create Condemnation Process</button></div>';
echo  '</td></tr></table><br/></form>';

} else { //total_item <= 0 
    echo '<div class="clear">&nbsp;</div><p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
    $('#cb_toggle').click(function(e){
        //alert($(this).attr('checked'))
        if ($(this).attr('checked')){
            $('.cb').attr('checked', true);
        } else {
            $('.cb').attr('checked', false);
        }
    });
    $('#condemnbtn').click(function(e){
        var cbs = $('.cb');
        var items = new Array();
        for(var i=0; i<cbs.length; i++){
            if (cbs[i].checked)
                items.push(cbs[i].value);
        }
        if (items.length>0){
            $('#itemlist').val(items.join(','));
            $('#itemlist')[0].form.submit();
        }
    });
</script>
