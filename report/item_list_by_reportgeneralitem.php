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

						  
$filterby = htmlspecialchars(($_GET['filterby']), ENT_QUOTES);
$column = htmlspecialchars(($_GET['column']), ENT_QUOTES);

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'asset_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_id = !empty($_GET['id_location']) ? $_GET['id_location'] : 0;
$_category = !empty($_GET['id_category']) ? $_GET['id_category'] : 0;
$_store = !empty($_GET['id_store']) ? $_GET['id_store'] : 0;
$_dept = !empty($_GET['id_department']) ? $_GET['id_department'] : 0;
$dept = defined('USERDEPT') ? USERDEPT : 0;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$categories[0] ='-- all categories --';
$categories += get_category_list('EQUIPMENT', $dept);

$store[0] ='-- all store type --';
$store += get_store_list();

$department[0] = '-- all department --';
$department += get_department_list();

$_searchby = 'id_department';
$_searchtext = $_dept;


$_searchby = 'id_department';

$_searchtext = $_dept;

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

$row_class = ' class="sort_'.$sort_order.'"';

$order_link = './?mod=report&sub=item&term=list&by=reportgeneralitem&chgord=1&searchby='.$_searchby.'&id_department='.$_searchtext.'&filterby='.$filterby.'&column='.$column.'&page='.$_page.'&ordby=';

?>
<br/>
<div id="submodhead" >
	
<?php


?>
	
	<table>
	<?php if(USERDEPT == 0) {?>
		<tr>
			<td>Filtered by Department </td>
			<td><?php echo build_combo('id_department', $department, $_dept);?> </td>
		</tr>
	<?php } else { ?>
		<tr>
			<td><span hidden>Filtered by Department </span></td>
			<td><select id="id_department" hidden><option value="<?php echo $department[0]; ?>">-- option --</option></select></td>
		</tr>
	<?php } ?>
		<tr>
			<td>Category </td>
			<td><?php echo build_combo('id_category', $categories, $_category);?> </td>
		</tr>
		<tr>
			<td>Store Type </td>
			<td><?php echo build_combo('id_store', $store, $_store);?></td>
		</tr>
		<tr>
			<td><input type="checkbox" id="category" 	value="category"> 	Category 		</td>
			<?php if(USERDEPT == 0) {?>
			<td><input type="checkbox" id="department" 	value="department"> Department		</td>
			<?php } else { ?>
			<td><input type="checkbox" id="department" 	value="department" disabled> Department		</td>
			<?php } ?>
		</tr>
		<tr>
			<td><input type="checkbox" id="brand_name" 	value="brand_no"> 	Brand Name 		</td>
			<td><input type="checkbox" id="model_no" 	value="model_no"> 	Model Number 	</td>
		</tr>
			<td><input type="checkbox" id="status" 		value="status"> 	Status 			</td>
			<td><input type="checkbox" id="issued_to" 	value="issued_to">	Issued To 		</td>
		</tr>
		<tr>
			<td><input type="checkbox" id="location" 	value="location"> 	Location 		</td>
			<td><input type="checkbox" id="cost" 		value="cost"> 		Cost			</td>
		</tr>
		<tr>
			<td><input type="checkbox" id="purchase_date" value="purchase_date"> Purchase Date </td>
			<td><input type="checkbox" id="warranty_end_date" value="warranty_end_date"> Warranty End Date </td>
		</tr>
			<td><input type="checkbox" id="project_comdemned_date" value="project_comdemned_date"> 	Project comdemned Date </td>
			<td><input type="checkbox" id="invoice_no" value="invoice_no">	Invoice No. </td>
		</tr>
		<tr>
			<td><input type="checkbox" id="brief" value="cost"> 		Brief			</td>
			<td><input type="checkbox" id="checkall" value="cost" onchange="return checkAll()"><span id="check_this_out">Check All</span></td>
		</tr>
	</table>
	
	<button class="button" onclick="return call_data()">Display</button>
	

