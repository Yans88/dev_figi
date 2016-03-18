<?php
require 'php-calendar/calendar.php';
if (!empty($_POST['w']) && !empty($_POST['y'])){
	$_time = strtotime(sprintf("%4dW%02d", $_POST['y'], $_POST['w']));
	$year = date('Y', $_time);
	$month = date('n', $_time);
}

$calendar = Calendar::factory($month, $year);

$calendar->standard('today')
    ->standard('prev-next');
?>

<link type="text/css" rel="stylesheet" href="style/default/calendar.css">
<table class="calendar small">
    <thead>
        <tr class="navigation">
            <th class="prev-month"><a class="navc" href="javascript:go_to('<?php echo htmlspecialchars($calendar->prev_month_url()) ?>')"><?php echo $calendar->prev_month(0, '&laquo;') ?></a></th>
            <th colspan="5" class="current-month"><?php echo $calendar->month() ?> <?php echo $calendar->year ?></th>
            <th class="next-month"><a class="navc" href="javascript:go_to('<?php echo htmlspecialchars($calendar->next_month_url()) ?>')"><?php echo $calendar->next_month(0, '&raquo;') ?></a></th>
        </tr>
        <tr class="weekdays">
            <?php foreach ($calendar->days(1) as $day): ?>
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
                     
                    if (is_array($data))
                    {
                        $classes = $data['classes'];
                        $title   = $data['title'];
                        $output  = empty($data['output']) ? '' : '<ul class="output"><li>'.implode('</li><li>', $data['output']).'</li></ul>';
                    }
					$todate = '';
					if ($current)
						$todate = "$year-$month-$number";
					//if ($number==$_day) $classes[] = 'today';
                    ?>
                    <td class="day <?php echo implode(' ', $classes) ?>" value="<?php echo $todate ?>">
                        <span class="date" title="<?php echo implode(' / ', $title) ?>">
                            <?php if ( ! empty($output)): ?>
                                <a href="#" class="view-events"><?php echo $number ?></a>
                            <?php else: ?>
                                <?php echo $number ?>
                            <?php endif ?>
                        </span>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<form method="post" id="scform">
<input type="hidden" name="d" value="">
<input type="hidden" name="_facility" value="">
</form>

<style>
.day { cursor: pointer; }
.calendar.small .today { background: #eee; }
</style>
<script>
$('.day').click(function(){
	var d = $(this).attr('value');
	if (d.length>0){
		$('input[name=d]').val(d);
		$('#scform').submit();
	}
});
function go_to(act){
	var fid = $('#_facility').val();
	//$('#id_facility').val(fid);
	var f = $('#scform').get(0);
	f._facility.value = fid;
	$('#scform').attr('action', act);
	$('#scform').submit();
}
</script>
