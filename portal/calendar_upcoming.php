<?php
global $repeat_labels;
include 'upcoming_events.php';
?>
<style type="text/css">
#tab_calendar #calendar_view {width: 700px; border: 1px solid #000;}
#tab_calendar #calendar_view h3 {color: #000; padding-top: 5px; padding-bottom: 5px;}
</style>
<script type="text/javascript">

$('#calbtn').click(function(e){
    location.href="./?mod=portal&portal=calendar";
});

var dt = new Date("<?php echo $event['date_start']?>");
$('#editbtn').click(function(e){
    location.href="./?mod=portal&portal=calendar&act=edit&id=<?php echo "$_id&d=$_date"?>";
});
</script>
