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

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-5, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

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
$pdf->AddPage('L', 'A4');

// print a message
/*
$txt = "You can also export 1D barcodes in other formats (PNG, SVG, HTML). Check the source code documentation of TCPDFBarcode class for further information.";
$pdf->MultiCell(70, 50, $txt, 0, 'J', false, 1, 125, 30, true, 0, false, true, 0, 'T', false);
$pdf->SetY(30);
*/
// -----------------------------------------------------------------------------

$pdf->SetFont('helvetica', '', 10);

// define barcode style
$style = array(
	'position' => '',
	'align' => 'C',
	'stretch' => false,
	'fitwidth' => true,
	'cellfitalign' => '',
	'border' => true,
	'hpadding' => 'auto',
	'vpadding' => 'auto',
	'fgcolor' => array(0,0,0),
	'bgcolor' => false, //array(255,255,255),
	'text' => true,
	'font' => 'helvetica',
	'fontsize' => 8,
	'stretchtext' => 0
);

if ($total_item > 0) {
    $no_of_cols = 3;
    //$rs = get_items($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
    $y = $pdf->getY();
    $x = $pdf->getX();
    $left = $x;
    $h = 20;
    $col = 1; $row = 0;
    while ($rec = mysql_fetch_array($rs)){
        if ($col > $no_of_cols){
            //$pdf->write1DBarcode($rec['asset_no'], 'C128', $x, $y, '', $h, 0.4, $style, 'N');
            $pdf->Ln();
            $y = $pdf->getY()+20;
            $x = $pdf->getX();
            $col = 1;
            $row++;
        } 
        if ($row > 6){
            $pdf->AddPage('L', 'A4');
            $pdf->Cell(0, 0, 'New Page: ' . $col);
            $row = 0;
            $y = $pdf->getY();
            $x = $left;//$pdf->getX();
            $col = 1;
        }
        $pdf->write1DBarcode($rec['asset_no'], 'C128', $x, $y, '', $h, 0.4, $style, 'T');
        $x = $pdf->getX() + 10;        
        $col++;

    /*
        if ($col > $no_of_cols){ 
            $pdf->Ln();
            $y = $pdf->getY() + $h;
            $x = $left;
            $row++;
            $col = 1;
        } 
        $pdf->write1DBarcode($rec['asset_no'], 'C128', $x, $y, '', $h, 0.4, $style, 'T');
        $x += 85;///(($col-1) * 100);
        $col++; 
        if ($row > 7 ) {
            $pdf->AddPage('L', 'A4');
            $y = $pdf->getY() + $h;
            $x = $left;
            $col = 1;
            $row = 0;
        }
    */
    }
}
// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('Figi Item Barcode.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
