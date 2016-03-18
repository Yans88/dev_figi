<?php
$_view = isset($_GET['view']) ? $_GET['view'] : 'calendar';
$_path = 'booking/booking_list_' . $_view.'.php';

if (!(file_exists($_path))){
	$_view = 'calendar';
}
$_path = 'booking/booking_list_' . $_view.'.php';

$current_url = $modact_url . '&view=' . $_view;
?>
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		
<div class="submod_wrap" style="">
	<div class="submod_title"><h4>Booking List / History</h4></div>
	<div class="submod_links">
		<a href="<?php echo $mod_url?>&act=list&view=table" class="button" id="display_list"> List View </a>
		<a href="<?php echo $mod_url?>&act=list&view=calendar" class="button" id="display_as_calendar"> Calendar View </a>
	<?php
		if (defined('PORTAL')){
			echo '<a href="'.$mod_url.'&act=make" class="button" id="make_booking"> Make Booking </a>';
		}
	?>
	</div>
	<div class="clear"> </div>
</div>

<?php
require $_path;

