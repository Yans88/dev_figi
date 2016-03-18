<?php

$category_type = 'CONSUMABLE';
$today = date('Y-M-d');

require_once('item/item_util.php');

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$categories = get_category_list($category_type, USERDEPT);

$summary = array();
$_display = true;

$query  = " SELECT ci.item_code, ci.item_name, 
            (SELECT SUM(cii.quantity) FROM consumable_item_in cii WHERE cii.id_item=ci.id_item) item_in,
            (SELECT SUM(quantity) FROM consumable_item_out_list cio WHERE cio.id_item=ci.id_item) item_out
            FROM consumable_item ci 
            GROUP BY ci.id_item
            ORDER BY item_in ASC";
$res = mysql_query($query);
$i = 0;
while ($rec = mysql_fetch_assoc($res)){
    $summary[] = $rec;
}

if (isset($_GET['export']) && $_GET['export'] == 1){
	$crlf = "\r\n";
    $fdel = ',';
	$content = 'Item Code, Item Name, Purchased, Issued Out' .  $crlf;

	foreach ($summary as $rec){ // baris
        $rec['item_in'] = (empty($rec['item_in'])) ? 0 : $rec['item_in'];
        $rec['item_out'] = (empty($rec['item_out'])) ? 0 : $rec['item_out'];
		$content .= $rec['item_code'] . $fdel;	
		$content .= '"' . $rec['item_name'] . '"' . $fdel;	
		$content .= $rec['item_in'] . $fdel;	
		$content .= $rec['item_out'] . $crlf;
	}
	download_this("consumable_frequancy_item_movement-$today.csv", $content);
	exit;
}

echo <<<HEAD
<script>
function export_this(){
	location.href = "./?mod=report&sub=consumable&act=view&term=frequency&by=category&export=1";
}
</script>
<h3>Frequency of Item Movement</h3>
HEAD;

if (count($summary) > 0){
	echo <<<HEAD1
<table class="report middle" width="500" cellpadding=2 cellspacing=1>
<tr>
	<td colspan=4 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr>
    <th rowspan=2 width=100>Item Code</th>
    <th rowspan=2>Item Name</th>
    <th colspan=2>Frequency</th>
</tr>
<tr>
    <th width=80>Purchased</th>
    <th width=80>Issued Out</th>
</tr>
HEAD1;

$row = 0;
foreach ($summary as $rec){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
    $rec['item_in'] = (empty($rec['item_in'])) ? 0 : $rec['item_in'];
    $rec['item_out'] = (empty($rec['item_out'])) ? 0 : $rec['item_out'];
	echo <<<REC
<tr $class>
    <td>$rec[item_code]</td>
    <td>$rec[item_name]</td>
    <td align="center">$rec[item_in]</td>
    <td align="center">$rec[item_out]</td>
</tr>
REC;
}
echo'</table>';
}
?>
<br/>
