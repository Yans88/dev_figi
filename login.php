<?php

$logged = false;
$error  = null;
$dsturl = $_SERVER['PHP_SELF'];
if (!empty($_SERVER['QUERY_STRING'])) $dsturl .= '?'.$_SERVER['QUERY_STRING'];
if (!empty($_POST['dsturl'])) $dsturl = $_POST['dsturl'];

if (!empty($_POST['doLogin'])) {
    //print_r($_POST);
    $rec = authenticate($_POST['username'], $_POST['password']); 
    if ($rec != null)
        $logged = $rec['id_user'] > 0;
    if (!$logged)
        $error = 'Username or password invalid!';
}

if ($logged) {
    ob_clean();
    $_SESSION['figi_authenticated'] = 'true';//date('YmdHis');
    $_SESSION['figi_username'] = $rec['user_name'];
	$_SESSION['figi_nric'] = $rec['nric'];
    $_SESSION['figi_fullname'] = $rec['full_name'];
    $_SESSION['figi_userid'] = $rec['id_user'];
    $_SESSION['figi_usergroup'] = $rec['id_group'];
    $_SESSION['figi_accesslist'] = serialize(get_user_access_list($rec['id_group']));
    $_SESSION['figi_modlist'] = serialize(get_user_module_list($rec['id_group'], 1));
    $_SESSION['figi_pagelist'] = serialize(get_user_page_list($rec['id_group']));
	$_SESSION['figi_department'] = $rec['id_department'];
	$_SESSION['figi_department_name'] = $rec['department_name'];
	$_SESSION['figi_main_department'] = $rec['id_department'];
	$_SESSION['figi_other_department'] = $rec['other_department'];
    $username = $_POST['username'];
    user_log(LOG_ACCESS, 'log-in as '.$username);
	/*
    $url = $_SERVER['PHP_SELF'];
    if (!empty($_SERVER['HTTP_REFERER']) && preg_match('/figi/i', $_SERVER['HTTP_REFERER']))
        $url = $_SERVER['HTTP_REFERER'];
    if ($rec['id_group']==GRPTEA) $url = './?mod=portal';
	if ($rec['id_group']==GRPSTUDENT) $url = './?mod=portal';
    echo '<script>location.href="'.$url.'";</script>';
	*/
	if ($rec['id_department'] > 0 && $rec['other_department'] > 0)
		$dsturl = 'select_department.php';
	redirect($dsturl);
    ob_flush();
	
} 
else {// not logged
    if (empty($_mod)){
?>
<?php include 'upcoming_events.php'; ?>
<br/>
<div id='loginbox' style="width: 320px;">
<form method="post" action="">
<fieldset>
<legend>Authentication</legend>
<table id="xlogintable">
<tr><td><strong>Username &nbsp; </strong></td>
  <td><input type="text" name="username" autocomplete="off" size=16/></td>
</tr>
<tr><td><strong >Password &nbsp; </strong></td>
  <td><input type="password" name="password" autocomplete="off"  size=16/></td></tr>
<tr><td colspan="2" align="center"></td></tr>
</table>
</fieldset>
<fieldset class="footer">
<a class="reset" href="./?mod=reset">Reset password</a>
<button type="submit" value="Login" name="doLogin"><strong>Login</strong></button>
</fieldset>
</form>
</div>

<div class="clear">&nbsp;</div>

<?php
  if ($error != null) 
    echo '<div class="error center">Error: '.$error.'</div>';
?>

<script  type="text/javascript">

    $("input[name='username']").focus();
    /*
	//$('#loginbox').position('absolute');
	$(window).resize(function(e){
		var ww = $(window).width();
		var wh = $(window).height();
		var ow = $('#loginbox').width();
		var oh = $('#loginbox').height();
		$('#loginbox').offset({top: (wh-oh) /2, left: (ww-ow) / 2});
		});
	$(window).resize();
    */
</script>

<?php
    } // empty mod
} // not logged
?>
