<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		

<div class="submod_wrap">
	<div class="submod_links">
	<?php
		if (defined('PORTAL')){
			echo '<a href="./?mod=portal&portal=facility" class="button" > Booking Calendar </a>';
		} else {
			echo '<a href="./?mod=booking" class="button" > Cancel </a> ';
			echo '<a href="./?mod=booking&act=import" class="button" > Import </a>';
		}
	?>
	</div>
	<div class="submod_title"><h4 >Make Booking</h4></div>
	<div class="clear"> </div>
</div>
<?php

require 'facility/facility_util.php';

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
	if(ALTERNATE_PORTAL_STATUS == 'enable'){
	$output =<<<OUT
<script>
	top.location.href="$url";
</script>
OUT;
	} else {
	$output =<<<OUT
<script>
	location.href="$url";
</script>
OUT;
}
	echo $output;
	exit;

}
else if (1 == $_step){ // selecting resource

	require_once 'booking_resource.php';

} else { // selecting time/period

?>
<div class="column" id="calcontainer" style="color:#fff;">
	<div class="column" id="calleft">
	<br>
	<?php
	require 'calendar_widget.php';
	echo '<br>';
	require 'booking_history_widget.php';
	?>
	</div>
	<div class="column" id="calright">
	<?php 
		require_once 'booking_period.php';
	?>
	</div>
</div>
<div id="calfooter"></div>

<?php
} // end of booking period
?>
