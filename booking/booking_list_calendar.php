<?php

require 'php-calendar/calendar.php';
require 'facility/facility_util.php';

$month = isset($_GET['m']) ? $_GET['m'] : date('n');
$year  = isset($_GET['y']) ? $_GET['y'] : date('Y');
$id_facility = isset($_POST['id_facility']) ? $_POST['id_facility'] : 9999;
if ($id_facility == 9999) if (!empty($_GET['f'])) $id_facility = $_GET['f'];

$calendar = Calendar::factory($month, $year);
$calendar->standard('today')->standard('prev-next');

$start_of_month = mktime(0, 0, 1, $month, 1, $year);
$last_dom = date('t', $start_of_month);
$end_of_month = mktime(23, 59, 59, $month, $last_dom, $year);
$filter = array(
	'start_date' => $start_of_month,
	'end_date' => $end_of_month,
	'id_facility' => $id_facility
	);
//if (USERGROUP!=GRPADM) $filter['id_user'] = USERID;
$booked_sheet = booked_date_rows($filter);
foreach($booked_sheet as $dt => $periods){
	$ymd = date('Ymd', $dt);
	$books = array();
	foreach($periods as $id_time => $rec){
		if (!isset($books[$rec['id_book']])){
			$books[$rec['id_book']] = array(
				'title' => $rec['purpose'],
				'output' => $rec['purpose']//.', '.$rec['remark']
				);
		}
	}
	$events = array();
	foreach($books as $id_book => $info){
		//$title = $info['title'];
		$events[] = '<a href="'.$mod_url.'&act=view&id='.$id_book.'">'.$info['title'].'</a><br>';
	}
	$title = '';
	$output = implode('', $events);
	$event = $calendar->event()->condition('timestamp', $dt)->title($title)->output($output);
	$calendar->attach($event);
}


$facility_list = array('0' => '* select a facility')+bookable_facility_list();
$todate = date('Y-n-j');
$start_time_of_today = mktime(0, 0, 0, date('n'), date('j'), date('Y')); 
?>
<link rel="stylesheet" type="text/css" href="style/default/calendar.css" media="screen" />		
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		
<form method="post" id="bookingform">
	<div class="bookinglist-calendar" style="">
		<div style="padding: 5px 0 1px 0; " >
			<div id="calnav" style="float: left; ">
				Facility: <?php echo build_combo('id_facility', $facility_list, $id_facility)?>	
			</div>
			<div id="calnav" style="float: right; ">
				<button type="button" class="weeknav" id="today"> Today </button>
			</div>
		<div class="clear"></div>
		</div>

		<table class="calendar">
			<thead>
				<tr class="navigation">
					<th class="prev-month"><a href="<?php echo htmlspecialchars($calendar->prev_month_url()) ?>"><?php echo $calendar->prev_month() ?></a></th>


					<th colspan="5" class="left current-month"><?php echo $calendar->month() .' '. $calendar->year ?></th>
					<th class="next-month"><a href="<?php echo htmlspecialchars($calendar->next_month_url()) ?>"><?php echo $calendar->next_month() ?></a></th>
				</tr>
				<tr class="weekdays">
					<?php foreach ($calendar->days() as $day): ?>
						<th><?php echo $day ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($calendar->weeks() as $week): ?>
					<tr>
						<?php foreach ($week as $day): ?>
							<?php
							list($number, $current, $data) = $day;
							$classes = array();
							$output  = '';
							$_title = 'Click to book';
							if (is_array($data))
							{
								$classes = $data['classes'];
								$title   = $data['title'];
								$output  = empty($data['output']) ? '' : '<ul class="output"><li>'.implode('</li><li>', $data['output']).'</li></ul>';
								//$_title = implode(' / ', $title);
							}
							/*
							$is_next_today = false;
							$allow_to_book = false;
							if ($current){
								$check_date = $calendar->year.'-'.$calendar->month.'-'.$number;
								$check_time = strtotime($check_date);
								$is_next_today =($start_time_of_today <= $check_time);
								$allow_to_book = $is_next_today;
							}
							$_id = null;
							$_title = null;
							if ($allow_to_book) {
								$_id = "id=\"date-$year-$month-$number\"";
								$_title = 'Click to book';
								$classes[] = 'allow-to-book';
							}
							*/
							$_id = null;
							?>
							<td class="day <?php echo implode(' ', $classes) ?>">
								<span class="date" <?php echo $_id?>  title="<?php echo $_title ?>"><?php echo $number ?>
								<?php 
								/*
									if ($allow_to_book)
										echo '<div style="float: right"><a href="javascript:book(0)" title="book this date" class="icon">+</a></div><div class="clear"></div>';
								*/
								?>
								</span>
								
								<div class="day-content">
									<?php echo $output ?>
								</div>
							</td>
						<?php endforeach ?>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
		
</form>

<script>


$('#id_facility').change(function(){
    $('#bookingform').submit();
});

$('.date').click(function(){
	var id = this.id
	var d = id.substr(5);
	if (d.length>0 && $(this).parent().hasClass('allow-to-book'))
		location.href = "<?php echo $mod_url?>&d="+d;
});

$('#today').click(function(){
	var d = new Date();
	location.href = "<?php echo $mod_url?>&act=list&view=calendar&y="+d.getFullYear()+"&m="+(d.getMonth()+1);
});

$('#display_list').click(function(){
	var d = new Date();
	location.href = "<?php echo $mod_url?>&act=list&view=table&y="+d.getFullYear()+"&m="+(d.getMonth()+1);
});
</script>
