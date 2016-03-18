<?php

$category_type = 'EQUIPMENT';
$this_year = date('Y');
$this_month = date('n');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;
$_page = !empty($_GET['page']) ? $_GET['page'] :  1;
$_cat = !empty($_GET['cat']) ? $_GET['cat'] : 0;

$_ewyr = $this_year;
$_ewmo = $this_month;
if (!empty($_GET['ew'])){
	$_ewyr = intval(substr($_GET['ew'], 0, 4));
	$_ewmo = intval(substr($_GET['ew'], 4, 2));
}

$categories = get_category_list($category_type);

$query  = "SELECT COUNT(*) 
			FROM item 
			WHERE id_category = '$_cat' AND 
            YEAR(warranty_end_date) = $_ewyr  AND MONTH(warranty_end_date) = $_ewmo  ";			
$rs = mysql_query($query);
//echo mysql_error().$query;
if ($rs && mysql_num_rows($rs)>0){
    $rec = mysql_fetch_row($rs);
    $total_items = $rec[0];

    $limit = RECORD_PER_PAGE;
    $start = 0;
    $total_page = round($total_items / $limit);
    if ($_page<1) $_page = 1;
    if ($_page>$total_page) $_page = $total_page;
    $start = ($_page-1) * $limit;
    $query  = "SELECT asset_no,serial_no, model_no, brand_name, status_name 
                FROM item 
                LEFT JOIN brand ON brand.id_brand = item.id_brand  
                LEFT JOIN status ON status.id_status = item.id_status 
                WHERE id_category = '$_cat' AND 
                YEAR(warranty_end_date) = $_ewyr  AND MONTH(warranty_end_date) = $_ewmo  
                ORDER BY serial_no 
                LIMIT $start, $limit";			
    $rs = mysql_query($query);
    //echo $query.mysql_error();
}	

$year_month = $month_names[$_ewmo] . ', ' . $_ewyr;
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'No,Asset No,Serial No,Brand,Model No,Status'.$crlf;
    while ($rec = mysql_fetch_assoc($rs))
		$content .= "$row,$rec[asset_no],$rec[serial_no],$rec[brand_name],$rec[model_no],$rec[status_name]".$crlf;
	//$year_month = rawurlencode($year_month);
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=\"list_item_warranty_expire_on-$year_month.csv\"");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.submit();
}
</script>
<form method="post">
<input type="hidden" name="act">
</form>
<h2>Items with Warranty Expire on $year_month</h2>
<br/>
<table class="report item-list" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=10 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr>
	<th>No</th>
	<th>Asset No</th>
	<th>Serial No</th>
	<th>Brand</th>
	<th>Model No</th>
	<th>Status</th>
</tr>
HEAD;
if ($total_items > 0){
    $row = $start;
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs)){
            $class = ($row++ % 2 == 0) ? 'class="alt"' : 'class="normal"';
            echo <<<ROW
<tr $class>
	<td>$row</td>
	<td>$rec[asset_no]</td>
	<td>$rec[serial_no]</td>
	<td>$rec[brand_name]</td>
	<td>$rec[model_no]</td>
	<td>$rec[status_name]</td>
</tr>
ROW;
        }
        echo '<tr ><td colspan=8 class="pagination">';
        echo make_paging($_page, $total_page, './?mod=report&sub=item&term=warranty&act=list_item&ew='.$_GET['ew'].'&cat='.$_cat.'&page=');
        echo  '</td></tr></table><br/>';
        
    }
}// total_items>0
else 
    echo '<tr><td colspan=6 class="error">Data is not available!</td></tr>';
?>
</table>
<br>&nbsp;
<br>