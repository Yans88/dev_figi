<?php

require 'booking_util.php';

if (empty($_sub)) $_sub = 'booking';

$_path = $_mod . '/' . $_sub . '.php';

if (!file_exists($_path)){
	$_path = $_mod . '/booking.php';
	$_sub = 'booking';
}

$mod_url = './?mod='.$_mod;
$submod_url = $mod_url . '&sub=' . $_sub;
$current_url = $submod_url;

?>
<div class="mod_wrap">
	<div class="mod_title"><h3>Facility Booking</h3></div>
	<div class="mod_links">
		<a href="./?mod=booking&act=make" class="button">Make Booking</a> 
		<!-- <a href="./?mod=booking&act=list" class="button">Booking Calendar</a> -->
	<?php if (USERGROUP == GRPADM){ ?>
		<a href="./?mod=booking&sub=subject" class="button">Manage Subject</a>		
		<a href="./?mod=facility" class="button">Manage Facility</a>
			<!--<a href="./?mod=maintenance" class="button">Maintenance Checklist</a>		
			<a href="./?mod=booking&sub=period_term" class="button">Manage Period Term</a-->
	<?php } ?>
	 </div>
	<div class="clear"> </div>
</div>

<?php
require $_path;

