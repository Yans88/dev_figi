<?php
$valid_ext = 'jpg|jpeg|png|gif';
//attach_type audio,image,video,doc,application

$_id = !empty($_GET['id']) ? $_GET['id'] : 0;
$_name = !empty($_GET['name']) ? $_GET['name'] : null;
$_thumb = (!empty($_GET['thumb']) && $_GET['thumb'] > 0) ? true : false;

if (!$_thumb){
    if ($_name != null)
        $query = 'SELECT filename,data FROM item_image WHERE filename = "' .$_name . '"';
    else
        $query = 'SELECT filename,data FROM item_image WHERE id_image = ' .$_id;
} else {
    if ($_name != null)
        $query = 'SELECT filename,thumbnail FROM item_image WHERE filename = "' .$_name . '"';
    else
        $query = 'SELECT filename,thumbnail FROM item_image WHERE id_image = ' .$_id;
}
$rs = mysql_query($query);
if ($rs && mysql_num_rows($rs) > 0){
    $rec = mysql_fetch_row($rs);
    $filename = $rec[0];
    $data = base64_decode($rec[1]);
    if (preg_match('/.(' . $valid_ext . ')$/i', $filename, $matches)){
        $ext = $matches[1];
        
        ob_clean();
        header('Content-type: image/' . $ext);
        header('Content-length: ' . strlen ($data)); 
        header('Content-disposition: inline;filename="' . $filename . '"');        
        //ob_flush();
        echo $data;
        ob_end_flush();
        
    }
} else {
    $im = ImageCreate (60, 20) or die ("Cannot Initialize new GD image stream");
    $white = ImageColorAllocate ($im, 255, 255, 255);
    $black = ImageColorAllocate ($im, 0, 0, 0);
    imagestring($im, 2, 0, 0, "No Image!", $black);
    
    ob_clean();
    header('Content-type: image/png');
    imagepng($im);
    ob_end_flush();
}
exit;
?>