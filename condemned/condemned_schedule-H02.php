<?php

if (!defined('FIGIPASS')) exit;

$request = get_condemned_issue($_id);
$items = get_item_by_condemned($_id);;

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

$pdf->SetFont('times', '', 10);

$disposal = get_disposal_info($_id);
$disposal_method = $disposal_methods[$disposal['disposal_method']];
$linecnt = 0;
$item_list = null;
foreach ($items as $rec){
    $linecnt++;
    $item_list .=<<<LIST
<tr>
    <td class="bottom right">$rec[asset_no]</td>
    <td class="bottom right" align="center">1</td>
    <td class="bottom right">$rec[serial_no], $rec[brand_name], $rec[model_no]</td>
    <td class="bottom right" align="center">$rec[date_of_purchase]</td>
    <td class="bottom right" align="center">$rec[cost]</td>
    <td class="bottom right">$rec[reason]</td>
    <td class="bottom ">$disposal_method</td>
</tr>
LIST;
}

$linemax = 15;
$rows = null;
for ($i=$linecnt; $i<$linemax; $i++){
    $rows .=<<<ROW
<tr>
    <td class="bottom right" style="height: 20px"></td>
    <td class="bottom right"></td>
    <td class="bottom right"></td>
    <td class="bottom right"></td>
    <td class="bottom right"></td>
    <td class="bottom right"></td>
    <td class="bottom right"></td>
</tr>
ROW;
}
$issue_date = null;
$item_list .= $rows;
$generate_date = date('d F Y');
$content =<<<TEXT
<style type="text/stylesheet">
body {font-family: times, serif, helvitica; font-size: 10pt;}
table { width: 100%; }
.list { border: 1px solid #000 }
td.right { border-right: 1px solid #000; }
td.left { border-left: 1px solid #000; }
td.top { border-top: 1px solid #000; }
td.bottom { border-bottom: 1px solid #000; }
td.head { text-align: center; font-weight: bold }
div {text-align: justify; }
div.text { }
.section {font-size: 10pt; text-decoration: underline; font-weight: bold; }
.info { }
.blank {height: 1px; }
.underscore {border-bottom: 1px solid #000;}
.signcol { width: 50%; border-bottom: 1px solid red; }
.sign {width: 20px; border: 1px solid #00f; float: right; clear: left; display: inline}
img.signature { height: 30px; border-bottom: 2px solid #000; }
.title{ font-size: 14pt; text-align: center; text-decoration: underline;}
.signbox { float: left; clear: both; width: 100px; border: 1px solid blue; display: block}
</style>
<br>&nbsp;<br>
<div><span class="section">SECTION D : SCHEDULE</span><span class="info"> (to be completed by Head, Consortioum/Department)</span></div>
<br>

<table nobr="true" cellpadding="3" cellspacing="0" class="list">
<tr>
    <td class="bottom right head" style="min-width:40px">ITEM NO.</td>
    <td class="bottom right head" width="60">QTY</td>
    <td class="bottom right head" width="300">DESCRIPTION(INCLUDING MACHINE IDENTIFICATION NUMBER)</td>
    <td class="bottom right head" width="90">DATE OF PURCHASE</td>
    <td class="bottom right head" width="90">ORIGINAL VALUE($)</td>
    <td class="bottom right head" width="170">REASON (OTHER THAN FAIR WEAR AND TEAR)</td>
    <td class="bottom right head" width="90">METHOD OF DISPOSAL</td>
</tr>
$item_list
</table>
<br>
<div class="section">SECTION E</div>
<br>
<div class="text">I certify that the items have been properly disposed as indicated in the Schedule.</div>
<br/>
<table nobr="true" cellpadding="4" cellspacing="0" >
<tr >
    <td rowspan="2" width="120">Signature of Head:</td>
    <td rowspan="2" class="underscore"><span class="signbox"></span></td>
    <td width="120"></td>
    <td width="60"></td>
    <td></td>
</tr>
<tr >
    <td></td>
    <td>Date:</td>
    <td class="underscore"><span class="sign">$issue_date</span></td>
</tr>
</table>

TEXT;
$pdf->writeHTML($content, true, false, false, false, '');

$pdf->SetFillColor(215, 235, 255);
$pdf->lastPage();

$pdf->Output($file_name, $output);
?>