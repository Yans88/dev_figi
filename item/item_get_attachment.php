<?php
$valid_ext = 'gif|jpg|jpeg|png|pdf|xls|doc|ppt|xlsx|docx|pptx';
//attach_type audio,attachment,video,doc,application

$_id = !empty($_GET['id']) ? $_GET['id'] : 0;
$_name = !empty($_GET['name']) ? $_GET['name'] : null;
$_dl = !empty($_GET['dl']) ? true : false;

if ($_name != null)
    $query = 'SELECT filename,data FROM item_attachment WHERE filename = "' .$_name . '"';
else
    $query = 'SELECT filename,data FROM item_attachment WHERE id_attach = ' .$_id;

$rs = mysql_query($query);
if ($rs && mysql_num_rows($rs) > 0){
    $rec = mysql_fetch_row($rs);
    $filename = $rec[0];
    $data = base64_decode($rec[1]);
    if (preg_match('/.(' . $valid_ext . ')$/i', $filename, $matches)){
        $ext = $matches[1];
        switch($ext){
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png': $ctype = 'image/' . $ext; break;
        case 'doc':
        case 'xls':
        case 'ppt':
        case 'docx':
        case 'xlsx':
        case 'pptx': $ctype = 'application/x-msdownload'; break;
        case 'pdf': $ctype = 'application/pdf'; break;
        default: $ctype = 'application/binary'; 
        }
        ob_clean();
        header('Content-type: ' . $ctype);
        header('Content-length: ' . strlen ($data));
        $disposition = ($_dl) ? 'attachment'  : 'inline';
        header('Content-disposition: '.$disposition.';filename=' . $filename);        
        //ob_flush();
        echo $data;
        ob_end_flush();
        
    }
} else
    echo 'Data is not available!';
exit;
?>