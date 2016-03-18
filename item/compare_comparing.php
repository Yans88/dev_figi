<?php
if (!defined('FIGIPASS')) exit;
$detp = USERDEPT;

/*
ext fields - internal fields
Zone - Ignore
Asset Code - Asset No
Asset Name - Brief
Serial No - Serial No
Location Name - Location
Purchase Date - Purchase Date
Purchase Value - Cost
Status Name - Status [ Lost / Missing,  Retired / Condemned ]
for vendor:
ext Blank = int Unknown
for location:
ext Blank = int Unknown
*/
function internal_count()
{
	$result = 0;
	$query = " SELECT *  
				FROM item II 
				LEFT JOIN category IC ON II.id_category = IC.id_category 
				LEFT JOIN location IL ON II.id_location = IL.id_location 
				WHERE category_type = 'EQUIPMENT'";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	if ($rs)
		$result = mysql_num_rows($rs);
	return $result;
}

function external_count()
{
	$result = 0;
	$query = " SELECT * FROM item_comparison_data ";
	$rs = mysql_query($query);
	//echo mysql_error().$query;
	if ($rs)
		$result = mysql_num_rows($rs);
	return $result;
}

function comparison_comparing($dept = 0)
{
	$filter_dept = null;
	if ($dept > 0)
		$filter_dept = ' AND II.id_department = '.$dept;
	$no_of_matches = 0;
	$query = <<<QUERY1
SELECT MIN(data_compare) AS data_compare, asset_no, serial_no, asset_name, location_name, purchase_date, status_name, purchase_value, vendor_name, id_item    
FROM (
	SELECT 'internal' AS data_compare, asset_no, serial_no, brief AS asset_name, location_name, date_of_purchase AS purchase_date, status_name, cost AS purchase_value, vendor_name, II.id_item    
	FROM item II 
	LEFT JOIN category IC ON II.id_category = IC.id_category 
	LEFT JOIN location IL ON II.id_location = IL.id_location 
	LEFT JOIN status S ON II.id_status = S.id_status 
	LEFT JOIN vendor V ON II.id_vendor = V.id_vendor 
	WHERE category_type = 'EQUIPMENT' $filter_dept 
	UNION ALL
	SELECT 'external' AS data_compare, asset_code AS asset_no, serial_code AS serial_no, asset_name, location_name, purchase_date, status_name, purchase_value, vendor_name , 0 AS id_item   
	FROM item_comparison_data 
	) tmp 
GROUP BY asset_no, serial_no, asset_name, location_name, purchase_date, status_namea,purchase_value, vendor_name   
HAVING COUNT(*) = 2 
ORDER BY asset_no
QUERY1;
	$rs = mysql_query($query);
	if ($rs)
		$no_of_matches = mysql_num_rows();

	$query = <<<QUERY2
SELECT MIN(data_compare) AS data_compare, asset_no, serial_no, asset_name, location_name, purchase_date, status_name, purchase_value, vendor_name, id_item   
FROM (
	SELECT 'internal' AS data_compare, asset_no, serial_no, brief AS asset_name, location_name, date_of_purchase AS purchase_date, status_name, cost AS purchase_value, vendor_name, II.id_item  
	FROM item II 
	LEFT JOIN category IC ON II.id_category = IC.id_category 
	LEFT JOIN location IL ON II.id_location = IL.id_location 
	LEFT JOIN status S ON II.id_status = S.id_status 
	LEFT JOIN vendor V ON II.id_vendor = V.id_vendor 
	WHERE category_type = 'EQUIPMENT' $filter_dept 
	UNION ALL
	SELECT 'external' AS data_compare, asset_code AS asset_no, serial_code AS serial_no, asset_name, location_name, purchase_date, status_name, purchase_value, vendor_name, 0 AS id_item   
	FROM item_comparison_data 
	) tmp 
GROUP BY asset_no, serial_no, asset_name, location_name, purchase_date, status_name, purchase_value, vendor_name   
HAVING COUNT(*) = 1 
ORDER BY asset_no
QUERY2;
//LIMIT $offset, $limit 

	$rs = mysql_query($query);
//	echo mysql_error().$query;
	$differences= array('both' => array(), 'asset' => array(), 'serial' => array());
	$missing= array('internal' => array(),'external' => array());
	$prev_row = array();
	$no_of_missing = 0;
	$no_of_differences= 0;
	while ($row = mysql_fetch_assoc($rs)){
	//	echo  implode(', ', array_values($row))."\r\n";
		$keep_row = true;
		if (!empty($prev_row)){
			// exist on both tables, meant it's a pear
			$is_asset_equal = strtolower($prev_row['asset_no'])==strtolower($row['asset_no']);
			$is_serial_equal = strtolower($prev_row['serial_no'])==strtolower($row['serial_no']);
			if ($is_asset_equal||$is_serial_equal){ 
				if ($is_asset_equal&&$is_serial_equal){ 
					// both asset & serial match, differences on the other fields
					$differences['both'][$row['asset_no'].'~'.$row['serial_no']][] = $prev_row;
					$differences['both'][$row['asset_no'].'~'.$row['serial_no']][] = $row;
				} else if ($is_asset_equal){ // same asset only
					$differences['asset'][$row['asset_no']][] = $prev_row;
					$differences['asset'][$row['asset_no']][] = $row;
				} else {// same asset only
					$differences['serial'][$row['serial_no']][] = $prev_row;
					$differences['serial'][$row['serial_no']][] = $row;
				}
				// clear previous row, get next row as previous  row
				$keep_row = false;
				$no_of_differences++;
			} else { // missing, not a pair between current - previous row
				$from_source = ($row['data_compare']=='internal') ? 'external' : 'internal';
				$missing[$from_source][] = $prev_row; 
				// prev row is missing from a table, if exist in internal mean doesn't exist in external and otherwise
				// need to keep the row to rowompare with next row
				$keep_row = true;
				$no_of_missing++;
			}
		}
		if ($keep_row) $prev_row = $row;
		else $prev_row = array();
	}
	$stats['matches'] = $no_of_matches;
	$stats['missing'] = $no_of_missing;
	$stats['differences'] = $no_of_differences;
	return array($stats, $missing, $differences);
}
$dept = USERDEPT;
$_limit = 20;//RECORD_PER_PAGE;
$_start = 0;
$_page = 1;
$sort_order = 'asc';
$int_item = internal_count();
$ext_item = external_count();
$row_class = ' class="sort_'.$sort_order.'"';

