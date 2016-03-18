<?php 


if($_portal == 'booking_alternate' || $_mod!='booking' ){
if(ALTERNATE_PORTAL_STATUS == 'enable'){ 
	
	
	ob_clean();
	
	?>
	<link rel="stylesheet" type="text/css" href="style/default/figi.css" media="screen" />	
	<link rel="stylesheet" type="text/css" href="style/default/anytimec.css" />
	<link rel='stylesheet' type='text/css' href='style/default/jquery-ui-1.8.13.custom.css'/>	

	<script type="text/javascript" src="js/jquery/jquery-1.5.1.min.js"></script>
	<script type="text/javascript" src="js/figi.js"></script>
	<script type="text/javascript" src="js/signature_pad.js"></script>
	<script type="text/javascript" src="js/anytimec.js"></script>
	<script type="text/javascript" src="js/moment.min.js"></script>
	<script type='text/javascript' src='js/jquery/jquery-ui-1.8.13.custom.min.js'></script>
	<script type="text/javascript" src="js/spin.min.js"></script>
<?php } 

}

 ?>
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		
<?php

if (empty($_act)) $_act = 'make';

$_path = 'booking/booking_' . $_act . '.php';

if (!file_exists($_path)){
	$_act = 'make';
	$_path = 'booking/booking_' . $_act . '.php';
}



$modact_url = $submod_url."&act=$_act";
$current_url = $modact_url;

$month = isset($_POST['m']) ? $_POST['m'] : null;
if (!$month) $month = isset($_GET['m']) ? $_GET['m'] : date('n');
$year  =  isset($_POST['y']) ? $_POST['y'] : null;
if (!$year) $year = isset($_GET['y']) ? $_GET['y'] : date('Y');
$day = date('j');


require $_path;

