<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('asset_no' => 'asc', 
                          'serial_no' => 'asc', 
                          'category_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'location_name' =>  'asc', 
                          'model_no' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_id = !empty($_GET['id_location']) ? $_GET['id_location'] : 0;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : 0;
$_store = !empty($_GET['id_store']) ? $_GET['id_store'] : 0;
$_dept = !empty($_GET['id_department']) ? $_GET['id_department'] : 0;
$_status = !empty($_GET['id_status']) ? $_GET['id_status'] : 0;
$model_no = !empty($_GET['model_no']) ? $_GET['model_no'] : '';

$pchf = $_POST['choose_field'];
$_SESSION['ITEM_FIELD'] = isset($pchf)? serialize($pchf):$_SESSION['ITEM_FIELD'];
$dept = defined('USERDEPT') ? USERDEPT : 0;


///////////////////////////////////////////////////////////////////
if (!empty($_SESSION['ITEM_FIELD']))
    $item_field = unserialize($_SESSION['ITEM_FIELD']);
else

    $item_field = array('category_name|Category','status_name|Status');


$opt_sel = array('model_no|Model Number','location_name|Location','department_name|Department','store_name|Store','category_name|Category','status_name|Status');




$name_field=array();
foreach($item_field as $tag){
  $name_field[]=explode('|',$tag);
  }
$fl_n = empty($name_field)? array('category_name','status_name','store_name','category_name','cost','invoice'):array();
foreach ($name_field as $row){
	$fl_n[] = $row[0];
}



$item_f = array('id_category'=>$_category,'id_store'=>$_store,'model_no'=>$model_no,'id_status'=>$_status);
if(SUPERADMIN){
	$item_f['id_department'] = $_dept;
}

/////////////////////////////////////////////////////////////////////////
if (!empty($_SESSION['ITEM_FIELD']))
    $item_field = unserialize($_SESSION['ITEM_FIELD']);
else
    $item_field = array('location_name|Location','category_name|Category','department_name|Department','cost|Cost of Item','date_of_purchase_fmt|Purchase Date','invoice|Invoice Number','status_name|Status') ;


$opt_sel = array('model_no|Model Number','location_name|Location','department_name|Department','store_name|Store Type','category_name|Category','cost|Cost of Item','date_of_purchase_fmt|Purchase Date','invoice|Invoice Number','status_name|Status');
$chf = (isset($_POST['choose_field']))? $_POST['choose_field'] : ;
$name_field=array();
foreach($chf as $tag){
  $name_field[]=explode('|',$tag);
  }
$fl_n = empty($name_field)? array('category_name','status_name','store_name','category_name','cost','invoice'):array();
foreach ($name_field as $row){
	$fl_n[] = $row[0];
}
// echo print_r($item_f);


$item_f = array('id_category'=>$_GET['id_category'],'id_store'=>$_GET['id_store'],'model_no'=>$_GET['model_no'],'id_status'=>$_GET['id_status'],'id_location'=>$_id);
if(SUPERADMIN){
	$item_f['id_department'] = $_GET['id_dept'];
}

$_limit = RECORD_PER_PAGE;
$_start = 0;

$locations[0] = '-- all locations --';
$locations += get_location_list();
$categories[0] ='-- all categories --';
$categories += get_category_list('EQUIPMENT', $dept);
$store[0] ='-- all store type --';
$store += get_store_list();
$statuses[0] = '-- all statuses --';
$statuses += get_status_list();
/*
if ($_id==0){
    $location_keys = array_keys($locations);
    $_id = $location_keys[0];
}
  */  
$_searchby = 'id_store';
$_searchtext = $_store;
$total_item = count_item($_searchby, $_searchtext,$dept,false,$item_f);
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
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=report&sub=item&term=list&by=store&chgord=1&searchby='.$_searchby.'&id_location='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div id="submodhead" style="overflow:hidden">
<script>
var dept = '<?php echo $dept?>';
function reload_location(me)
{
	var form = me.form;
	form.submit();
}
var values=<?php echo json_encode($fl_n)?>;
$('#choose_field').prop('selected', 'selected');
// $.each(values, function(i,e){
    // $("#choose_field option[value='a']").prop('selected', true);
	// alert(e);
// });
</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
#filter_group div{display: inline-block}
#filter_group div > select,#filter_group div > input{width: 120px}
</style>
<form method="get">
<input type="hidden" name="mod" value="report">
<input type="hidden" name="sub" value="item">
<input type="hidden" name="term" value="list">
<input type="hidden" name="by" value="store">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div style="display: inline-block;text-align: left; float: left; width: 70%;  font-weight:bold" id="filter_group">
   
	<div>Filtered by Store Type <?php echo build_combo('id_store', $store, $_store, 'reload_location(this)');?> </div>
    <div>Category <?php echo build_combo('id_category', $categories, $_category, 'reload_location(this)');?> </div>
	<?php if(SUPERADMIN){?><div>Department <?php echo build_combo('id_dept', get_department_list(), $_dept, 'reload_location(this)');?></div><?php }?>
	<div>Model Number <input type='text' name="model_no" value='<?php echo $model_no ?>' onblur='reload_location(this)'></div>
	<div>Location <?php echo build_combo('id_location', $locations, $_id, 'reload_location(this)');?> </div>
	<div>Status<?php echo build_combo('id_status', $statuses, $_status,'reload_location(this)');?></div>
</div>
</form>
<form method="post">
<div style="display: inline-block;text-align: left; float: left;  font-weight:bold">
	<p style='margin: 0;'>Choose Field : </p>
	<select multiple id="choose_field" name="choose_field[]" size="3" onblur="reload_location(this)">
		<option value="asset_no" disabled>Asset Number</option>
		<option value="serial_no" disabled>Serial Number</option>
		<?php foreach($opt_sel as $row){
			$optex = explode('|',$row);
			
				$sel_opt = in_array($row,$_POST['choose_field']) ? 'selected' : '';
				$sel_opt = isset($_POST['choose_field']) ? $sel_opt : 'selected';
				$str = "<option value='$row' $sel_opt>$optex[1]</option>";
				if($row=='department_name|Department'&&!SUPERADMIN)continue;
			echo $str;
		} ?>
		
		
		
	</select>
</div>
</form>
    

<?php if($total_item>0){?>
<div style="float: right">
    <a class="button" href="./?mod=report&sub=item&term=export&by=store&chgord=1&searchby=id_location&id_location=<?php echo $_searchtext?>&ordby=<?php echo $_orderby?>">Export</a>
</div>
<?php } ?>
</div>

<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
	<th <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
	<a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
	<?php 
			$fl = "";
			foreach($name_field as $row){
	?>
	<th <?php echo ($_orderby == $row[0]) ? $row_class : null ?> >
	<a href="<?php echo $order_link .$row[0]?>"><?php echo $row[1]?></a></th>
	
	
	<?php
		}
	?>
  <th width=50>Action</th>
</tr>

<?php
$rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept, false,$item_f);
$counter = $_start+1;
while ($rec = mysql_fetch_array($rs))
{
	$edit_link = null;
	if (!SUPERADMIN && $i_can_update && $i_can_delete && ($rec['id_status']!=CONDEMNED))
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	$str_out = "<tr $_class><td align='right'>$counter</td><td>$rec[asset_no]</td><td>$rec[serial_no]</td>";
	foreach($fl_n as $row){
	$str_out .= "<td >$rec[$row]</td>";
	}
	$str_out .="<td align='center' nowrap><a href='?mod=item&act=view&id=$rec[id_item]' title='view'><img class='icon' src='images/loupe.png' alt='view' ></a></td></tr>";
	echo $str_out;
  $counter++;
}

echo '<tr ><td colspan=10 class="pagination">';
echo make_paging($_page, $total_page, './?mod=report&sub=item&term=list&by=location&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<br>&nbsp;<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>
