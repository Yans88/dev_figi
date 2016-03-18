<?php
require_once('item/item_util.php');

$category_type = 'CONSUMABLE';
$this_year = date('Y');
$this_month = date('n');

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$_ewyr = $this_year;
$_ewmo = $this_month;
if (!empty($_GET['ew'])){
	$_ewyr = intval(substr($_GET['ew'], 0, 4));
	$_ewmo = intval(substr($_GET['ew'], 4, 2));
}

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$categories = get_category_list($category_type);
	
$query  = "SELECT *, date_format(trx_time, '%e-%b-%Y %H:%i') trx_time 
			FROM consumable_item_out cio 
			LEFT JOIN consumable_item_out_list ciol ON ciol.id_trx = cio.id_trx 
			LEFT JOIN consumable_item ci ON ci.id_item = ciol.id_item 
			LEFT JOIN category c ON c.id_category = ci.id_category 
			WHERE YEAR(trx_time) = $_year AND MONTH(trx_time) = $_ewmo AND ci.id_category IS NOT NULL  
			ORDER BY category_name ASC, trx_time DESC";			
$rs = mysql_query($query);
//echo mysql_error().$query;

if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\r\n";
	$content = 'Transaction No,Transaction Date,User Name,Item Code,Item Name,Quantity'. $crlf;

	if ($rs && mysql_num_rows($rs)>0)
		while ($rec = mysql_fetch_assoc($rs)){
			$content .= $rec['id_trx'] . ',' . $rec['trx_time'] . ',"' . $rec['user_name'] . '",';
			$content .= $rec['item_code'] . ',"' . $rec['item_name'] . '",' . $rec['quantity'] . $crlf;
	}
	
	ob_clean();
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=consumable_transaction-$_year-$_ewyr-$_ewmo.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

$year_month = $month_names[$_ewmo] . ', ' . $_ewyr;
echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.submit();
	frm.act.value='';
}
</script>
<form method="Post">
<input type=hidden name=act>
</form>

<h2>Usage Transaction List on $year_month</h2>
<br/>
<table class="report" width="600" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4><h3>In Terms Of Category<h3></td>
	<td colspan=10 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr>
	<th width=60>Trx. No.</th>
	<th width=110>Trx. Date</th>
	<th>User Name</th>
	<th>Item Code</th>
	<th>Item Name</th>
	<th>Quantity</th>
</tr>
HEAD;
$row = 1;
$currtrx = -1;
if ($rs && mysql_num_rows($rs)>0){
	while ($rec = mysql_fetch_assoc($rs)){
		if ($currtrx != $rec['id_trx']) {
			$class = ($row++ % 2 == 0) ? 'class="alt"' : 'class="normal"';
			echo <<<ROW
<tr $class>
	<td align="left">CUR$rec[id_trx] </td>
	<td align="center">$rec[trx_time]</td>
	<td>$rec[user_name]</td>
	<td class="rowgrp">$rec[item_code]</td>
	<td class="rowgrp">$rec[item_name]</td>
	<td class="rowgrp" align="center">$rec[quantity]</td>
</tr>
ROW;
		} else
			echo <<<ROWA
<tr $class>
	<td colspan=3></td>
	<td class="rowgrp">$rec[item_code]</td>
	<td class="rowgrp">$rec[item_name]</td>
	<td class="rowgrp" align="center">$rec[quantity]</td>
</tr>
ROWA;
		$currtrx = $rec['id_trx'];
	}
}
?>
</table>
&nbsp;<br/>