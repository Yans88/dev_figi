<?php
$category_type = 'EQUIPMENT';
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

$page = (!empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$limit = 20;
$start = 0;
// get stock take sessions
$res = @mysql_query("SELECT COUNT(*) FROM `stock_takes` ");
if ($res){
	$rec = mysql_fetch_row($res); 
	$total_take = $rec[0];
}
$pages = ($total_take>0) ? round($total_take / $limit) : 0;
$start = ($page - 1) * $limit;
$takes = array();
$res = @mysql_query("SELECT * FROM `stock_takes` ORDER BY take_date DESC LIMIT $start, $limit");
if ($res){
	while ($rec = mysql_fetch_array($res)) 
		$takes[] = $rec;
}

if (isset($_GET['act']) && $_GET['act'] == 'export'){
	$crlf = "\n";
	$content = 'id_take,take_date,id_department,id_user'. $crlf;
	$res = @mysql_query("SELECT * FROM `stock_takes` ORDER BY take_date DESC ");

	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=item_stock-take_by_handheld.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $content;
	while ($rec = mysql_fetch_array($res)){
		echo "$rec[id_take],'$rec[take_date]',$rec[id_department],$rec[id_user]$crlf";
	}
	ob_end_flush();
	exit;	
}

?>
<script>
function export_this(){
	location.href = "./?mod=report&sub=item&act=export&term=stock-take&by=handheld";
}
</script>

<h3>Stock Take Report by Handheld</h3>
<table class="report middle" width="280" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=2><h3>Stock Take Session List<h3></td>
</tr>
<tr><th>Stock Take Session Date</th><th width="80">Changes</th></tr>

<?php
$row = 0;
if (count($takes)>0){
	foreach ($takes as $rec){
		$row++;
		$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
		$lnk1 = '?mod=report&sub=item&act=view&term=stock-take&by=handheld_detail&id='.$rec['id_take'];
		echo "<tr $class><td align='center'><a href=\"$lnk1\">$rec[take_date]</a></td><td align='center'>$rec[changes]</td></tr>";
	}
	// page navigation
	if ($pages>1){
		$lnk1 = "./?mod=report&sub=item&act=view&term=stock-take&by=handheld&id=$id&page=";
		$prev = $lnk1 . (($page > 1) ? $page-1 : 1);
		$next = $lnk1 . (($page < $pages) ? $page+1 : $pages);
		echo "<tr class='normal'><td colspan=2 align=\"center\"><a href=\"$prev\">&lt;&lt;</a> &nbsp; <a href=\"$next\">&gt;&gt;</a> </td></tr>";
	}
} else
	echo "<tr class='normal'><td colspan=2 align='center'><br>Data is not avalable!<br>&nbsp;</td></tr>";

?>
</table>
<br/>
<br/>
