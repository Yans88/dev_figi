<?php
//if (!defined('FIGIPASS')) exit;
include_once '../calendar/calendar_util.php';

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){}

$start = 0;
$limit = RECORD_PER_PAGE;
if ($_page > 0) $start = ($_page-1) * $limit;

$total_item = count_events_by_creator(USERID);
$total_page = ceil($total_item/$limit);
$data = get_events_by_creator(USERID, $start, $limit);

?>
<h4 class="facility_caption"><br/>My Event List</h4>
<?php
if ($total_item > 0){
?>
<style type="text/css">
 
/* Z-index of #mask must lower than #boxes .window */
#mask {
  left: 0;
  top: 0;
  position:absolute;
  z-index:9000;
  background-color:#000;
  display:none;
}
   
#boxes .window {
  position:fixed;
  /*
  width:440px;
  height:340px;
  border: 1px solid yellow;
  */
  display:none;
  z-index:9999;
  padding:1px;
}
 
 
/* Customize your modal window here, you can add background image too */
#boxes #dialog {
  width:840px; 
  height:320px;
  background-color: #062312;
  border: 1px solid #888;
}
#dialogtop {
    text-align: right;
}
</style>
<div id="boxes">
    <div id="dialog" class="window">
        <div id="dialogtop"><a href="#" class="close" alt="Close"  >[ X ]</a> &nbsp; </div>
        <div id="dialogcontent"></div>
    </div>
    <div id="mask"></div>
</div>

<table width="100%" cellpadding=2 cellspacing=1 class="facility_table" >
<tr>
  <th>Date/Time</th>
  <th>Title</th>
  <th>Location</th>
  <th>Repetition</th>
</tr>
<?php

$row = 0;
foreach ($data as $rec){
	$row++;
    
	$class = ($row % 2 == 0 ) ? 'alt' : 'normal';
    $repetition = $repetitions[$rec['repetition']];
    if ($rec['repetition']>0){
        $repetition = 'Every ' . $rec['interval'] . ' ' . $repeat_labels[$rec['interval']] ;
        if (!empty($rec['dt_last']))
            $repetition .= ' until ' . date('d M Y', $rec['dt_last']);
    }
    $booked_time = date('d M Y', $rec['dt_start']);
    if ($rec['dt_start'] != $rec['dt_end'])
        $booked_time .= ' to ' . date('d M Y', $rec['dt_end']);
    if ($rec['fullday'])
        $booked_time .= ". Full day event";
    else
        $booked_time .= date('H:i', $rec['dt_start']) . date(' -- H:i', $rec['dt_end']) ;
	echo <<<ROW
<tr class="$class">
	<td class="eventrow" align="left" id="event-$rec[id_event]">$booked_time</td>
	<td align="left">$rec[title]</td>
	<td align="left">$rec[location_name]</td>
	<td align="left">$repetition</td>
</tr>

ROW;
	}

echo '<tr ><td colspan=6 class="pagination">';
echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=calendar&act=list&page=');
echo '<div class="exportdiv">';
echo '<a href="./?mod=portal&portal=calendar&act=view_month" class="button">Calendar View</a> ';
echo '<a href="./?mod=portal&portal=calendar&act=add" class="button">Create Event</a> ';
echo '</div></td></tr></table>';
        
} else
    echo '<p class="error" style="margin-top: 10px">Data is not available!.<br/>
            Click <a href="./?mod=portal&portal=calendar&act=view_month">Calendar View</a> to see events in this month. 
            Or <a href="./?mod=portal&portal=calendar&act=add">here</a> to create an event!
        </p>';
?>

<br/>

<script type="text/javascript">

$('.eventrow').click(function (e){
    location.href = './?mod=portal&portal=calendar&act=view&id='+e.target.id.substring(6);
    
});

</script>