<?php

	
if (! $column || $column == null){ // GET COLUMN FROM URL
	// NO PROCESS HERE
} else {
	echo"<a class='button' href='./?mod=report&sub=item&act=view&term=export&by=reportgeneralitem&filterby=".$filterby."&column=".$column."'>Export</a>";
	echo "<br /><br />";
	//echo "Display By Field : ".$column;
	$var_filter = explode(",", $filterby);
	$data_department = $var_filter[0]; $render_id_department = explode('|', $data_department); 
	$data_category = $var_filter[1] ;$render_id_category = explode('|', $data_category);
	$data_store = $var_filter[2];$render_id_store = explode('|', $data_store);

	$variable = explode(",", $column);
	
	$department 			= $variable[0];
	$category 				= $variable[1];
	$brand_name 			= $variable[2];
	$model_no				= $variable[3];
	$status					= $variable[4];
	$issued_to				= $variable[5];
	$location			 	= $variable[6];
	$cost					= $variable[7];
	$purchase_date 			= $variable[8];
	$warranty_end_date 		= $variable[9];
	$project_comdemned_date	= $variable[10];
	$invoice_no				= $variable[11];
	$brief					= $variable[12];
	
	//echo "<br /><br />----------------------<br />";
	//echo $category. "-". $department . "-" . $brand_name . "-" . $model_no . "-" . $status . "-" . $issued_to . "-" . $location;
	
	//Filtering Status Here
	
		?>
		<div class="clear"></div>
		<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
		<tr height=30>
			<th width=30>No</th>
			<th width=110 <?php echo ($_orderby == 'asset_no') ? $row_class : null ?>> 
				<a href="<?php echo $order_link ?>asset_no">Asset No</a>
			</th>
			<th width=100 <?php echo ($_orderby == 'serial_no') ? $row_class : null ?>>
				<a href="<?php echo $order_link ?>serial_no">Serial No</a>
			</th>
			
			<?php if(!$department){} else {?>
			<?php if (SUPERADMIN){ ?> 
			<th width=110  <?php echo ($_orderby == 'department_name') ? $row_class : null ?> > <a href="<?php echo $order_link ?>department_name">Department</a></th>
			<?php } ?>
			<?php } ?>
			
			
			<?php if(!$category){} else {?>
			<th width=110  <?php echo ($_orderby == 'category_name') ? $row_class : null ?> >
				<a href="<?php echo $order_link ?>category_name">Category</a>
			</th> 
			<?php } ?>
			
			
			<?php if(!$brand_name){} else {?>
			<th width=100 <?php echo ($_orderby == 'brand_name') ? $row_class : null ?> > <a href="<?php echo $order_link ?>brand_name">Brand</a></th>
			<?php } ?>
			<?php if(!$model_no){} else {?>
			<th width=100 <?php echo ($_orderby == 'model_no') ? $row_class : null ?> > <a href="<?php echo $order_link ?>model_no">Model No</a></th>
			<?php } ?>
			<?php if(!$status){} else {?>
			<th width=100 <?php echo ($_orderby == 'status_name') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>status_name">Status</a></th>
			<?php } ?>
			<?php if(!$issued_to){} else {?>
			<th width=100 <?php echo ($_orderby == 'issued_to') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>issued_to">Issued To</a></th>
			<?php } ?>
			<?php if(!$location){} else {?>
			<th width=100 <?php echo ($_orderby == 'location') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>location">Location</a></th>
			<?php } ?>
			<?php if(!$cost){} else {?>
			<th width=100 <?php echo ($_orderby == 'cost') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>cost">Cost</a></th>
			<?php } ?>
			<?php if(!$purchase_date){} else {?>
			<th width=100 <?php echo ($_orderby == 'date_of_purchase') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>date_of_purchase">Purchase Date</a></th>
			<?php } ?>
			<?php if(!$warranty_end_date){} else {?>
			<th width=100 <?php echo ($_orderby == 'warranty_end_date') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>warranty_end_date">Warranty End Date</a></th>
			<?php } ?>
			<?php if(!$project_comdemned_date){} else {?>
			<th width=100 <?php echo ($_orderby == 'date_of_purchase_fmt') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>date_of_purchase_fmt">Project Comdemned Date</a></th>
			<?php } ?>
			<?php if(!$invoice_no){} else {?>
			<th width=100 <?php echo ($_orderby == 'invoice') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>invoice">Invoice No</a></th>
			<?php } ?>
			<?php if(!$brief){} else {?>
			<th width=100 <?php echo ($_orderby == 'brief') ? $row_class : null ?> >
			<a href="<?php echo $order_link ?>brief">Brief</a></th>
			<?php } ?>
			<th width=50>Action</th>
		</tr>

		<?php
		
		
		$item_f = array('id_category'=>$render_id_category[1],'id_store'=>$render_id_store[1], 'id_department'=>$render_id_department[1]);
		
		
		$rs = get_item_for_generalreportitem($_orderby, $sort_order, $_start, $_limit, $dept, false, $item_f);
		//echo $rs;
		$counter = $_start+1;
		
			while ($rec = mysql_fetch_array($rs))
			{
				
				$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
				$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
				$dept_col = (SUPERADMIN) ? "<td>$rec[department_name]</td>" : '';
				?>
				<tr <?php echo $_class?>>
				<td align='right'><?php echo $counter?></td>
				<td><?php echo $rec['asset_no'];?></td>
				<td><?php echo $rec['serial_no'];?></td>
				<?php if(!$category){} else {?>
				<td><?php echo $rec['category_name'];?></td>
				<?php } ?>
				<?php if(!$department){} else {?>
				<?php echo $dept_col;?>
				<?php }?>
				<?php if(!$brand_name){} else {?>
				<td><?php echo $rec['brand_name'];?></td>
				<?php } ?>
				<?php if(!$model_no){} else {?>
				<td ><?php echo $rec['model_no'];?></td>
				<?php } ?>
				<?php if(!$status){} else {?>
				<td ><?php echo $rec['status_name'];?></td>
				<?php } ?>
				<?php if(!$issued_to){} else {?>
				<td ><?php echo $rec['issued_to_name'];?></td>
				<?php } ?>
				<?php if(!$location){} else {?>
				<td ><?php echo $rec['location_name'];?></td>
				<?php } ?>
				<?php if(!$cost){} else {?>
				<td ><?php echo $rec['cost'];?></td>
				<?php } ?>
				<?php if(!$purchase_date){} else {?>
				<td ><?php echo $rec['date_of_purchase_fmt'];?></td>
				<?php } ?>
				<?php if(!$warranty_end_date){} else {?>
				<td ><?php echo $rec['warranty_end_date_fmt'];?></td>
				<?php } ?>
				<?php if(!$project_comdemned_date){} else {?>
				<td ><?php echo $rec['date_of_purchase_fmt'];?></td>
				<?php } ?>
				<?php if(!$invoice_no){} else {?>
				<td ><?php echo $rec['invoice'];?></td>
				<?php } ?>
				<?php if(!$brief){} else {?>
				<td ><?php echo $rec['brief'];?></td>
				<?php } ?>
				<td align='center' nowrap>
					<a href='?mod=item&act=view&id=<?php echo $rec['id_item'];?>' title='view'><img class='icon' src='images/loupe.png' alt='view' ></a>
				</td>
				</tr>
				<?php
				$counter++;
			  
			}
		
		
		echo '<tr ><td colspan=20 class="pagination">';
		echo make_paging($_page, $total_page, $base_url.'/?mod=report&sub=item&act=view&term=list&by=reportgeneralitem&filterby='.$filterby.'&column='.$column.'&page=');
		echo  '</td></tr></table><br/>';

	
    
} // END GET COLUMN

