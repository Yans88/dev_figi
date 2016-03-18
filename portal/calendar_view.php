<?php
$from_portal = true;

global $repeat_labels;
include 'calendar/calendar_view.php';
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
/*
$('#delbtn').click(function (e){
	if (confirm('Are you sure delete this event?')){
            var remark = prompt('Delete for what reason: ');
             if (remark != null && remark != undefined){  
                $.post("calendar/event_delete.php", {id: <?php echo $_id?>, remark: ""+remark+"", submitcode: "remove"}, function(data){
                    //alert(data)
                    if (data == 'OK'){
                        alert('Selected calendar event has been deleted');
                       location.href="./?mod=portal&portal=calendar";
                    } else
                        alert('Fail to delete seletected calendar event');
                });
            }
        }
});
*/
</script>
