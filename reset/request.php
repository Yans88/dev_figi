<div class="resetpage" >
<form id="resetForm" method="post" action="">
<h3>Resetting password request!</h3>
<p>Please enter your registered username or email.
<table id="xlogintable">
<tr><td><strong>Username &nbsp; </strong></td>
  <td><input name="name" type="text" autocomplete="off" size=26 value=""/></td>
</tr>
<tr><td><strong >Email&nbsp; </strong></td>
  <td><input name="email" type="text" autocomplete="off" size=26 value=""/></td></tr>
<tr><td colspan="2" align="center"> &nbsp; </td></tr>
<tr><td colspan="2" align="center">
<button id="doReset" type="button"><strong>Reset Password</strong></button>
<a class="reset" href="./">Sign In</a>
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
$('#doReset').click(function(){
	var u = $('input[name=name]').val();
	var e = $("input[name=email]").val();
	if (e.length+u.length > 3){
		$('#resetForm').append('<input type="hidden" name="doReset" value=1>');	
		$('#resetForm').submit();	
	} else 
		alert('Please enter correct username or registered email!');

});

$("input[name='username']").focus();
</script>

</div>
<br/><br/>
