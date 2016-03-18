<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="X-UA-Compatible" content="chrome=1" />
<title>FiGi Productivity Tools</title>
<link rel="shortcut icon" type="image/x-icon" href="images/figiicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLE_PATH ?>figi.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo STYLE_PATH ?>anytimec.css" />
<link rel='stylesheet' type='text/css' href='./style/default/jquery-ui-1.8.13.custom.css'/>	
<script type="text/javascript" src="./js/jquery/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="./js/figi.js"></script>
<script type="text/javascript" src="./js/signature_pad.js"></script>
<script type="text/javascript" src="./js/anytimec.js"></script>
<script type="text/javascript" src="./js/moment.min.js"></script>
<script type='text/javascript' src='./js/jquery/jquery-ui-1.8.13.custom.min.js'></script>
<script type="text/javascript" src="js/spin.min.js"></script>	

<script type="text/javascript">
var opts = {
  lines: 13, // The number of lines to draw
  length: 11, // The length of each line
  width: 7, // The line thickness
  radius: 28, // The radius of the inner circle
  corners: 1, // Corner roundness (0..1)
  rotate: 0, // The rotation offset
  direction: 1, // 1: clockwise, -1: counterclockwise
  color: '#000', // #rgb or #rrggbb or array of colors
  speed: 1, // Rounds per second
  trail: 60, // Afterglow percentage
  shadow: false, // Whether to render a shadow
  hwaccel: false, // Whether to use hardware acceleration
  className: 'spinner', // The CSS class to assign to the spinner
  zIndex: 2e9, // The z-index (defaults to 2000000000)
  top: 'auto', // Top position relative to parent in px
  left: 'auto' // Left position relative to parent in px
};

var timer = null

function display_time(){
    var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var time = new Date()
    var hours = time.getHours()
    hours=((hours < 10) ? "0" : "") + hours
    var minutes = time.getMinutes()
    minutes=((minutes < 10) ? "0" : "") + minutes
    var seconds = time.getSeconds()
    seconds=((seconds < 10) ? "0" : "") + seconds
    var dow = time.getDay();
    var dom = time.getDate();
    dom=((dom < 10) ? "0" : "") + dom
    var mon = time.getMonth();
    var year = time.getFullYear();
    var clock = days[dow] + ', ' + dom + " " + months[mon] + " " + year + " "+ hours + ":" + minutes + ":" + seconds;
    $('#nowdatetime').html(clock + ' &nbsp;  &nbsp; ');
    timer = setTimeout("display_time()",1000)
}

function toggle_fold(obj){
    var rel = obj.rel;
    var dataid = obj.id.toString().substring(4);
    if (rel == 'open'){
        $('#'+dataid).hide();
        obj.rel = "close";
        obj.innerHTML = "&darr;";
    } else {
        $('#'+dataid).show();
        obj.rel = "open";
        obj.innerHTML = "&uarr;";
    }
}


$(document).ready(display_time());
</script>
</head>

<body>
<div id="contentcenter" align="center" >
    
<?php
//if (defined('FIGIPASS')) {
?>
<div id="header">
        <div id="headercontent">
        <div id="logo">&nbsp;</div>
        <div id="nowdatetime" style="text-align: right; "></div>
        <div id="account">
<?php
    if (defined('FULLNAME')) {
        $username = FULLNAME;
        echo <<<TEXT
    Welcome, $username. &nbsp;
    <a href="./?mod=user&act=account">account</a> | <a href="logout.php">logout</a>
    &nbsp; &nbsp;
TEXT;
    } else {
        if ($_mod != null)
        echo <<<LOGIN
<form method="post" action="" id="loginbox-small">
Username <input type="text" name="username" autocomplete="off" size=8 />
Password <input type="password" name="password" autocomplete="off" size=8 />
<button type="submit" value="Login" name="doLogin">Login</button>
</form>
LOGIN;
    }
?>
        </div>
    </div>
</div>
<script>
$('#logo').click(function(e){
    location.href = "./?";
});
</script>
<div class="clear"></div>

<?php
$crlf = "\r\n";
    if (defined('USERGROUP'))
        if ((USERGROUP != GRPTEA) && ($_mod!='portal')) {
?>
<div id="menubar" >
    <ul>
    <li><a <?php echo ($_mod==null) ? 'class="current"' : '' ?>  href="./">Home</a></li>
<?php

    //if (in_array('user', $accessible_modules))
    if (USERID == 1)
        echo '<li><a ' . (($_mod=='user') ? 'class="current"' : '') . ' href="./?mod=user">Users</a></li>'.$crlf;
    if (in_array('item', $accessible_modules))
        echo '<li><a ' . (($_mod=='item') ? 'class="current"' : '') . ' href="./?mod=item">Items</a></li>'.$crlf;
    if (in_array('loan', $accessible_modules))
        echo '<li><a ' . (($_mod=='loan') ? 'class="current"' : '') . ' href="./?mod=loan">Loans</a></li>'.$crlf;
    if (in_array('service', $accessible_modules))
        echo '<li><a ' . (($_mod=='service') ? 'class="current"' : '') . ' href="./?mod=service">Services</a></li>'.$crlf;
    if (in_array('fault', $accessible_modules))
        echo '<li><a ' . (($_mod=='fault') ? 'class="current"' : '') . ' href="./?mod=fault">Faults</a></li>'.$crlf;
    if (in_array('facility', $accessible_modules))
        echo '<li><a ' . (($_mod=='facility') ? 'class="current"' : '') . ' href="./?mod=facility">Facilities</a></li>'.$crlf;
    if (in_array('report', $accessible_modules))
        echo '<li><a ' . (($_mod=='report') ? 'class="current"' : '') . ' href="./?mod=report">Reports</a></li>'.$crlf;
    if (in_array('condemned', $accessible_modules))
        echo '<li><a ' . (($_mod=='condemned') ? 'class="current"' : '') . ' href="./?mod=condemned">Condemn</a></li>'.$crlf;
    if (in_array('payment', $accessible_modules))
        echo '<li><a ' . (($_mod=='payment') ? 'class="current"' : '') . ' href="./?mod=payment">Payments</a></li>'.$crlf;
?>    
    </ul>
</div>
<?php
    } else { // other then loan only    
    /*
    echo <<<STYLE
<script type="text/javascript">
    var h = document.getElementById('header')
    //h.style.position = 'relative';
    h.style.width = '740px';
</script>
STYLE;
    */
    }
?>
<div id="body" >