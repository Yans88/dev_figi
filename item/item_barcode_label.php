<?php

require_once('../tcpdf/config/lang/eng.php');
require_once('../tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('FiGi');
$pdf->SetTitle('Item Barcodes');
$pdf->SetSubject("Generating Item's Barcodes");


// set default header data


// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins

$pdf->SetMargins(10, 0, 0,0);
$pdf->SetAutoPageBreak(TRUE, 0);


//set auto page breaks


//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set a barcode on the page footer


// set font
$pdf->SetFont('helvetica', '', 11);

// add a page
$pdf->AddPage('P', 'A4');

// print a message

// -----------------------------------------------------------------------------

$pdf->SetFont('helvetica', '', 10);

$css = <<<EOF
	<style>
		td{
			border: 1px solid black;
			height: 105px;
			width: 188px;
			text-align: center;
			
			display: table-cell;
			
		}
		tr{
			vertical-align: middle;
		}
		table{
			vertical-align: middle;
		}
		img{
			
			height: 80px;
			
		}
	</style>
EOF;
	
if($_limit==99999){
	$_limit = count($_POST['sel']);
}
if ($total_item > 0) {
    if(!isset($_POST['sel'])){
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
	$item = array();
	while($rec = mysql_fetch_array($rs)){
		$item[] = $rec;
	}
	}
	else{
		foreach($_POST['sel'] as $row){
			$item[]['asset_no'] = $row;
		}
	}
	
    $_per_page = 40;
	$total_item = ($_limit) ?$_limit : $total_item;
	$total_page = ceil($total_item/$_per_page);
	$table = '';
	for($i=1; $i<=$total_page; $i++){
		$html = "";
		
		$table = "";
		for($j = 1; $j<=$_per_page; $j++){
			
			$tr = "";
			$val = ($j+($_per_page*($i-1)));
			if($val>$total_item)continue;
			if($item[$val-1]['asset_no']!=''){
			if($j%4==1||$j==1){
				$tr .= '<tr>';
			}
			$tr .= '<td><br><br><img src="http://dev.figi.sg/gb.php?text=1&format=png&barcode='.$item[$val-1]['asset_no'].'"></td>';

			}
			if($j%4==0||$val==$total_item){
				$tr .= '</tr>';
				
			}
			$table .= $tr;
			
			
		}
		$html ="$css<table>$table</table>";
		
		// echo $html;
		$pdf->writeHTML($html,true, false, false, false, '');
		
		if($i!=$total_page) $pdf->AddPage('P', 'A4');
	}
		
}


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('Figi Item Barcode.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
