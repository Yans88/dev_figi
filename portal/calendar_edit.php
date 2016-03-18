<br>
<?php
$from_portal = true;
$now = time();
$dts = isset($_GET['dts']) ? $_GET['dts'] : $now;
$dte = isset($_GET['dte']) ? $_GET['dte'] : $now;
$_d = isset($_GET['d']) ? $_GET['d'] : null;
if ($_d == 0)
    $_d = date('Y-m-d', $dts);
include 'calendar/calendar_edit.php';
?>
<style type="text/css">
#tab_calendar #calendar_edit {width: 700px; border: 1px solid #000;}
#tab_calendar #calendar_edit h3 {color: #000;  padding-top: 5px; padding-bottom: 5px;}
</style>
<script type="text/javascript">
$('#cancelbtn').click(function(e){
    var id = "<?php echo $_id?>";
    if (id > 0)
        location.href="./?mod=portal&portal=calendar&act=view&d=<?php echo $_d?>&id="+id;
    else
        location.href="./?mod=portal&portal=calendar&act=view_month&d=<?php echo $_d?>";
});

</script>