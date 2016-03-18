<?php
$valid_img = 'jpg|jpeg|png|gif|bmp';

$_id = !empty($_GET['id']) ? $_GET['id'] : 0;
$no_attachment = true;
if ($_id > 0){
    $query = 'SELECT filename, data FROM condemned_attachment WHERE id_attach = ' .$_id;
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){
        $no_attachment = false;
        $rec = mysql_fetch_row($rs);
        $filename = $rec[0];
        $data = base64_decode($rec[1]);
        
        
        if (preg_match('/.(' . $valid_img . ')$/i', $filename, $matches)){
            $ext = $matches[1];            
            $type = 'image';
        } else if (preg_match('/.(\w{3,4})$/i', $filename, $matches)){
            $ext = $matches[1];
            $type = 'application';
        } else $no_attachment = true;
        
        if (!$no_attachment){
            ob_clean();
            header("Content-type: $type/$ext");
            header('Content-length: ' . strlen ($data));
            header('Content-disposition: inline;filename="' . $filename . '"');
            echo $data;
            ob_end_flush();
        }
    }
}
if ($no_attachment){
    $im = ImageCreate (175, 20) or die ("Cannot Initialize new GD image stream");
    $white = ImageColorAllocate ($im, 255, 255, 255);
    $black = ImageColorAllocate ($im, 0, 0, 0);
    imagestring($im, 2, 5, 3, "Attachment is not available!", $black);
    
    ob_clean();
    header('Content-type: image/png');
    imagepng($im);
    ob_end_flush();
}
exit;
?>