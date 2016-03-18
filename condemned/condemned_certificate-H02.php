<?php

if (!defined('FIGIPASS')) exit;

$request = get_condemned_issue($_id);

if (CONDEMNATION_FLOW_TYPE == 2){
    $principle = get_principle();
    $director = get_director();
    $request['approved_by'] = $principle['full_name'];
    $request['recommended2_by'] = $director['full_name'];
}

class H02PDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        /*
        $this->Image('condemned/res/H02.gif', 60, 10, '', '', 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Image('condemned/res/hwa-chong.gif', 30, 10, '', '', 'GIF', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('times', '', 10);
        // Title
        $this->Ln(11);
        $this->Cell(0, 40, 'HWA CHONG INSTITUTION', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetFont('times', '', 8);
        // Title
        $this->Ln(6);
        $this->Cell(0, 55, '661 Bukit Timah Road, Singapore 269734', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetLineWidth(0.7);
        $this->Line(10, 30, 200, 30);
        */
        $this->Image('condemned/res/H02_logo.gif', 60, 5, '', '', 'GIF', '', 'T', false, '', '', false, false, 0, false, false, false);
        $this->SetLineWidth(0.5);
        $this->Line(10, 35, 200, 35);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('times', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}


// create new PDF document
$pdf = new H02PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(FULLNAME);
$pdf->SetTitle('Certificate of Condemnation');
$pdf->SetSubject('Certificate of Condemnation');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setPrintHeader(true);
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
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf_margin_top = 15;
$pdf_margin_header = 15;
$pdf->SetMargins(PDF_MARGIN_LEFT+25, $pdf_margin_top, PDF_MARGIN_RIGHT+10);
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
$pdf->SetFont('times', '', 14);

// add a page
$pdf->AddPage();

//$pdf->Write(0, 'CERTIFICATE OF CONDEMNATION', '', 0, 'L', true, 0, false, false, 0);

$pdf->Ln(15);

$pdf->SetFont('times', '', 10);


// set color for background
//$pdf->SetFillColor(255, 255, 200);
// set color for text
//$pdf->SetTextColor(0, 63, 127);
$_sign = false;
list($dt, $tm) = explode(' ', $request['issue_datetime']);
$issue_sign = ($_sign) ? '<img class="signature" src="'.FIGI_URL.'/condemned/sign_img.php?status=issue&id='.$_id.'">' : '';
$issue_date = ($_sign) ?  str_replace('-', ' ', $dt): '';
//$issue_sign ='mbele gedhes';

$hod_name = $request['recommended_by'];
$director_name = $request['recommended2_by'];
$exception_list = '<br/><br/>';
if (empty($director_name))
    $director_name = '<img src="images/space.gif" class="blank underscore" style="width: 200px">&nbsp;</img>';
else {
    $items = get_item_exception_by_condemned($_id);
    foreach ($items as $id_item => $item)
        $exceptions[] = $item['asset_no'];
    if (count($exceptions)>0)
        $exception_list = implode(', ', $exceptions);
}
$principle_name = $request['approved_by'];
$issuer = get_user($request['issued_by']);
$department_name = $issuer['department_name'];
$generate_date = date('d F Y');
$transaction_no = TRX_PREFIX_CONDEMNED . $request['id_issue'];

//$data = file_get_contents($template_path);
{
$content =<<<TEXT
<style type="text/stylesheet">
body {font-family: times, serif, helvitica; font-size: 10pt;}
table { width: 100% }
td.right { border-right: 1px solid #000; }
td.left { border-left: 1px solid #000; }
td.top { border-top: 1px solid #000; }
td.bottom { border-bottom: 1px solid #000; }
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
<div><label class="title">CERTIFICATE OF CONDEMNATION</label> 
<img style="width: 20px" src="images/space.gif" class="blank"/> 
<cite>Transaction No. $transaction_no</cite>
</div>
<div><span class="section">SECTION  A</span><span class="info"> (to be completed by Head)</span></div>
<br>
<del>CONSORTIUM</del> / <br>DEPARTMENT:  
<img style="width: 50px" src="images/space.gif" class="blank"/>$department_name<br><br>
NAME OF HEAD<img style="width: 50px" src="images/space.gif" class="blank"/>$hod_name<br>
<div style="text-align: left">I certify that the items scheduled in this certificate are * beyond economical repair / irreparable / obsolete and should be disposed as recommended in the schedule.</div>
<br>
<table nobr="true" cellpadding="0" cellspacing="0" >
<tr >
    <td width="120"></td>
    <td rowspan="2" class="underscore"><span class="signbox"></span></td>
    <td width="120"></td>
    <td width="60"></td>
    <td></td>
</tr>
<tr >
    <td>Signature of HEAD:</td>
    <td></td>
    <td>Date:</td>
    <td class="underscore"><span class="sign">$generate_date</span></td>
</tr>
</table>
<br>&nbsp;
<hr>
<br>&nbsp;
<div><span class="section">SECTION B</span> <span class="info">(to be completed by a Dean/Director)</span></div>
<br>
NAME OF <del>DEAN</del>/DIRECTOR: 
<img style="width: 20px" src="images/space.gif" class="blank"/>$director_name
<br>
<div class="text">I have personally examined the items scheduled in this certificate and I recommend the condemnation of all the items except those listed below.</div>
<div>Items not to be condemned: $exception_list<br></div>
<table nobr="true" cellpadding="0" cellspacing="0" >
<tr >
    <td rowspan="2" width="120">Signature of Dean/Director:</td>
    <td rowspan="2" class="underscore"><span class="signbox"></span></td>
    <td width="120"></td>
    <td width="60"></td>
    <td></td>
</tr>
<tr >
    <td></td>
    <td>Date:</td>
    <td class="underscore"><span class="sign">$generate_date</span></td>
</tr>
</table>
<br>&nbsp;
<hr>
<br>&nbsp;
<div><span class="section">SECTION C</span> <span class="info">(to be completed by Deputy Principle)</span></div>
<br>
<div class="text">I approve condemnation of the items scheduled in this Certificate except those listed in Section B above and also approve write-off of the amount involved.  The stores should be disposed as indicated in the schedule.</div>
<br/>

NAME of Deputy Principle: <img style="width: 20px" src="images/space.gif" class="blank"/>$principle_name
<br/>&nbsp;<br/>
<br/>&nbsp;<br/>

<table nobr="true" cellpadding="0" cellspacing="0" >
<tr >
    <td rowspan="2" width="100">Signature of Deputy Principle:</td>
    <td rowspan="2" class="underscore"><span class="signbox"></span></td>
    <td width="100"></td>
    <td width="60"></td>
    <td></td>
</tr>
<tr >
    <td></td>
    <td>Date:</td>
    <td class="underscore"><span class="sign">$generate_date</span></td>
</tr>
</table>

&nbsp;<br/>
&nbsp;<br/>
<span class="info">* Delete whichever is not applicable</span>

TEXT;
}
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
//print_r($request);
$pdf->Output($file_name, $output);
//============================================================+
// END OF FILE                                                
//============================================================+
?>