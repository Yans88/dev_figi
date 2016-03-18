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
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins

$pdf->SetMargins(0, 0, 0,0);
$pdf->SetAutoPageBreak(TRUE, 0);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set a barcode on the page footer
//$pdf->setBarcode(date('Y-m-d H:i:s'));

// set font
$pdf->SetFont('helvetica', '', 11);

// add a page
$pdf->AddPage('P', 'A4');

// print a message
/*
$txt = "You can also export 1D barcodes in other formats (PNG, SVG, HTML). Check the source code documentation of TCPDFBarcode class for further information.";
$pdf->MultiCell(70, 50, $txt, 0, 'J', false, 1, 125, 30, true, 0, false, true, 0, 'T', false);
$pdf->SetY(30);
*/
// -----------------------------------------------------------------------------

$pdf->SetFont('helvetica', '', 10);

$css = <<<EOF
	<style>
		td{
			
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
	
	
if ($total_item > 0) {
    if(!isset($_POST['sel'])){
    $rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
	$item = array();
	while($rec = mysql_fetch_array($rs)){
		$item[] = $rec;
	}
	}
	elseif(isset($_POST['sel'])){
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
			$tr .= '<td><br><br><img src="'.$base_url .'/qrcode.php?text=1&format=png&qrcode='.$item[$val-1]['asset_no'].'&selected_field='.$_GET['selected_field'].'"></td>';
			}
			if($j%4==0){
				$tr .= '</tr>';
				
			}
			$table .= $tr;
		}
		$html ="$css<table>$table</table>";
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
