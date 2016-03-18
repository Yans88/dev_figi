<?php

$category_type = 'EQUIPMENT';
$this_year = date('Y');
$id_department = (!SUPERADMIN) ? USERDEPT : 0;

$_year = !empty($_POST['y']) ? $_POST['y'] : $this_year;

$month_names = array('none', 'January', 'February', 'March', 'April', 'May', 'June',
					 'July', 'August', 'September', 'October', 'November', 'December');

$categories = get_category_list($category_type, $id_department );
	
$query  = "SELECT item.id_category, COUNT(id_history) repair_frequency, total_charge repair_cost 
			FROM machine_history mh 
            LEFT JOIN machine_info mi ON mi.id_machine = mh.id_machine 
            LEFT JOIN item ON mi.id_item = item.id_item 
			WHERE YEAR(period_from) = $_year AND mi.id_item > 0 
			GROUP BY item.id_category ";			
$res = mysql_query($query);
//echo mysql_error().$query;
while ($rec = mysql_fetch_array($res)) {   
  $summary[$rec['id_category']] = array($rec['repair_frequency'], $rec['repair_cost']);
}

$years = array();
$year_start = $this_year - 7;
for ($i = $this_year+2; $i >= $year_start; $i--)
	$years[$i] = $i;
	
	
if (isset($_POST['act']) && $_POST['act'] == 'export'){
	$crlf = "\n";
	$content = 'Category,Frequency,Cost'.$crlf;
    
	foreach ($categories as $id_category => $category_name){ // baris
		$content .= $category_name ;		
		$content .= ','. $summary[$id_category][0] . ','. $summary[$id_category][1] . $crlf;
	}
	
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=machine_summary_by_category.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}

$year_combo = build_combo('y', $years, $this_year);

echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.display.click();
	frm.act.value='';
}
</script>

<h3>Machine Maintenance Report</h3>
<form method="Post">
<input type=hidden name=act>

<p style="color: #fff" class="center">
Select a year $year_combo <button type="submit" name="display" value=" Display ">Display</button>
</p>
</form>
<table class="report middle" width="400" cellpadding=2 cellspacing=1>
<tr>
	<td><h3>In Terms Of Category<h3></td>
	<td colspan=2 align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><th>Category</th><th>Frequency</th><th>Cost</th></tr>
HEAD;

$row = 0;
foreach ($categories as $id_category => $category_name){ // baris
	$row++;
	$class = ($row % 2 == 0) ? 'class="alt"' : 'class="normal"';
    $freq = (!empty($summary[$id_category][0])) ? $summary[$id_category][0] : 0;
    $cost = (!empty($summary[$id_category][1])) ? $summary[$id_category][1] : 0;
	echo '<tr '.$class.'><td>' . $category_name . '</td>';
	echo '<td align="center">' . $freq . '</td><td align="center">' . $cost . '</td></tr>';
}
echo'</table>';
?>
<br/>&nbsp;
