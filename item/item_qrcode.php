<?php
if (!defined('FIGIPASS')) exit;
if (!QR_CODE){ 
	echo 'Unknown Module'; 
	exit;
}
// include '../phpqrcode/qrlib.php';
//include('./qrcode_util.php');

include "simple_qrcode/qrcode.php";
			$qr = new qrcode();
			
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
$_noofshown = isset($_GET['noofshown']) ? $_GET['noofshown'] : 18;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_orderdir = isset($_GET['orddir']) ? $_GET['orddir'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;
$_brand = !empty($_GET['id_brand']) ? $_GET['id_brand'] : null;
$_vendor = !empty($_GET['id_vendor']) ? $_GET['id_vendor'] : null;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : null;
$_sf_array = !empty($_GET['selected_field']) ? explode(',',$_GET['selected_field']) : array();

$dept = defined('USERDEPT') ? USERDEPT : 0;


$category_list = get_category_list(null,$dept,null,null);
$category_list[0] = '--- all ---';

$brand_list = get_brand_list();
$brand_list[0] = '--- all ---';

$vendor_list = get_vendor_list();
$vendor_list[0] =  '--- all ---';


$_limit = $_noofshown;
$_start = 0;
$total_item = 0;


if (isset($_GET['display']) ||isset($_GET['label'])){
// count total item
	
    $criterias = array();
    $joins = array();
	$query  = "SELECT count(*) FROM item ";
	
	if (!empty($dept))
		//$joins[] = " LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor "; 
		$criterias[] = ' item.id_department = '.$dept.'';
		
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
		$joins[] =  "LEFT JOIN category ON item.id_category=category.id_category "; 
        $criterias[] = ' item.id_category = ' . $_category;
    } else {
		$joins[] =  "LEFT JOIN category ON item.id_category=category.id_category "; 
	}
    if (!empty($joins))
        $query .= implode(' ', $joins);
		$query .= "WHERE category.category_type = 'EQUIPMENT' ";           
    if (!empty($criterias))
        $query .= ' AND ' . implode(' AND ', $criterias);
    
	
	
	$rs = mysql_query($query);
    //echo mysql_error().$query;
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

/*
SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department 
               LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               WHERE category_type = 'EQUIPMENT'
*/

$rs=null;

if ($total_item > 0){
	/*
    $query = "SELECT item.*, status_name, brand_name, category_name, vendor_name, manufacturer_name, department_name  
               FROM item 
               LEFT JOIN category ON item.id_category=category.id_category 
               LEFT JOIN department ON category.id_department = department.id_department
			   LEFT JOIN status ON item.id_status=status.id_status 
               LEFT JOIN vendor ON item.id_vendor=vendor.id_vendor 
               LEFT JOIN brand ON item.id_brand=brand.id_brand 
               LEFT JOIN manufacturer ON brand.id_manufacturer=manufacturer.id_manufacturer 
               WHERE category_type = 'EQUIPMENT'";
			   // 
	*/
	
	//THIS IS NEW QUERY
	$query = "
	SELECT item.*, category.category_name, vendor.vendor_name, location.location_name, brand.brand_name, status.status_name, department.department_name
		FROM item 
		LEFT JOIN category ON category.id_category = item.id_category
		LEFT JOIN vendor ON vendor.id_vendor = item.id_vendor
		LEFT JOIN location ON location.id_location = item.id_location
		LEFT JOIN brand ON brand.id_brand = item.id_brand
		LEFT JOIN status ON status.id_status = item.id_status
		LEFT JOIN department ON department.id_department = item.id_department
	WHERE category.category_type = 'EQUIPMENT'";
	
	if (!empty($dept))
		$query .= ' AND item.id_department = '.$dept.'';
	
	if (!empty($_category))
        $query .= ' AND item.id_category = "'.$_category.'" ';
		
	if (!empty($_brand))
        $query .= ' AND item.id_brand = "'.$_brand.'" ';
    	
    $query .= " ORDER BY $_orderby $_orderdir LIMIT $_start,$_limit ";
    
	$rs = mysql_query($query);
	//var_dump($query);
}
$buffer = ob_get_contents();
ob_clean();

$_SESSION['ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=item&act=qrcode&id_category=&id_brand=&chgord=1&orddir='.$_orderdir.'&id_vendor=&noofshown='.$_noofshown.'&label=&page='.$_page.'&ordby=';
//echo $_orderby;
$limit_options = array(18 => 18, 36 => 36, 54 => 54, 72 => 72, 90 => 90, 99999 => 'all');

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
<?php //echo @$_GET['selected_field'];?>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
.qc{text-align: center}
</style>
<form method="get" id="bot_form">
<input type="hidden" name="mod" value="item">
<input type="hidden" name="act" value="qrcode">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<input type="hidden" name="selected_field" id="selected_field" value="">
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
    <td colspan=6 align="center">
    <button name="display">Display</button>  <button name="label">Display & Selected item</button> 
    </td>
</tr>
</table>
<br/>


</div>
</form>
<?php
if (isset($_POST['generate_qrcode'])){
			//$result_set = $rs;
			
			include 'item/item_qrcode_generate.php';
			//echo "<script>alert('testing');</script>";
			exit;
		}
        

if ($total_item > 0) {
		if(isset($_GET['display'])){
			if ($total_item < $_limit)
				$rows = ceil($total_item / 2);
			else
				$rows = ceil($_limit/ 2);
			
			?>
			<div class="clear"></div>
			<?php
				$displayed = ($_noofshown > $total_item) ? $total_item : $_noofshown;
				$num_displayed = $displayed;
				echo "<div>
						Displaying Item's Barcode  #$displayed of #$total_item
					  </div>
					  
				";
			?>
			
			<div id="itemlist">
			<button onclick="return PrintDoc()">Preview & Print</button>
			<select id='type_print' onChange="return choose_type_print();">
				<option value=''>-- Choose Printer Paper --</option>
				<option value='0'>Potrait (A4)</option>
				<option value='1'>Landscape</option>
			</select>
			<script>
				function choose_type_print(){
					var x = document.getElementById("type_print").value;
					
					if(x == 1){
						location.href=window.location + '&display_printer=1';
					} else {
						location.href=window.location + '&display_printer=0';
					}
				
				}
			</script>
	<div id="printarea">
			<?php
				$a = $_GET['display_printer'];
				
			?>
			<style>	
			<?php if ($a == 0) { ?>
				.papers{
					size:A4;
					max-width:188mm;
					height:1020px;
					background-color:#fff;
					clear:both;
					padding-top:20px;
					padding-left:25px;
					padding-bottom:20xp;
					margin-bottom:20px;
					font-size:10px;
					overflow:hidden;
				}
				
				.papers .listing{
					border:1px solid #000;
					border-radius:10px;
					moz-border-radius:10px;
					float:left;
					margin:1px;
					width:220px;
					height:150px;
					color:#000;
				}
				<?php } else {?>
				
				.papers {
					width:62mm;
					height:auto;
					background-color:#fff;
					clear:both;
					font-size:10px;
					overflow:hidden;
					margin-top:30px;
					padding-top:15px;
					
					
				}
				.papers .listing{
					-webkit-transform: rotate(90deg);
					-moz-transform: rotate(90deg);
					-o-transform: rotate(90deg);
					-ms-transform: rotate(90deg);
					transform: rotate(90deg);
					border:1px solid #000;
					border-radius:10px;
					moz-border-radius:10px;
					float:left;
					width:220px;
					height:150px;
					color:#000;
					margin-top:40px;
					margin-bottom:160px;
				}
				<?php } ?>
				.papers img.qrcode{
					width:55px;height:55px;
					cursor:pointer;padding:3px;
					float:left;
				}

				.papers img.barcode{
					width:200px;height:20px;
					cursor:pointer;
					padding:0 0 0 10px;
					margin:0;clear:both;
					padding:0;float:left;
				}
				
				.papers .list-one		{padding:1px;}
				.papers .list-one p 	{text-align:left;padding-top:4px;margin:0;}
				.papers .list-two		{padding:0px;margin:0px;}
				.papers .list-two p 	{clear:both;padding:0px;text-align:center;}
				.papers .list-tree		{padding:0;margin:0px;}
				.papers .list-tree p	{clear:both;padding:0px;text-align:center;}
				
			</style>
		
			<?php
				
				
				//echo ''.$_orderby.', '.$sort_order.', '.$_start.', '.$_limit.', '.$_searchby.', '.$_searchtext.', '.$dept.'';
				//$rs = mysql_fetch_array($rs); //get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept, false);
					  
				$data_arr=array();
				for ($i = 0; $i < $num_displayed; $i++){
						//echo $query."<br />";
						
					$rec = mysql_fetch_array($rs);
					$qr->text($rec['asset_no']."\r\n".$rec['serial_no']."\r\n".$rec['department_name']."\r\n".$rec['category_name']."\r\n".$rec['brand_name']."\r\n".$rec['model_no']);
					if (!empty($rec))
						
						$data_arr[] = "<div class='listing'>	
						<div style='text-align:center;'>Singapore Chinese Girls' School</div>
						<div class='list-one'>
							<img class='qrcode' src='".$qr->get_link()."' border='0'> 
							<p>
								".$rec['department_name']."<br />".$rec['category_name']."<br />".$rec['brand_name']."<br />".$rec['model_no']."
							
							</p>
						</div>
						<div class='list-two'>
							<img class='barcode' src='item/lib_barcode/barcode.php?text=".$rec['asset_no']."' alt='".$rec['asset_no']."'><p> ".$rec['asset_no']."</p>
						</div>
						<div class='list-tree'>
							<img class='barcode' src='item/lib_barcode/barcode.php?text=".$rec['serial_no']."' alt='".$rec['serial_no']."'><p> ".$rec['serial_no']."</p>
						</div>
					</div>";
					else
						break ;
						
				}
				
				for ($o = 0; $o < $num_displayed; $o++){
				
					if($o % 18 == 0){

						echo "<div class='papers'>";
						
						if($o == 0){
							for($vv = 0; $vv<18;$vv++){
								echo $data_arr[$vv];
							}
						} 
						
						if($o == 18){
						
							for($ww = 18; $ww<36;$ww++){
								echo $data_arr[$ww];
							}
						
						}
						
						if($o == 36){
							for($xx = 36; $xx<54;$xx++){
								echo $data_arr[$xx];
							}
						}
						
						if($o == 54){
							for($yy = 54; $yy<72;$yy++){
								echo $data_arr[$yy];
							}
						}
						
						if($o == 72){
							for($zz = 72; $zz<90;$zz++){
								echo $data_arr[$zz];
							}
						}
						
						if($o == 90){
							for($aa = 90; $aa<107;$aa++){
								echo $data_arr[$aa];
							}
						}
						echo "</div>";
						
					}
				
				}
		
			?>
			
			<?php

			
			echo  '
				   </div>';
				   
			echo '<div class="pagination">';
			echo make_paging($_page, $total_page, './?mod=item&act=qrcode&ordby='.$_orderby.'&id_category='.$_category.'&id_brand='.$_brand.'&orddir='.$_orderdir.'&id_vendor='.$_vendor.'&noofshown='.$_noofshown.'&display=&page=');
			echo "</div>";
			
		}elseif(isset($_GET['label'])){
		
		
		
		
		
        if ($total_item < $_limit)
            $rows = ceil($total_item / 2);
        else
            $rows = ceil($_limit/ 2);
        
		?>
		<div class="clear"></div>
		<?php
			$displayed = ($_noofshown > $total_item) ? $total_item : $_noofshown;
			$num_displayed = $displayed;
			echo "<div>
					Displaying Item's Barcode  #$displayed of #$total_item
				  </div>
				  ";
		?>
		
		<div id="itemlist">
		<div id="show_data">
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
			  <th width=50>Select</th>
			</tr>
		<?php
			
			
			//echo ''.$_orderby.', '.$sort_order.', '.$_start.', '.$_limit.', '.$_searchby.', '.$_searchtext.', '.$dept.'';
			//$rs = mysql_fetch_array($rs); //get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept, false);
			$counter = $_start+1;
			$data_arr=array();
			for ($i = 0; $i < $num_displayed; $i++){
			
				
			
				
					
					$rec = mysql_fetch_array($rs);
					$qr->text($rec['asset_no']."\r\n".$rec['serial_no']."\r\n".$rec['department_name']."\r\n".$rec['category_name']."\r\n".$rec['brand_name']."\r\n".$rec['model_no']);
					if (!empty($rec)){
						
							$edit_link = '<input type="checkbox" id="sel" name="sel[]" value="'.$rec['asset_no'].'">';
						
							$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
							$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
							$dept_col = (SUPERADMIN) ? "<td>$rec[department_name]</td>" : '';
							echo '
							<tr '.$_class.'>
							<td align="right">'.$counter++.'</td>
							<td>'.$rec['asset_no'].'</td>
							<td>'.$rec['serial_no'].'</td>
							<td>'.$rec['category_name'].'</td>
							'.$dept_col.'
							<td>'.$rec['brand_name'].'</td>
							<td >'.$rec['model_no'].'</td>
							<td >'.$rec['status_name'].'</td>
							<td align="center" nowrap>
							'.$edit_link.'
							</td>
							</tr>
							';
						
						
					} else {
						break ;
					}
				
			}
			
			
			
		?>
		
		<?php

		
			   
	    echo '<tr class="pagination" ><td colspan="9">';
		echo make_paging($_page, $total_page, './?mod=item&act=qrcode&ordby='.$_orderby.'&id_category='.$_category.'&id_brand='.$_brand.'&orddir='.$_orderdir.'&id_vendor='.$_vendor.'&noofshown='.$_noofshown.'&label=&page=');
		echo "</td></tr>";
		echo  '
		</table>
		</div>
		</div>
		<input type="submit" value="GENERATE" name="generate_qrcode" >';
		echo "</form>";
		
		
		echo "<br /><br /><br /><br />";
		
		} 
		
	
	
	
	
} else { //total_item <= 0 
    if ((isset($_GET['display'])) || (isset($_GET['label'])))
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script type="text/javascript">
$("#bot_form").submit(function(event){
	var a = $("#select_table option:selected").map(function(){ return this.value }).get().join(",");
	$("#selected_field").val(a);
	// event.preventDefault();
});
$('#select_table').change(function(){
	$('#select_table option[value=asset_no]').attr('selected','selected');
});


 function PrintDoc() {

        var toPrint = document.getElementById('printarea');

        var popupWin = window.open('', '_blank', 'scrollbars=1,max-width=695px,height=900px,location=no,left=200px');

        popupWin.document.open();

        popupWin.document.write('<html><title>Preview</title></head><body onload="window.print()">')

        popupWin.document.write(toPrint.innerHTML);

        popupWin.document.write('</html>');

        popupWin.document.close();

    }

function myFunction(){
	var i = document.getElementById('sel').value;
	
    document.getElementById("show_data").innerHTML = i;

}
</script>
