<?php

putenv('GDFONTPATH=' . realpath('./fonts'));

include '../tcpdf/barcodes.php';

function create_barcode($code){

    $g = null;
    $fp = 'VeraBd.ttf';
    $bc = new TCPDFBarcode($code, 'C128');            
    $bc->copyBarcodeImage($g, 2, 60);
    $w = imagesx($g);
    $h = imagesy($g);
    $hspace = 20;
    $vspace = 35;
    $nw = $w+$hspace;
    $nh = $h+$vspace;
    $im = imagecreate($nw, $nh);

    $bg = imagecolorallocate ($im, 255, 255, 255);
    $fg = imagecolorallocate ($im, 0, 0, 0);
    imagerectangle($im, 0, 0, $nw-1, $nh-1, $fg);
    imagecopy($im, $g, $hspace / 2, 10, 0, 0, $w, $h);
    $fs = 11;
    $fa = 0;
    $td = imagettfbbox($fs, $fa, $fp, $code);

    $sw = abs($td[4] - $td[0]);
    $sx = ($nw - $sw) / 2;

    imagettftext($im, $fs, 0, $sx, $h+25, $fg, $fp, $code);
    return $im;
}

function create_barcode_sheet($data, $col = 2, $space = 20)
{
    if (!is_array($data) || empty($data))
        return null;
    $c = 0;
    $im = null;
    foreach ($data as $code){
        $bc = create_barcode($code);
        $w = imagesx($bc);
        $h = imagesy($bc);
        if ($im == null){
            $r = ceil(count($data) / $col);
            
            $im = imagecreate(($w + $space) * $col, ($h + $space) * $r);
            $bg = imagecolorallocate ($im, 255, 255, 255);
            $fg = imagecolorallocate ($im, 0, 0, 0);
            
            $y = $space / 2;
            $x = $space / 2;
        }
        if ($c >= $col){
            $y += $h + $space;
            $x = $space / 2;
            $c = 0;
        }
        imagecopy($im, $bc, $x, $y, 0, 0, $w, $h);
        $x += $w + $space;
        $c++;
    }
    return $im;
}

?>
