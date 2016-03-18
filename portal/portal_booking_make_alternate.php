

<?php 


ob_clean();

?>
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />
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





<?php
if (!defined('FIGIPASS')) exit;


$month = isset($_POST['m']) ? $_POST['m'] : null;
if (!$month) $month = isset($_GET['m']) ? $_GET['m'] : date('n');
$year  =  isset($_POST['y']) ? $_POST['y'] : null;
if (!$year) $year = isset($_GET['y']) ? $_GET['y'] : date('Y');
$day = date('j');

require 'facility/facility_util.php';
require 'booking/booking_util.php';

$_facility = isset($_POST['_facility']) ? $_POST['_facility'] : 0;
$facilities = bookable_facility_list();
if (count($facilities) == 0){
	$facilities[0] = '--none--';
} else {
/*
	if (empty($_facility)){
		$k = array_keys($facilities);
		$_facility = $k[0];
	}
*/
    $facilities = array(0=>'* select a facility')+$facilities;
}

if (empty($_POST['step'])) $_step = 0;
else $_step = $_POST['step'];


if (!empty($_POST['remove'])){
	$id_book = $_POST['id_book'];
	book_remove($id_book);
	$msg = "Selected booking has been deleted!";
	$url = './?mod=booking&act=list&view=table';
	if (defined('FROM_PORTAL'))
		$url = './?mod=portal&portal=facility&act=list&view=table';
	redirect($url, $msg);
} else
if (2 == $_step){ // booking confirm

	$id_book = book_save($_POST);
	if ($id_book>0){
		$msg['title'] = 'Booking Confirmed';
		$msg['text'] = 'Booking has been made successfully.';
		$msg['new'] = true;
		$book = book_info($id_book);

		/* NOTIFICATION */
		if(($book['notification'] == 1) && (!empty($book['email']))){
			
		}
		
		$url = '?mod=booking&act=view&id='.$id_book;
		
	} else {
		$msg['title'] = 'Booking Failed';
		$msg['text'] = 'Booking has failed to make.';
		$url = '?mod=booking';
	}
	$_SESSION['msg'] = serialize($msg);
	$output =<<<OUT
<script>
	location.href="$url";
</script>
OUT;
	echo $output;
	exit;

}
else if (1 == $_step){ // selecting resource

	require_once 'booking/booking_resource.php';

} else { // selecting time/period

?>
<div class="column" id="calcontainer" style="color:#fff;">
	<div class="column" id="calleft">
	<br>
	<?php
	require 'booking/calendar_widget.php';
	echo '<br>';
	require 'booking_history_widget.php';
	?>
	</div>
	<div class="column" id="calright">
	<?php 
		require_once 'booking/booking_period.php';
	?>
	</div>
</div>
<div id="calfooter"></div>

<?php
} // end of booking period
?>
