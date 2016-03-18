<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ITEM_ORDER_STATUS']);
else
    $order_status = array('asset_no' => 'asc', 
                          'category_name' => 'asc', 
                          'vendor_name' => 'asc', 
                          'brand_name' =>  'asc', 
                          'model_no' =>  'asc', 
                          'status_name' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_noofshown = isset($_GET['noofshown']) ? $_GET['noofshown'] : 10;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_orderdir = isset($_GET['orddir']) ? $_GET['orddir'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;
$_brand = !empty($_GET['id_brand']) ? $_GET['id_brand'] : null;
$_vendor = !empty($_GET['id_vendor']) ? $_GET['id_vendor'] : null;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : null;
$dept = defined('USERDEPT') ? USERDEPT : 0;


$category_list = get_category_list('',$dept);

$category_list[0] = '--- all ---';

$brand_list = get_brand_list();
$brand_list[0] = '--- all ---';

$vendor_list = get_vendor_list();
$vendor_list[0] =  '--- all ---';


$_limit = $_noofshown;
$_start = 0;
$total_item = 0;

if (isset($_GET['display']) || isset($_GET['pdf']) || isset($_GET['image'])||isset($_GET['generate_label'])||isset($_GET['label'])){
// count total item
    $criterias = array();
    $joins = array();
	$query  = "SELECT count(*) FROM item 
			   LEFT JOIN category ON item.id_category=category.id_category ";

    //case 'status_name' : $query .= "LEFT JOIN status ON item.id_status=status.id_status "; break;
	if ($_vendor>0) {
        $joins[] = " LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor "; 
        $criterias[] = ' item.id_vendor = ' . $_vendor;
    }
	if ($_brand>0) {
        $joins[] =  "LEFT JOIN brand ON item.id_brand=brand.id_brand "; 
        $criterias[] = ' item.id_brand = ' . $_brand;
    }
	if ($_category>0) {
        $criterias[] = ' item.id_category = ' . $_category;
    }
    if (!empty($joins))
        $query .= implode(' ', $joins);
    $query .= "WHERE category_type = 'EQUIPMENT' ";           
    if (!empty($criterias))
        $query .= ' AND ' . implode(' AND ', $criterias);
    
	$rs = mysql_query($query);
    
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$total_item = $rec[0];
	}
}
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;

$sort_order = $order_status[$_orderby];
if ($_changeorder)
    $sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;

$rs=null;
if ($total_item > 0){
    $query = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               WHERE category_type = 'EQUIPMENT' ";

    if (!empty($criterias))
        $query .= ' AND '. implode(' AND ', $criterias);
    $query .= " ORDER BY $_orderby $_orderdir LIMIT $_start,$_limit ";
    $rs = mysql_query($query);
}
$buffer = ob_get_contents();
ob_clean();
if (isset($_GET['pdf'])){
    $result_set = $rs;
    include 'item/item_barcode_pdf.php';
    exit;
} elseif (isset($_GET['image'])){
    $result_set = $rs;
    include 'item/item_barcode_image.php';
    exit;
} elseif (isset($_POST['generate_label'])){
    $result_set = $rs;
    include 'item/item_barcode_label.php';
    exit;
}
$_SESSION['ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=item&sub=item&act=barcode&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

$limit_options = array(10 => 10, 20 => 20, 50 => 50, 100 => 100, 99999 => 'all');

$order_list = array(
    "asset_no" => "Asset No",
    "serial_no" => "Serial No",
    "category_name" => "Category",
    "vendor_name" => "Vendor",
    "brand_name" => "Brand",
    "model_no" => "Model No"
    );
$order_dir_list = array(
    'asc' => 'Ascending',
    'desc' => 'Descending'
    );

?>
<br/>
<div id="submodhead" >

<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<form method="get">
<input type="hidden" name="mod" value="item">
<input type="hidden" name="act" value="barcode">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<table style="width: 500px; text-align: left; " cellpadding=2 cellspacing=1>
<tr>
    <td>Category</td><td><?php echo build_combo('id_category', $category_list, $_category)?> </td>
    <td>Sort By</td><td> <?php echo build_combo('ordby', $order_list, $_orderby)?> </td>
</tr>
<tr>
    <td>Brand:</td> <td><?php echo build_combo('id_brand', $brand_list, $_brand)?>  </td>
    <td>Order</td><td> <?php echo build_combo('orddir', $order_dir_list, $_orderdir)?> </td>
</tr>
<tr>
    <td>Vendor</td><td> <?php echo build_combo('id_vendor', $vendor_list, $_vendor)?> </td>
    <td>No of Items</td><td> <?php echo build_combo('noofshown', $limit_options, $_limit)?> </td>
</tr>
<tr>
    <td colspan=4 align="center">
    <button name="display">Display</button>  <button name="pdf">Pdf Format</button>  <button name="image">Full Image</button> <button name="label">Label</button> 
    </td>
</tr>
</table>
<br/>

</div>
</form>
<?php
    if ($total_item > 0) {
		if(isset($_GET['display'])){
		

			if ($total_item < $_limit)
				$rows = ceil($total_item / 2);
			else
				$rows = ceil($_limit/ 2);
			?>
		<div class="clear"></div>
		<table id="itemlist" cellpadding=2 cellspacing=2 style="background-color: #fff; color: #000" >
		<?php
			$displayed = ($_noofshown > $total_item) ? $total_item : $_noofshown;
			echo "<tr><td colspan=2>Displaying Item's Barcode  #$displayed of #$total_item</td></tr>";
			for ($i = 0; $i < $rows; $i++){
				echo '<tr>';
				for ($j = 0; $j < 2; $j++){
					$rec = mysql_fetch_array($rs);
					if (!empty($rec))
						echo '<td><img class="barcode" src="./gb.php?text=1&format=png&barcode='.$rec['asset_no'].'" title="'.$rec['asset_no'].'"></td>';
					else
						break ;
				}
				echo '</tr>';
			}
		?>
		<?php

		echo '<tr ><td colspan=8 class="pagination">';
		echo make_paging($_page, $total_page, './?mod=item&act=barcode&ordby='.$_orderby.'&id_category='.$_category.'&id_brand='.$_brand.'&orddir='.$_orderdir.'&id_vendor='.$_vendor.'&noofshown='.$_noofshown.'&display=&page=');
		echo  '</td></tr></table><br/>';
	}elseif(isset($_GET['label']))
		{
    if ($total_item > 0) {
	?>
			<div class="clear"></div>
			<form method="post" target="_blank">
			<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
			<tr height=30>
			  <th width=30>No</th>
			  <th width=110 <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>>
				<a href="<?php echo $order_link ?>asset_no">Asset No</a></th>
			  <th width=100 <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
				<a href="<?php echo $order_link ?>serial_no">Serial No</a></th>
			  <th width=110  <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
				<a href="<?php echo $order_link ?>category_name">Category</a></th>
			<?php if (SUPERADMIN){ ?>
			  <th width=110  <?php echo ($_orderby == 'department_name') ? $row_class : null ?> >
				<a href="<?php echo $order_link ?>department_name">Department</a></th>
			<?php } ?>
			  <th width=100 <?php echo ($_orderby == 'brand_name') ? $row_class : null ?> >
				<a href="<?php echo $order_link ?>brand_name">Brand</a></th>
			  <th width=100 <?php echo ($_orderby == 'model_no') ? $row_class : null ?> >
				<a href="<?php echo $order_link ?>model_no">Model No</a></th>
			  <th width=100 <?php echo ($_orderby == 'status_name') ? $row_class : null ?> >
				<a href="<?php echo $order_link ?>status_name">Status</a></th>
			  <th width=50>Print</th>
			</tr>

			<?php
			$counter = $_start+1;
			while ($rec = mysql_fetch_array($rs))
			{
					$edit_link = '<input type="checkbox" name="sel[]" value="'.$rec['asset_no'].'" checked>';
				
				$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
				$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
				$dept_col = (SUPERADMIN) ? "<td>$rec[department_name]</td>" : '';
				echo <<<DATA
				<tr $_class>
				<td align="right">$counter</td>
				<td>$rec[asset_no]</td>
				<td>$rec[serial_no]</td>
				<td>$rec[category_name]</td>
				$dept_col
				<td>$rec[brand_name]</td>
				<td >$rec[model_no]</td>
				<td >$rec[status_name]</td>
				<td align="center" nowrap>
				$edit_link
				</td>
				</tr>
DATA;
			  $counter++;
			}

			echo '<tr ><td colspan=8 class="pagination">';
			echo make_paging($_page, $total_page, './?mod=item&act=barcode&ordby='.$_orderby.'&id_category='.$_category.'&id_brand='.$_brand.'&orddir='.$_orderdir.'&id_vendor='.$_vendor.'&noofshown='.$_noofshown.'&label=&page=');
			echo  '</td></tr><tr><td style="text-align: center" colspan=8 ><input type="submit" value="GENERATE" name="generate_label"></td></tr></table><br/></form>';

			} else { //total_item <= 0 
				echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
			}
		}
	} else { //total_item <= 0 
		if (isset($_GET['display']))
		echo '<p class="error" style="margin-top: 10px">Data is not available!</p>';
	}
?>
<script type="text/javascript">
//$('#searchtext').focus();
    
</script>