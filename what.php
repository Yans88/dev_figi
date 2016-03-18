<?php
include 'encryption.php';

$result = null;
$_text = !empty($_POST['text']) ? $_POST['text'] : null;
if (!empty($_POST['process']) && ($_text != null)){
    $encrypt = new Encryption();
    if ($_POST['process'] == 'decrypt')
        $result = $encrypt->decode($_text);
    else
        $result = $encrypt->encode($_text);
}
?>
<form method="post">
<div>Text : <textarea name="text" rows=3 cols=30><?php echo $_text?></textarea></div>
<div valign="top">Result : <textarea readonly rows=3 cols=30><?php echo $result?></textarea></div>
<button name="process" value="encrypt">Encrypt</button> 
<button name="process" value="decrypt">Decrypt</button>
</form>
