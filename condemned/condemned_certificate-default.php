<?php

if (!defined('FIGIPASS')) exit;

$request = get_condemned_issue($_id);

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('FiGi - Productivity Tools');
$pdf->SetTitle('Certificate of Condemnation');
$pdf->SetSubject('Certificate of Condemnation');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 017', PDF_HEADER_STRING);
$pdf_header_logo = "logo_print.png";
$pdf_header_logo_width = 60;
$pdf_header_title = 'Certificate of Condemnation';
$pdf_header_string = "FiGi - Productivity Tools\n".FIGI_URL;
//$pdf->SetHeaderData($pdf_header_logo, $pdf_header_logo_width, $pdf_header_title, $pdf_header_string);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf_margin_top = 15;
$pdf_margin_header = 15;
$pdf->SetMargins(PDF_MARGIN_LEFT+10, $pdf_margin_top, PDF_MARGIN_RIGHT+10);
/*
$pdf->SetHeaderMargin($pdf_margin_header);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
*/
//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//$pdf->SetFont('times', '', 14);

// add a page
$pdf->AddPage();

//$pdf->Write(0, 'CERTIFICATE OF CONDEMNATION', '', 0, 'L', true, 0, false, false, 0);

//$pdf->Ln(5);

$pdf->SetFont('times', '', 10);


// set color for background
//$pdf->SetFillColor(255, 255, 200);
// set color for text
//$pdf->SetTextColor(0, 63, 127);
list($dt, $tm) = explode(' ', $request['issue_datetime']);
$issue_sign = ($_sign) ? '<img style="border: 1px solid #000; height: 50px" src="'.FIGI_URL.'/condemned/sign_img.php?status=issue&id='.$_id.'">' : '';
$issue_date = ($_sign) ?  str_replace('-', ' ', $dt): '';

$generate_date = date('d F Y');
$content =<<< TEXT
<style type="text/stylesheet">

table { width: 100% }
td.right { border-right: 1px solid #000; }
td.left { border-left: 1px solid #000; }
td.top { border-top: 1px solid #000; }
td.bottom { border-bottom: 1px solid #000; }
div {text-align: justify; width: 50px; }
div.info {font-size: 9pt; }
div.text { }
div.section {font-weight: bold;}
.title{ font-size: 12pt; text-align: center; text-decoration: none;}
</style>
<br>
<div class="title">CERTIFICATE OF CONDEMNATION</div>
<br>&nbsp;
<table nobr="true" cellpadding="10" cellspacing="0" border="0">
<tr>
    <td colspan="2" class="top right"><div class="section">Section  A</div><br/>
        <span class="info">(to be completed by officer responsible for items)</span>
    </td>
    <td class="top">File Reference</td>
</tr>
<tr>
    <td class="top right bottom" style="height: 120px">Department holding goods / Fixed assets </td>
    <td class="left right top bottom">Total depricated value of goods / fixed assets</td>
    <td class="top left bottom">Total number of sheets in this Certificate</td>
</tr>
</table>
<table nobr="true" cellpadding="10" cellspacing="0" >
<tr>
    <td class="" style="text-align: center; width: 50%">
    <div style="text-align: justify">I certify that the items scheduled in this Certificate are beyond economical repair/irreparable* and should be disposed as recommended in the schedule.</div>
    
    </td>
    <td class="" style="width: 40%"><br/>&nbsp;<br/>$issue_sign</td>
    <td class="" style="width: 10%; vetical-align: bottom; font-size: 9pt"><br/>&nbsp;<br/>&nbsp;<br/>$issue_date</td>
</tr>
<tr>
    <td class="bottom"></td>
    <td class="bottom" style="vetical-align: bottom;" ><div>Name, Designation and Signature</div></td> 
    <td class="bottom" style="text-align: center;">Date</td>
</tr>
</table>
<table nobr="true" cellpadding="3" cellspacing="0" >
<tr>
    <td colspan=2 class="top right"><div class="section">Section B</div></td>
    <td colspan=2 class="top"><div class="section">Section C</div></td>
</tr>
<tr>
    <td width="2%" rowspan="3">&nbsp;</td>
    <td class="right" rowspan="3" style=" width: 48%">
        <div class="info">(to be completed by authority in Ministry)</div>
        <div>*I support and recommend condemnation and write-off of the items scheduled in this Certificate.</div>
        <div>*I approve condemnation of the items scheduled in this Certificate and also approve write-off of the amount involved. The goods/fixed assets should be disposed as indicated in the schedule</div>
        <div>I also confirm that where this Certificate covers any item, noted in the schedule as having been rendered inserviceable for reasons other than fair wear and tear, disciplinary action against the officer(s) responsible for such deterioration in condition has been considered and retention of the items is not necessary in connection with any proceeding under such action.</div>
        <div>&nbsp;<br/>&nbsp;<br/>&nbsp;</div>
    </td> 
    <td width="2%" >&nbsp;</td>
    <td style="text-align: left; width: 48%" >
        <div class="info">(to be completed by Ministry of finance only if Ministry's authority is exceeded)</div>
        <div>I approve condemnation of the items scheduled in this Certificate and also approve write-off of the amount involved. The goods/fixed assets should be disposed as indicated in the schedule</div>
        <div>&nbsp;<br/>&nbsp;</div>
        <div>Name, Designation and Signature &nbsp; &nbsp; &nbsp; Date</div>
    </td> 
</tr>
<tr>
    <td class="top" colspan="2"><div class="section">Section D</div></td>
</tr>
<tr>
    <td ></td>
    <td >
        <div class="info">(to be completed on disposal of condemned goods)</div>
        <div>I certify that the goods/fixed assets have been properly disposed as indicated in the schedule. I also attach the "Sale Certificate", acknowledgement receipt or any other disposal certificate.</div>
        <div>&nbsp;<br/>&nbsp;</div>
        
    </td> 
</tr>
<tr>
    <td></td>
    <td class="right"><div>Name, Designation and Signature &nbsp; &nbsp; &nbsp; Date</div></td>
    <td></td>
    <td><div>Name, Designation and Signature &nbsp; &nbsp; &nbsp; Date</div></td>
</tr>
<tr>
    <td class="top"></td>
    <td class="top" colspan="3">
        <div class="text">
            <div class="info">(* Delete were not applicable)<br/>$generate_date</div>
        </div>
    </td> 
</tr>

</table>
TEXT;

$pdf->writeHTML($content, true, false, false, false, '');
//$pdf->MultiCell(30, 0, $left_column, 1, 'J', 1, 0, '', '', true, 0, false, true, 0);

// set color for background
$pdf->SetFillColor(215, 235, 255);

// set color for text
//$pdf->SetTextColor(127, 31, 0);
// write the second column
//$pdf->MultiCell(80, 0, $right_column, 1, 'J', 1, 1, '', '', true, 0, false, true, 0);

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($file_name, $output);
//============================================================+
// END OF FILE                                                
//============================================================+
?>