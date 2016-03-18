<?php

include './barcode_util.php';
// echo 'a';

if ($total_item > 0) {
    $no_of_cols = 2;
    $data = array();
    while ($rec = mysql_fetch_array($rs)){    
        $data[] = $rec['asset_no'];

    }
    $im = null;
    if (count($data)>0)
        $im = create_barcode_sheet($data, $no_of_cols, 20);
    if ($im != null){
        $fg = imagecolorallocate($im, 0, 0, 0);
        //imagerectangle($im, 0, 0, imagesx($im)-1, imagesy($im)-1, $fg);
        header('Content-type: image/png');
        header('Content-disposition: attachment; filename=Item Barcode Sheet.png');
        imagepng($im);
        imagedestroy($im);
        
    }
    
}
?>