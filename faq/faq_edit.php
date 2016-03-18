<?php 

//require_once('imageresize.php');

if (!defined('FIGIPASS')) exit;


$_id = isset($_POST['id_faq']) ? $_POST['id_faq'] : 0;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$question = isset($_POST['question']) ? $_POST['question'] : null;
$answer = isset($_POST['answer']) ? $_POST['answer'] : null;
$_msg = null;

$faq = get_faq_by_id($id);


if (isset($_POST['save'])) {
	$data = array('question' => $question, 'answer'=>$answer);
	if($_id > 0){
		$data += array('id_faq'=>$_id);		
		$save = edit_faq($data);
	}else{
		$save = save_faq($data);
	}	
	if($save){
		echo '<script>alert("FAQ data saved successfully");location.href="./?mod=faq&act=list"</script>';
	}else{
		echo '<script>alert("FAQ data saved is failed");location.href="./?mod=faq&act=list"</script>';
	}
	
}

?>

<!--<script type="text/javascript" src="./js/jquery.opacityrollover.js"></script>
<script type="text/javascript" src="./js/slimbox2.js"></script>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<link rel="stylesheet" href="<?php echo STYLE_PATH?>slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" /> -->
<script type="text/javascript" src="./js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="./js/ckeditor/config.js"></script>
<br/><br/>
<br/><br/>
<form id="frm_edit" method="post">
<input type="hidden" id="id_faq" name="id_faq" value=<?php echo $id; ?> enctype="multipart/form-data">
<table  class="itemlist student" style="">
<tr><th class="center" colspan=2>FAQ Form</th></tr>
<tr height="10">
	<td valign="top"></td><td align="left"></td>
</tr>
<tr>
	<td valign="top">Question</td><td><textarea name="question" id="question" cols="44" rows="5"><?php echo $faq['question'];?></textarea>
	<script>CKEDITOR.replace('question',{
			filebrowserBrowseUrl: './kcfinder/browse.php',
			filebrowserUploadUrl: './kcfinder/upload.php'
	});</script>
	</td>
</tr>
<tr height="30">
	<td valign="top"></td><td align="left"></td>
</tr>
<tr class="alt">
	<td valign="top">Answer</td><td align="left"><textarea name="answer" id="answer" cols="44" rows="7" align="left"><?php echo $faq['answer'];?>	
	</textarea><script>CKEDITOR.replace('answer',{
			filebrowserBrowseUrl: './kcfinder/browse.php',
			filebrowserUploadUrl: './kcfinder/upload.php'
	});</script></td>
</tr>

<tr>
	<th colspan=2 class="center" height="30">
		<input type="button" name="cancel" id="cancel" value=" Cancel" onclick="cancel_it()">
		<input type="submit" name="save" id="save" value="Save" >
	</th>	
</tr>
</table>
</form>
<br/>
<br/>

<script>
function cancel_it()
{
    location.href='./?mod=faq';
}
</script>