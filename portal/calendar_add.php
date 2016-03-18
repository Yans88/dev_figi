<?php
include 'calendar/calendar_add.php';
?>
<style type="text/css">
#tab_calendar #calendar_edit {width: 700px; border: 1px solid #000;}
#tab_calendar #calendar_edit h3 {color: #000;  padding-top: 5px; padding-bottom: 5px;}
</style>
<script type="text/javascript">
$('#cancelbtn').click(function(e){
    location.href="./?mod=portal&portal=calendar";
});

</script>