<?php

if (!defined('FIGIPASS')) exit;

$request = get_condemned_issue($_id);
$items = get_item_by_condemned($_id);

// create new PDF document
$pdf = new TCPDF($pdf_page_orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('FiGi - Productivity Tools');
$pdf->SetTitle('Certificate of Condemnation');
$pdf->SetSubject('Certificate of Condemnation');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf_header_logo = "logo_print.png";
$pdf_header_logo_width = 60;
$pdf_header_title = 'Certificate of Condemnation';
$pdf_header_string = "FiGi - Productivity Tools\n".FIGI_URL;

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf_margin_top = 10;
$pdf_margin_header = 10;
$pdf->SetMargins(PDF_MARGIN_LEFT+10, $pdf_margin_top, PDF_MARGIN_RIGHT+10);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->AddPage();

$pdf->SetFont('times', '', 8);

$linecnt = 0;
$item_list = null;
foreach ($items as $rec){
    $linecnt += 2;
    $item_list .=<<<LIST
<tr>
    <td class="right">$rec[asset_no]</td>
    <td class="right">$rec[serial_no]</td>
    <td class="right" align="center">1</td>
    <td class="right">$rec[serial_no], $rec[brand_name], $rec[model_no], $rec[date_of_purchase]</td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class=""></td>
</tr>
LIST;
}
$linemax = 18;
for ($i=$linecnt;$i<$linemax;$i++){
    $item_list .=<<<ELIST
<tr>
    <td class="right">&nbsp;</td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class="right"></td>
    <td class=""></td>
</tr>
ELIST;
}

$generate_date = date('d F Y');
$content =<<<TEXT
<style type="text/stylesheet">
body { font-family: helvetica, serif, times; font-size: 10px; }
table { width: 100% }
td.right { border-right: 1px solid #000; }
td.left { border-left: 1px solid #000; }
td.top { border-top: 1px solid #000; }
td.bottom { border-bottom: 1px solid #000; }
div {text-align: justify; width: 50px; }
.section {font-weight: bold;}
.info {font-size: 8pt; }
</style>
<table nobr="true" cellpadding="3" cellspacing="0" border="0">
<tr>   
    <td width="9%"></td><td width="9%"></td>
    <td width="9%"></td><td width="19%"></td>
    <td width="9%"></td><td width="9%"></td>
    <td width="12%"></td><td width="9%"></td>
    <td width="9%"></td><td width="9%"></td>
</tr>
<tr>
    <td colspan="3" rowspan="2" class="bottom right" style="text-align: center;">SCHEDULE<br/>
        <span class="info">(to be completed by officer responsible for items)</span>
    </td>
    <td colspan="4" rowspan="2" class="bottom right">File Reference<br/>
        <span class="info"><font size=-2>(Only on continuation Sheet)</font></span>
    </td>
    <td colspan="3" class="bottom">Continuation<br/>Sheet No.</td>
</tr>
<tr>
    <td colspan="3" class="bottom"><span class="info">(To be completed only if the item is a fixed asset)</span>
    </td>
</tr>
<tr>
    <td class="bottom right" style="text-align: center;">(1)<br/>Goods / Fixed Asset ID</td>
    <td class="bottom right" style="text-align: center;">(2)<br/>Goods / Fixed Asset Code No. or File Ref.</td>
    <td class="bottom right" style="text-align: center;">(3)<br/>Quantity and Unit (Number of items)</td>
    <td class="bottom right" style="text-align: center;">(4)<br/>Description (including machine identification No.) and Date of original purchase</td>
    <td class="bottom right" style="text-align: center;">(5)<br/>Depreciated Value of Quantity (Retirement Cost)<br/>$</td>
    <td class="bottom right" style="text-align: center;">(6)<br/>If item is condemned for reasons other than fair wear and tear state reason</td>
    <td class="bottom right" style="text-align: center;">(7)<br/>Recommended method of disposal</td>
    <td class="bottom right" style="text-align: center;">(8)<br/>Retirement Date</td>
    <td class="bottom right" style="text-align: center;">(9)<br/>Proceeds<br/>$</td>
    <td class="bottom" style="text-align: center;">(10)<br/>Removal Cost<br/>$</td>
</tr>
$item_list
<tr>
    <td class="top bottom right"></td>
    <td class="top bottom right"></td>
    <td class="top bottom right"></td>
    <td class="top bottom right">Total value</td>
    <td class="top bottom right"></td>
    <td class="top bottom right"></td>
    <td class="top bottom right"></td>
    <td class="top bottom right"></td>
    <td class="top bottom right"></td>
    <td class="top bottom"></td>
</tr>
<tr>
    <td class="bottom right" colspan="7"></td>
    <td class="bottom" colspan="3">Initials of officer where retires the fixed assets from the fixed asset system</td>
</tr>
<tr>
    <td class="bottom" colspan="10" style="text-align: center">THIS SECTION FOR USE ON CONTINUATION SHEETS ONLY</td>
</tr>
<tr>
    <td colspan="2">Initials of officer who sign Certificate</td>
    <td ></td>
    <td colspan="4" style="text-align: center">Signatures of officers who examine and recommend issue of Certificate:</td>
    <td colspan="3"></td>
</tr>
<tr>
    <td colspan="10">&nbsp;<br/>$generate_date</td>
</tr>
</table>
TEXT;
//$content ='TEST';
$pdf->writeHTML($content, true, false, false, false, '');

$pdf->SetFillColor(215, 235, 255);
$pdf->lastPage();

$pdf->Output($file_name, $output);
?>