?>


<script>
	function call_data(){
		
		var id_department = document.getElementById('id_department').value ;
		var department 	= document.getElementById('department').checked	;
		
		var id_category = document.getElementById('id_category').value 	;
		var id_store	= document.getElementById('id_store').value 	;
		
		var category 	= document.getElementById('category').checked 	;
		var brand_name 	= document.getElementById('brand_name').checked ; 
		var model_no	= document.getElementById('model_no').checked	;
		var status		= document.getElementById('status').checked		;
		var issued_to	= document.getElementById('issued_to').checked	;
		var location	= document.getElementById('location').checked	;
		var cost		= document.getElementById('cost').checked		;
		var purchase_date 	= document.getElementById('purchase_date').checked	;
		var warranty_end_date 	= document.getElementById('warranty_end_date').checked ; 
		var project_comdemned_date	= document.getElementById('project_comdemned_date').checked	;
		var invoice_no		= document.getElementById('invoice_no').checked		;
		var brief	= document.getElementById('brief').checked	;
		
		
		if( id_department	> 0){ data_department 	= id_department;} else {data_department  = <?php echo USERDEPT;?>;}
		
		if( department 	== true){ department_field = "department";} else {department_field = "";}
		
		
		if( id_category		> 0){ data_category 	= id_category;} else {data_category 	 = 0;}
		if( id_store		> 0){ data_store 		= id_store;} else {data_store 	 = 0;}
		
		
		if( category 	== true){ category_field = "category";} else {category_field 	 = "";}
		if( brand_name 	== true){ brand_name_field = "brand_name";} else {brand_name_field = "";}
		if( model_no	== true){ model_no_field = "model_no";} else {model_no_field = "";}
		if( status	 	== true){ status_field = "status";} else {status_field = "";}
		if( issued_to 	== true){ issued_to_field = "issued_to";} else {issued_to_field  = "";}
		if( location	== true){ location_field = "location";} else {location_field = "";}
		if( cost	== true){ cost_field = "cost";} else {cost_field = "";}
		if( purchase_date 	== true){ purchase_date_field = "purchase_date";} else {purchase_date_field = "";}
		if( warranty_end_date 	== true){ warranty_end_date_field = "warranty_end_date";} else {warranty_end_date_field = "";}
		if( project_comdemned_date	== true){ project_comdemned_date_field = "project_comdemned_date";} else {project_comdemned_date_field = "";}
		if( invoice_no	 	== true){ invoice_no_field = "invoice_no";} else {invoice_no_field = "";}
		if( brief 	== true){ brief_field = "brief";} else {brief_field  = "";}
		
		//GET FILTERING BY HERE
		var item_title 	= 	"department|"+data_department+",category|"+data_category+",store|"+data_store; 
		
		
		//GET COLUMN DISPLAY
		var column = department_field+","+category_field+","+brand_name_field+","+model_no_field+","+status_field+","+issued_to_field+","+location_field+","+cost_field+","+purchase_date_field+","+warranty_end_date_field+","+project_comdemned_date_field+","+invoice_no_field+","+brief_field;
		
		
		window.location.href="<?php echo $base_url;?>/?mod=report&sub=item&act=view&term=list&by=reportgeneralitem&filterby="+item_title+"&column="+column;
		
		
	}
	
	
	function checkAll(){
		var checkall 	= document.getElementById('checkall').checked 	;
		
		if(checkall == true){
			document.getElementById('category').checked = true;
			document.getElementById('brand_name').checked = true  ; 
			document.getElementById('model_no').checked = true 	;
			document.getElementById('status').checked = true 		;
			document.getElementById('issued_to').checked = true 	;
			document.getElementById('location').checked = true 	;
			document.getElementById('cost').checked = true 		;
			document.getElementById('purchase_date').checked = true 	;
			document.getElementById('warranty_end_date').checked = true  ; 
			document.getElementById('project_comdemned_date').checked = true 	;
			document.getElementById('invoice_no').checked = true 		;
			document.getElementById('brief').checked = true;
			document.getElementById('check_this_out').innerHTML = "Uncheck All";
		} else if (checkall == false) {
			document.getElementById('category').checked = false;
			document.getElementById('brand_name').checked = false  ; 
			document.getElementById('model_no').checked = false 	;
			document.getElementById('status').checked = false 		;
			document.getElementById('issued_to').checked = false 	;
			document.getElementById('location').checked = false 	;
			document.getElementById('cost').checked = false 		;
			document.getElementById('purchase_date').checked = false 	;
			document.getElementById('warranty_end_date').checked = false  ; 
			document.getElementById('project_comdemned_date').checked = false 	;
			document.getElementById('invoice_no').checked = false 		;
			document.getElementById('brief').checked = false;
			document.getElementById('check_this_out').innerHTML = "Check All";
		
		}
	
	}
</script>