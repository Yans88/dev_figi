<style>
a.reset {text-decoration: underline; color: navy}
div.resetpage {
	color: #000;
	background-color: #efe;
	width: 400px;
	padding: 10px 10px;
}
h3 { color: #000; text-align: center }
.error { color: #c00;}
</style>
<br/>&nbsp;<br/>
<br/>&nbsp;<br/>
<div class="resetpage" >
<br/>
<form id="resetForm" method="post" action="">
<h3>Setting new password!</h3>
 <br/>
<p>Please enter your new password!
<table id="xlogintable">
<tr><td><strong>Password&nbsp; </strong></td>
  <td><input name="new_password" type="password" autocomplete="off" size=26 value=""/></td>
</tr>
<tr><td><strong >Confirm Password&nbsp; </strong></td>
  <td><input name="confirm_password" type="password" autocomplete="off" size=26 value=""/></td></tr>
<tr><td colspan="2" align="center"> &nbsp; </td></tr>
<tr><td colspan="2" align="center">
<button id="doChange" type="button"><strong>Change</strong></button>
<!-- <a class="reset" href="./">Sign In</a> -->
</td></tr>
</table>
</form>

<div class="clear">&nbsp;</div>
<br/>
<?php
  if ($error != null) 
    echo '<div class="error">Error: '.$error.'</div>';
?>

<script  type="text/javascript">
$('#doChange').click(function(){
	var p = $('input[name=new_password]').val();
	var c = $("input[name=confirm_password]").val();
	if (p.length == 0 || c.length == 0)
		alert('You must fill both entries!');
	else if (p != c)
		alert('Password and Confirm Password must be same!');
	else {
		$('#resetForm').append('<input type="hidden" name="doChange" value=1>');	
		$('#resetForm').submit();	
	} 
});

$("input[name='username']").focus();
</script>

</div>
<br/><br/>
