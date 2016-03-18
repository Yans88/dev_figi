<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

if (empty($_REQUEST['id'])){
	header('location: ?mod=report&sub=item&act=view&term=stock-take&by=handheld');
	exit;
}
$id = $_REQUEST['id'];
$page = (!empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$limit = 20;
$start = 0;
// get stock take sessions
$take_info = array();
$query = "SELECT * FROM `stock_takes` WHERE id_take=$id";
$res = @mysql_query($query);
if ($res && mysql_num_rows($res)>0){
	$take_info= mysql_fetch_array($res); 
}

if (isset($_GET['act']) && $_GET['act'] == 'export'){
	$crlf = "\n";
	
	$res = @mysql_query("SELECT * FROM `stock_taken_item` WHERE id_take=$id ORDER BY take_date DESC ");

	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_stock-take_by_handheld_detail-$id.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo 'id_item,id_location,take_date,id_take,old_location'. $crlf;

	if ($res){
		while ($rec = mysql_fetch_array($res)) 
			echo "$rec[id_item],$rec[id_location],$rec[take_date],$rec[id_take],$rec[old_location]$crlf";
	}
	ob_end_flush();
	exit;	
}

$total_item = 0;
$take_items = array();
$res = @mysql_query("SELECT COUNT(*) FROM `stock_taken_item` WHERE id_take=$id");
if ($res){
	$rec = mysql_fetch_row($res); 
	$total_item	= $rec[0];
}

$pages = ($total_item>0) ? round($total_item/ $limit) : 0;
$start = ($page - 1) * $limit;
$query = "SELECT sti.*, 
			(SELECT location_name FROM location l WHERE l.id_location = sti.id_location) current_location, 
			(SELECT location_name FROM location l WHERE l.id_location = sti.old_location) previous_location 
			FROM `stock_taken_item` sti WHERE id_take=$id ORDER BY take_date DESC LIMIT $start, $limit";
$res = @mysql_query($query);
if ($res){
	while ($rec = mysql_fetch_array($res)) 
		$takes[] = $rec;
}


?>
<script>
function export_this(){
	location.href = "./?mod=report&sub=item&act=export&term=stock-take&by=handheld_detail";
}
</script>

<h3>Stock Take Report by Handheld (Detail)</h3>
<br>
<table class="report middle" width="500" cellpadding=2 cellspacing=1>
<tr class="normal">
	<td colspan=2>Stock Take Session Date</td>
	<td colspan=2><?php echo $take_info['take_date'];?></td>
</tr>
<tr>
	<td colspan=3><h3>Stock Taken Item List<h3></td>
	<td align="right"><a class="button" href="./?mod=report&sub=item&act=view&term=stock-take&by=handheld">back</a></td>
</tr>
<tr><th width=50 rowspan=2>ID Item</th><th width=120 rowspan=2>Take Date</th><th colspan=2>Location</th></tr>
<tr><th>Current</th><th>Previous</th></tr>

<?php
$row = 0;
if (count($takes)>0){
	foreach ($takes as $rec){
		$row++;
		$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
		echo "<tr $class><td align='center'>$rec[id_item]</td><td align='center'>$rec[take_date]</td><td>$rec[current_location]</td><td>$rec[previous_location]</td></tr>";
	}
	// page navigation
	if ($pages > 1){
		$lnk1 = "./?mod=report&sub=item&act=view&term=stock-take&by=handheld&id=$id&page=";
		$prev = $lnk1 . ($page > 1) ? $page-1 : 1;
		$next = $lnk1 . ($page < $pages) ? $page+1 : $pages;
		echo "<tr class='normal'><td colspan=4 align=\"center\"><a href=\"$prev\">&lt;&lt;</a> &nbsp; <a href=\"$next\">&gt;&gt;</a> </td></tr>";
	}
} else
	echo "<tr class='normal'><td colspan=4 align='center' class='normal'><p>Data is not avalable!</p></td></tr>";

?>
</table>
<br/>
<br/>
