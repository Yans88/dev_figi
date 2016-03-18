<?php
if (!defined('FIGIPASS')) exit;
ob_clean();

$crlf = "\r\n";
$check_date = date('YmdHis');
if ($what_to_check=='differences') 
	$filename = 'figi-item_comparison-differences-'.$check_date.'.csv';
else
	$filename = 'figi-item_comparison-missing-from-'.$missing_from.'-'.$check_date.'.csv';

header('Content-type: text/x-comma-separated-values');
header('Content-disposition: attachment; filename="'.$filename.'"');
header('Pragma: no-cache');
header('Expires: 0');

echo 'FiGi Item Comparison'.$crlf;
echo 'Comparison Date: '.date('d M Y H:i:s').$crlf;
echo 'No of internal records: '.$int_item.$crlf;
echo 'No of external records: '.$ext_item.$crlf;
echo 'No of incorrect records: '.$item_count.$crlf;
echo 'No,Asset No,Serial No,Asset Name,Purchase Date,Purchase Value,Status,Vendor Name,Location Name';
if ($what_to_check=='differences') echo ',Source';
echo $crlf;

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

		foreach ($rec as $row){
			$span = null;
			if ($r==0)
				  $span = "$counter,$row[asset_no],$row[serial_no]";
			if ($different_in=='asset'){
				if ($r > 0) $span = ",,$row[serial_no]";
			} else if ($different_in=='serial'){
				if ($r > 0) $span = ",$row[asset_no],";
			} else { // both
				if ($r > 0) $span = ",,";
			}
			echo <<<DATAD
$span,"$row[asset_name]",$row[purchase_date],"$row[purchase_value]",$row[status_name],"$row[vendor_name]","$row[location_name]",$row[data_compare]$crlf
DATAD;
			$r++;
		}
	} else {
	
		echo <<<DATA
$counter,"$rec[asset_no]","$rec[serial_no]","$rec[asset_name]","$rec[purchase_date]","$rec[purchase_value]",$rec[status_name],"$rec[vendor_name]","$rec[location_name]"$crlf
DATA;
	}
  	$counter++;
}

ob_end_flush();