if (isset($_GET['what_to_check'])) $what_to_check = $_GET['what_to_check'];
else $what_to_check = isset($_POST['what_to_check']) ? $_POST['what_to_check'] : 'missing';
if (isset($_GET['missing_from'])) $missing_from= $_GET['missing_from'];
else $missing_from = isset($_POST['missing_from']) ? $_POST['missing_from'] : 'internal';
if (isset($_GET['different_in'])) $different_in= $_GET['different_in'];
else $different_in = isset($_POST['different_in']) ? $_POST['different_in'] : 'asset';

if (isset($_GET['page'])) $_page= $_GET['page'];
else $_page = isset($_POST['page']) ? $_POST['page'] : 1;
if (isset($_GET['display'])) $is_display= true;
else $is_display = isset($_POST['display']) ? true : false;
$is_download = isset($_POST['download']) ? true : false;
if ($is_download) $is_display = true;

if ($is_display){
	$total_item = comparison_count();

	
	$result  = comparison_comparing();
	$stats = $result[0];
	if ($what_to_check == 'missing'){
		$items = $result[1];
		$items = $items[$missing_from];
	} else {
		$items = $result[2];
		if ($different_in=='asset') $items = $items['asset'];
		else if ($different_in=='serial') $items = $items['serial'];
		else $items = $items['both'];
	}

	$item_count = count($items);
	$total_item = $item_count;
	$total_page = ceil($total_item/$_limit);
	if ($_page > $total_page) $_page = 1;
	if ($_page > 0)	$_start = ($_page-1) * $_limit;

	if ($is_download){
		require_once 'comparing_export.php';
		exit();
	}

	$items = array_splice($items, $_start, $_limit);
}
?>
<br/>
<div id="submodhead" >
<div align="left" valign="middle" class="leftlink" >
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
		$.post("item/item_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", dept: ""+dept+"", searchBy: ""+$('#searchby').val()+""}, function(data){
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

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
#itemlist { width: 900px }
</style>
<div>
<p>
Date of comparison: <?php echo date('D, d M Y'); ?><br>
No of equipment in FiGi: <?php echo $int_item; ?><br> 
No of equipment from Vendor: <?php echo $ext_item; ?><br>
</p>
<form id="compareform" method="post" action="<?php echo '?mod=item&sub=compare&act=comparing'?>">
<input type="radio" id="missing_check" name="what_to_check" value="missing" <?php echo ($what_to_check=='missing')?'checked':null ?> > <label for="missing_check">Missing records </label>
<input type="radio" id="differences_check" name="what_to_check" value="differences" <?php echo ($what_to_check=='differences')?'checked':null ?> > <label for="differences_check">Incomplete records</label>
<div id="for_missing" >
<input type="radio" id="missing_internal" name="missing_from" value="internal" <?php echo ($missing_from=='internal')?'checked':null ?> > <label for="missing_internal">Missing from Internal DB</label>
<input type="radio" id="missing_external" name="missing_from" value="external" <?php echo ($missing_from=='external')?'checked':null ?> > <label for="missing_external">Missing from Vendor</label>
</div>

<div style="display:none" id="for_differences" >
<input type="radio" id="differ_asset" name="different_in" value="asset" <?php echo ($different_in=='asset')?'checked':null ?> > <label for="differ_asset">Different in Asset No</label>
<input type="radio" id="differ_serial" name="different_in" value="serial" <?php echo ($different_in=='serial')?'checked':null ?> > <label for="differ_serial">Different in Serial No</label>
<input type="radio" id="differ_others" name="different_in" value="others" <?php echo ($different_in=='others')?'checked':null ?> > <label for="differ_others">Different in Other Fields</label>
</div>
<br>
<button name="display"> Display </button> <button name="download"> Download </button>
</form>
</div>
<div class="clear"></div>
<br>
<?php
if ($is_display && $item_count> 0) {
	if ($what_to_check=='missing'){
		echo '<div>Displaying missing item from '.$missing_from.' database: '.$item_count.' </div>';
	} else if ($what_to_check=='differences'){
		echo '<div>Displaying incorrect/mismatch item: '.$item_count.' </div>';
	}
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=0 cellspacing=0 class="itemlist" >
<tr height=30>
  <th width=30>No</th>
  <th width=120> Asset No</th>
  <th width=120> Serial No</th>
  <th >Asset Name</th>
  <th width=100 >Purchase Date</th>
  <th width=100 >Purchase Value</th>
  <th width=100 >Status</th>
  <th>Vendor</th>
  <th>Location</th>
 <?php
 if ($what_to_check=='differences') 
 	echo "<th width=60>Source</th>";
 ?>
</tr>

<?php
$counter = $_start+1;
foreach ($items as $rec) {
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	if ($what_to_check=='differences'){
		$r = 0;
		//swap the order
		if ($rec[0]['data_compare']!='internal'){
			$keep = $rec[0];
			$rec[0] = $rec[1];
			$rec[1] = $keep;
		}
		$rowspan = (count($rec)>1) ? 'rowspan=2' : null;
		$bg_asset = '';
		if ($rec[0]['asset_no']!=$rec[1]['asset_no'])
			$bg_asset = 'style="background-color: #FCC"';
		$bg_serial = '';
		if ($rec[0]['serial_no']!=$rec[1]['serial_no'])
			$bg_serial = 'style="background-color: #FCC"';
		$bg_name = '';
		if (strtolower($rec[0]['asset_name'])!=strtolower($rec[1]['asset_name']))
			$bg_name = 'style="background-color: #FCC"';
		$bg_location= '';
		if (strtolower($rec[0]['location_name'])!=strtolower($rec[1]['location_name'])){
			$bg_location = 'style="background-color: #FCC"';
			if (empty($rec[1]['location_name']) && strtolower($rec[0]['location_name'])=='unkown')
				$bg_vendor = ''; // set as equal, so ignore/reset the bg color
		}
		$bg_vendor= '';
		if (strtolower($rec[0]['vendor_name'])!=strtolower($rec[1]['vendor_name'])){
			$bg_vendor = 'style="background-color: #FCC"';
			if (empty($rec[1]['vendor_name']) && strtolower($rec[0]['vendor_name'])=='unkown')
				$bg_vendor = ''; // set as equal, so ignore/reset the bg color
		}
		$bg_value= '';
		if ($rec[0]['purchase_value']!=$rec[1]['purchase_value'])
			$bg_value = 'style="background-color: #FCC"';
		$bg_date = '';
		if ($rec[0]['purchase_date']!=$rec[1]['purchase_date'])
			$bg_date = 'style="background-color: #FCC"';
		$bg_status = '';
		if (strpos($rec[0]['status_name'], 'Available')>-1&&substr($rec[0]['status_name'],0,9)!=substr($rec[1]['status_name'],0,9))
			$bg_status = 'style="background-color: #FCC"';
		else if (strpos($rec[0]['status_name'], 'Lost'>-1)&&substr($rec[0]['status_name'],0,4)!=substr($rec[1]['status_name'],0,4))
			$bg_status = 'style="background-color: #FCC"';
		/*
		else if (strtolower($rec[0]['status_name'])!=strtolower($rec[1]['status_name']))
			$bg_status = 'style="background-color: #FCC"';
		*/
		foreach ($rec as $row){
			$span = null;
			echo "<tr $_class>";
			$edit_link = '?mod=item&act=edit&id='.$row['id_item'];
			$asset_link = '<a href="'.$edit_link.'" target="_blank">'.$row['asset_no'].'</a>';
			if (!empty($rowspan)&&($r==0))
				  echo "<td align='right' $rowspan>$counter</td>";
			if ($different_in=='asset'){
				if ($r == 0)
					$span = "<td $rowspan>$asset_link</td> <td $bg_serial>$row[serial_no]</td>";
				else
					$span = "<td $bg_serial>$row[serial_no]</td>";
			} else if ($different_in=='serial'){
				if ($r == 0)
					$span = "<td $bg_asset>$asset_link</td> <td $rowspan>$row[serial_no]</td>";
				else
					$span = "<td $bg_asset>$row[asset_no]</td> ";
			} else { // both
				if ($r == 0)
					$span = "<td $rowspan>$asset_link</td> <td $rowspan>$row[serial_no]</td>";
			}
			echo <<<DATAD
		$span
		<td $bg_name>$row[asset_name]</td>
		<td $bg_date>$row[purchase_date]</td>
		<td $bg_value>$row[purchase_value]</td>
		<td $bg_status>$row[status_name]</td>
		<td $bg_vendor>$row[vendor_name]</td>
		<td $bg_location>$row[location_name]</td>
		<td >$row[data_compare]</td>
DATAD;
			$r++;
		}
		echo '</tr>';
	} else {
	
		echo <<<DATA
	<tr $_class>
	<td align="right">$counter</td>
	<td>$rec[asset_no] $rec[id_item]</td> 
	<td>$rec[serial_no]</td>
	<td>$rec[asset_name]</td>
	<td>$rec[purchase_date]</td>
	<td>$rec[purchase_value]</td>
	<td >$rec[status_name]</td>
	<td >$row[vendor_name]</td>
	<td >$rec[location_name]</td>
	</tr>
DATA;
	}
  	$counter++;
}

echo '<tr ><td colspan=11 class="pagination">';
echo make_paging($_page, $total_page, './?mod=item&sub=compare&act=comparing&what_to_check='.$what_to_check.'&missing_from='.$missing_from.'&different_in='.$different_in.'&display=1&page=');
echo  '</td></tr></table><br/>';

} else if ($is_display) { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
$('#searchtext').focus();
$('input[name=what_to_check]').change(function(){
	var what = $(this).val();
	if (what=='missing'){
		$('#for_differences').hide();
		$('#for_missing').show();
	} else {
		$('#for_missing').hide();
		$('#for_differences').show();
	}
})
$('input[name=what_to_check]:checked').trigger('change');
</script>
<br><br>
<br><br>

